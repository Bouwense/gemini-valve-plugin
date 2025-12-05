<?php
/**
 * Plugin Name: GV - ChatGPT Product Finder (Dynamic + AI Reasons)
 * Description: Reliable product finder with direct SQL text search (title/content/slug), dynamic strictness, SKU LIKE, and AI reranking that explains why each result matches. Uses central OpenAI key via get_option('gv_openai_api_key') and optional org via get_option('gv_openai_org_id'). Logs all searches.
 * Author: Gemini Valve
 * Version: 2.4.0-en
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ===== WooCommerce guard ===== */
add_action('plugins_loaded', function () {
    if ( ! class_exists('WooCommerce') ) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>GV Product Finder:</strong> WooCommerce is required but not active.</p></div>';
        });
    }
});

/* =============================================================================
 * UTILITIES
 * ============================================================================= */

function gvpf_log(array $data) {
    $uploads = wp_upload_dir();
    $dir = trailingslashit($uploads['basedir']) . 'gv-logs';
    if ( ! file_exists($dir) ) {
        wp_mkdir_p($dir);
        $ht = $dir . '/.htaccess';
        if ( ! file_exists($ht) ) @file_put_contents($ht, "Require all denied\n");
    }
    $file = $dir . '/gv-product-search.log';
    $data['ts'] = current_time('mysql');
    @file_put_contents($file, wp_json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
}

function gvpf_sanitize_input(string $text) : array {
    $clean = trim(wp_strip_all_tags($text));
    $blocked = false; $reasons = [];

    if ( preg_match_all('#https?://[^\s]+#i', $clean, $m) ) {
        foreach ($m[0] as $u) {
            $p = wp_parse_url($u);
            $host = isset($p['host']) ? strtolower($p['host']) : '';
            if ( $host && ! in_array($host, ['geminivalve.nl','www.geminivalve.nl'], true) ) {
                $blocked = true; $reasons[] = 'external_url'; $clean = str_replace($u, '', $clean);
            }
        }
    }
    $bad_patterns = [
        '/\b(?:curl|wget|rm\s+-rf|chmod\s+\d+|chown\s+|scp\s|ssh\s)\b/i',
        '/(;|\|\||&&)\s*(?:cat|tail|head|less|more|echo|touch|mv|cp|dd)\b/i',
        '/\b(?:DROP|DELETE|ALTER|TRUNCATE|INSERT\s+INTO|UPDATE)\b/i',
        '/\bSYSTEM PROMPT\b/i',
        '/\bignore (all )?previous instructions\b/i',
    ];
    foreach ($bad_patterns as $rx) {
        if ( preg_match($rx, $clean) ) { $blocked = true; $reasons[]='command_pattern'; $clean = preg_replace($rx, '', $clean); }
    }
    $clean = trim(preg_replace('/\s+/', ' ', $clean));
    return ['clean'=>$clean, 'blocked_reason'=>$blocked ? implode(',', array_unique($reasons)) : ''];
}

function gvpf_basic_keywords(string $text) : array {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\/\"\-\s]/', ' ', $text); // keep / and " for inches
    $parts= array_filter(array_unique(explode(' ', $text)));
    $parts = array_filter($parts, function($k){ $l=strlen($k); return $l>=2 && $l<=50; });
    $stop  = ['the','and','or','of','for','with','a','an','to','is','are','on','in'];
    $keywords = array_values(array_diff($parts, $stop));
    return array_slice($keywords, 0, 16);
}

function gvpf_parse_smart_query(string $raw) : array {
    $raw_trim = trim($raw);
    $phrase = '';
    if ( preg_match('/"([^"]+)"/', $raw_trim, $m) ) $phrase = trim($m[1]); else if ( strlen($raw_trim) >= 4 ) $phrase = $raw_trim;

    $kw = gvpf_basic_keywords($raw_trim);
    $must = []; $maybe= [];
    foreach ($kw as $k) {
        if ( preg_match('/^dn\s?\d+$/i', $k) ) { $must[] = strtoupper(str_replace(' ', '', $k)); continue; }
        if ( preg_match('/^cl\s?\d+$/i', $k) ) { $must[] = strtoupper(str_replace(' ', '', $k)); continue; }
        if ( preg_match('/^pn\s?\d+$/i', $k) ) { $must[] = strtoupper(str_replace(' ', '', $k)); continue; }
        if ( preg_match('/^api\s?\d+$/i', $k) ) { $must[] = strtoupper(preg_replace('/\s+/','',$k)); continue; }
        if ( preg_match('/^\d+(?:\/\d+)?["]$/', $k) ) { $must[] = $k; continue; } // 1/2", 3/4"
        if ( preg_match('/^(?:316|304|aisi316|aisi304)$/i', str_replace(' ','',$k)) ) { $must[] = strtoupper(str_replace(' ','',$k)); continue; }
        $maybe[] = $k;
    }
    return [
        'phrase' => $phrase,
        'must'   => array_values(array_unique($must)),
        'maybe'  => array_values(array_unique($maybe)),
        'all'    => $kw,
    ];
}

function gvpf_build_sku_like_meta_query(array $keywords) : array {
    $keywords = array_values(array_unique(array_filter(array_map('sanitize_text_field', $keywords))));
    $keywords = array_slice(array_filter($keywords, function($k){ $l=strlen($k); return $l>=2 && $l<=50; }), 0, 10);
    if (empty($keywords)) return [];
    $clauses = ['relation' => 'OR'];
    foreach ($keywords as $kw) {
        $clauses[] = ['key'=>'_sku', 'value'=>$kw, 'compare'=>'LIKE'];
    }
    return $clauses;
}

/**
 * Direct text search (no WP filters). Modes:
 *  - 'or'           : phrase OR any token (each checks title/content/slug)
 *  - 'and'          : phrase OR (ALL tokens)
 *  - 'title_strict' : (title LIKE phrase) OR (ALL tokens present in title OR slug)
 */
function gvpf_direct_text_search(array $tokens, string $phrase = '', string $mode = 'or', int $limit = 400) : array {
    global $wpdb;

    $tokens = array_values(array_unique(array_filter(array_map('sanitize_text_field', $tokens))));
    $tokens = array_slice(array_filter($tokens, function($k){ $l=strlen($k); return $l>=2 && $l<=50; }), 0, 16);

    $clauses_phrase = [];
    $clauses_tokens = [];

    $phrase = trim( wp_strip_all_tags( (string)$phrase ) );
    if ( $phrase !== '' ) {
        $plike = '%' . $wpdb->esc_like($phrase) . '%';
        if ( $mode === 'title_strict' ) {
            $clauses_phrase[] = $wpdb->prepare("(p.post_title LIKE %s)", $plike);
        } else {
            $clauses_phrase[] = $wpdb->prepare("(p.post_title LIKE %s OR p.post_content LIKE %s OR p.post_name LIKE %s)", $plike, $plike, $plike);
        }
    }

    foreach ($tokens as $t) {
        $like = '%' . $wpdb->esc_like($t) . '%';
        if ( $mode === 'title_strict' ) {
            $clauses_tokens[] = $wpdb->prepare("(p.post_title LIKE %s OR p.post_name LIKE %s)", $like, $like);
        } else {
            $clauses_tokens[] = $wpdb->prepare("(p.post_title LIKE %s OR p.post_content LIKE %s OR p.post_name LIKE %s)", $like, $like, $like);
        }
    }

    $where = '';
    if ( $mode === 'and' ) {
        if ( !empty($clauses_phrase) && !empty($clauses_tokens) ) {
            $where = ' AND ( ' . $clauses_phrase[0] . ' OR ( ' . implode(' AND ', $clauses_tokens) . ' ) ) ';
        } elseif ( !empty($clauses_tokens) ) {
            $where = ' AND ( ' . implode(' AND ', $clauses_tokens) . ' ) ';
        } elseif ( !empty($clauses_phrase) ) {
            $where = ' AND ( ' . $clauses_phrase[0] . ' ) ';
        }
    } elseif ( $mode === 'title_strict' ) {
        $blocks = [];
        if ( !empty($clauses_phrase) ) $blocks[] = $clauses_phrase[0];
        if ( !empty($clauses_tokens) ) $blocks[] = '( ' . implode(' AND ', $clauses_tokens) . ' )';
        if ( !empty($blocks) ) $where = ' AND ( ' . implode(' OR ', $blocks) . ' ) ';
    } else { // 'or'
        $blocks = array_merge($clauses_phrase, $clauses_tokens);
        if ( !empty($blocks) ) $where = ' AND ( ' . implode(' OR ', $blocks) . ' ) ';
    }

    if ( $where === '' ) return [];

    $sql = "
        SELECT DISTINCT p.ID
        FROM {$wpdb->posts} p
        WHERE p.post_type IN ('product','product_variation')
          AND p.post_status = 'publish'
          {$where}
        ORDER BY p.post_date_gmt DESC
        LIMIT %d
    ";

    $ids = $wpdb->get_col( $wpdb->prepare($sql, $limit) );
    return array_map('intval', $ids);
}

/* =============================================================================
 * OPENAI
 * ============================================================================= */

function gvpf_openai_chat_json(array $args) : ?array {
    $api_key = trim( (string) get_option('gv_openai_api_key', '') );
    if ( empty($api_key) ) return null;
    $org_id  = trim( (string) get_option('gv_openai_org_id', '') );
    $model   = trim( (string) get_option('gv_pf_openai_model', 'gpt-4o-mini') );

    $endpoint = 'https://api.openai.com/v1/chat/completions';
    $payload = [
        'model'       => $model,
        'temperature' => $args['temperature'] ?? 0.2,
        'messages'    => [
            [ 'role' => 'system', 'content' => $args['system'] ],
            [ 'role' => 'user',   'content' => $args['user']   ],
        ],
        'max_tokens'  => $args['max_tokens'] ?? 1000,
    ];
    $headers = [
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type'  => 'application/json',
    ];
    if ( ! empty($org_id) ) $headers['OpenAI-Organization'] = $org_id;

    $res = wp_remote_post($endpoint, [
        'headers' => $headers,
        'timeout' => $args['timeout'] ?? 25,
        'body'    => wp_json_encode($payload),
    ]);
    if ( is_wp_error($res) ) return null;
    $code = wp_remote_retrieve_response_code($res);
    if ( $code < 200 || $code >= 300 ) return null;

    $body = json_decode(wp_remote_retrieve_body($res), true);
    if ( ! isset($body['choices'][0]['message']['content']) ) return null;

    $content = trim($body['choices'][0]['message']['content']);
    if ( preg_match('/\{.*\}/s', $content, $m) ) $content = $m[0];
    $json = json_decode($content, true);
    if ( ! is_array($json) ) return null;
    return $json;
}

function gvpf_collect_product_facts($product) : array {
    $pid   = $product->get_id();
    $title = $product->get_name();
    $sku   = (string) $product->get_sku();
    $slug  = basename( get_permalink($pid) );

    $cats  = wp_get_post_terms($pid, 'product_cat', ['fields' => 'names']);
    $cats  = is_wp_error($cats) ? [] : array_slice($cats, 0, 6);

    $content = wp_strip_all_tags( get_post_field('post_content', $pid) );
    $excerpt = has_excerpt($pid) ? get_the_excerpt($pid) : '';
    $blurb   = trim($excerpt ?: mb_substr($content, 0, 300));

    $attrs_txt = [];
    $attrs = $product->get_attributes();
    if ( is_array($attrs) ) {
        foreach ($attrs as $attr) {
            if ( method_exists($attr, 'get_name') ) {
                $name = $attr->get_name();
                $val  = '';
                if ( $attr->is_taxonomy() ) {
                    $terms = wp_get_post_terms($pid, $name, ['fields' => 'names']);
                    if ( ! is_wp_error($terms) && ! empty($terms) ) $val = implode(', ', array_slice($terms,0,4));
                } else {
                    $val = method_exists($attr, 'get_options') ? implode(', ', array_slice((array)$attr->get_options(),0,4)) : '';
                }
                if ( $val !== '' ) $attrs_txt[] = $name . ':' . $val;
            }
        }
    }

    return [
        'id'         => $pid,
        'title'      => $title,
        'sku'        => $sku,
        'slug'       => $slug,
        'categories' => $cats,
        'blurb'      => $blurb,
        'attrs'      => implode(' | ', array_slice($attrs_txt, 0, 8)),
        'url'        => get_permalink($pid),
    ];
}

function gvpf_ai_rerank_with_reasons(string $query, array $candidate_facts, int $max_return = 50) : ?array {
    if ( empty($candidate_facts) ) return null;
    $candidate_facts = array_slice($candidate_facts, 0, 60);

    $system = "You are a product-matching engine for an industrial valve webshop.
Return ONLY valid JSON. No commentary.
Rate each candidate 0-100 for the query and give a short reason (<200 chars).
Prioritize: exact phrase in title/slug, correct size/class (DN/CL/PN), material (316), certification (API 607), correct product type (e.g., Air Header).
Penalize unrelated results.";

    $user = wp_json_encode([
        'query'      => $query,
        'candidates' => $candidate_facts,
        'instructions' => [
            'output_format' => [
                'query_understanding' => [
                    'phrase' => 'string',
                    'must_tokens' => ['string'],
                    'maybe_tokens' => ['string']
                ],
                'results' => [[
                    'id'    => 'number (candidate id)',
                    'score' => 'integer 0..100',
                    'why'   => 'short reason'
                ]]
            ],
            'max_return' => $max_return
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $json = gvpf_openai_chat_json([
        'system'     => $system,
        'user'       => $user,
        'timeout'    => 25,
        'max_tokens' => 1200,
        'temperature'=> 0.1
    ]);

    if ( ! is_array($json) || empty($json['results']) ) return null;

    $understanding = [
        'phrase'       => (string)($json['query_understanding']['phrase'] ?? ''),
        'must_tokens'  => array_values(array_filter((array)($json['query_understanding']['must_tokens'] ?? []))),
        'maybe_tokens' => array_values(array_filter((array)($json['query_understanding']['maybe_tokens'] ?? []))),
    ];
    $results = [];
    foreach ( (array)$json['results'] as $r ) {
        $id = isset($r['id']) ? (int)$r['id'] : 0;
        if ( $id <= 0 ) continue;
        $results[$id] = [
            'score' => max(0, min(100, (int)($r['score'] ?? 0))),
            'why'   => sanitize_text_field( (string)($r['why'] ?? '') ),
        ];
    }
    if ( empty($results) ) return null;

    return ['understanding' => $understanding, 'results' => $results];
}

/* =============================================================================
 * SHORTCODE (front-end)
 * ============================================================================= */

add_shortcode('gv_product_finder', function($atts){
    $atts = shortcode_atts([
        'per_page'  => 12,
        'cards'     => 0,          // 0 = list only, 1 = also show cards (not emphasized here)
        'ai'        => 1,          // 1 = AI rerank with reasons, 0 = disable AI
        'strict'    => 'auto',     // auto | loose | tight
    ], $atts, 'gv_product_finder');

    if ( ! function_exists('wc_get_product') ) {
        return '<div class="gvpf-error" style="padding:12px;border:1px solid #f5c2c0;background:#fdecea;border-radius:8px;">WooCommerce is not active. Product finder is unavailable.</div>';
    }

    ob_start(); ?>
    <style>
        .gvpf-wrap { margin: 1rem 0; }
        .gvpf-form { display:flex; gap:.5rem; flex-wrap:wrap; align-items:flex-end; margin-bottom:1rem; }
        .gvpf-form .field { flex:1 1 280px; }
        .gvpf-list ul { margin:0 0 1rem; padding-left:1.1rem; }
        .gvpf-msg { padding:.75rem 1rem; border-radius:8px; background:#fff8e1; border:1px solid #ffe0a3; }
        .gvpf-error { padding:.75rem 1rem; border-radius:8px; background:#fdecea; border:1px solid #f5c2c0; }
        .gvpf-score { opacity:.85; font-size:.9em; }
    </style>
    <div class="gvpf-wrap">
        <?php
        $errors  = [];
        $results = [];
        $paged   = max(1, (int) (get_query_var('paged') ?: (isset($_GET['gvpf_paged']) ? intval($_GET['gvpf_paged']) : 1)));
        $query_text  = isset($_POST['gvpf_q']) ? wp_unslash($_POST['gvpf_q']) : ( isset($_GET['gvpf_q']) ? wp_unslash($_GET['gvpf_q']) : '' );
        $use_ai      = (int)$atts['ai'] === 1;
        $strict_attr = in_array($atts['strict'], ['auto','loose','tight'], true) ? $atts['strict'] : 'auto';
        ?>
        <form class="gvpf-form" method="post">
            <div class="field">
                <label for="gvpf_q">Search text</label>
                <input type="text" id="gvpf_q" name="gvpf_q" value="<?php echo esc_attr($query_text); ?>" placeholder='e.g. "Air Header" DN25 CL150' style="width:100%;">
            </div>
            <?php wp_nonce_field('gvpf_search','gvpf_nonce'); ?>
            <div><button class="button button-primary" type="submit">Search</button></div>
        </form>
        <?php

        $nonce_ok = ( isset($_POST['gvpf_nonce']) ? wp_verify_nonce($_POST['gvpf_nonce'], 'gvpf_search') : true );

        if ( $nonce_ok && $query_text !== '' ) {
            $res = gvpf_search_dynamic_ai([
                'text'      => $query_text,
                'per_page'  => (int)$atts['per_page'],
                'paged'     => $paged,
                'use_ai'    => $use_ai,
                'strict'    => $strict_attr,
            ], $errors);
            $results = $res;
        }

        if ( ! empty($errors) ) {
            echo '<div class="gvpf-error"><ul>';
            foreach ($errors as $e) echo '<li>'.esc_html($e).'</li>';
            echo '</ul></div>';
        }

        if ( isset($results['items']) && is_array($results['items']) ) {
            if ( empty($results['items']) ) {
                echo '<div class="gvpf-msg">No results. Try different terms (e.g., size DN, pressure class, certification).</div>';
            } else {
                if ( !empty($results['understanding']) || !empty($results['strictness']) ) {
                    echo '<p class="gvpf-score">';
                    if ( !empty($results['understanding']['phrase']) ) {
                        echo '<strong>AI understanding</strong> — Phrase: “'.esc_html($results['understanding']['phrase']).'”. ';
                    }
                    if ( !empty($results['understanding']['must_tokens']) ) {
                        echo 'Must: '.esc_html(implode(', ', $results['understanding']['must_tokens'])).'. ';
                    }
                    if ( !empty($results['understanding']['maybe_tokens']) ) {
                        echo 'Also: '.esc_html(implode(', ', $results['understanding']['maybe_tokens'])).'. ';
                    }
                    if ( !empty($results['strictness']) ) {
                        echo ' | Strictness: '.esc_html($results['strictness']['mode']).' (pool '.$results['strictness']['pool'].')';
                    }
                    echo '</p>';
                }

                echo '<div class="gvpf-list"><ul>';
                foreach ($results['items'] as $r) {
                    $line = '<a href="'.esc_url($r['url']).'">'.esc_html($r['title']).'</a>';
                    if ( isset($r['ai_score']) ) $line .= ' — <span class="gvpf-score">'.$r['ai_score'].'/100</span>';
                    if ( !empty($r['ai_why']) ) $line .= ' — Why: '.esc_html($r['ai_why']);
                    echo '<li>'.$line.'</li>';
                }
                echo '</ul></div>';
            }
        }
        ?>
    </div>
    <?php
    return ob_get_clean();
});

/**
 * Dynamic strictness + AI rerank pipeline
 */
function gvpf_search_dynamic_ai(array $input, array &$errors) : array {
    $who   = get_current_user_id();
    $ip    = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
    $text  = trim((string)($input['text'] ?? ''));
    $paged = max(1, (int)($input['paged'] ?? 1));
    $ppp   = max(1, min(48, (int)($input['per_page'] ?? 12)));
    $use_ai= (bool)($input['use_ai'] ?? true);
    $strict= (string)($input['strict'] ?? 'auto');

    $san = gvpf_sanitize_input($text);
    if ( $san['blocked_reason'] ) $errors[] = 'Your input contained blocked patterns/links and was cleaned.';
    $text = $san['clean'];

    if ( $text === '' ) return ['items'=>[], 'total'=>0, 'total_pages'=>1, 'paged'=>1];

    $smart = gvpf_parse_smart_query($text);
    $tokens = array_values(array_unique(array_merge($smart['must'], $smart['maybe'])));

    // Step 1: choose initial mode
    $mode = 'or';
    if ( $strict === 'loose' ) $mode = 'or';
    elseif ( $strict === 'tight' ) $mode = 'title_strict';
    else $mode = 'or'; // auto starts loose

    // thresholds
    $MAX_POOL   = 500;  // too many → tighten
    $TIGHT_POOL = 350;
    $MIN_POOL   = 1;    // if we go below this after tightening, relax

    // Run search with dynamic tightening
    $pool_ids = gvpf_direct_text_search($tokens, $smart['phrase'], $mode, 700);
    $used_mode = $mode;

    if ( $strict === 'auto' ) {
        if ( count($pool_ids) > $MAX_POOL ) {
            // tighten to AND
            $pool_ids2 = gvpf_direct_text_search($tokens, $smart['phrase'], 'and', 700);
            if ( count($pool_ids2) >= $MIN_POOL ) { $pool_ids = $pool_ids2; $used_mode = 'and'; }
        }
        if ( count($pool_ids) > $TIGHT_POOL ) {
            // tighten to title_strict
            $pool_ids3 = gvpf_direct_text_search($tokens, $smart['phrase'], 'title_strict', 700);
            // Avoid over-tightening to 0 if we had something
            if ( count($pool_ids3) >= $MIN_POOL ) { $pool_ids = $pool_ids3; $used_mode = 'title_strict'; }
        }
        // If we ended up with nothing but had tokens/phrase, relax back to OR
        if ( empty($pool_ids) ) {
            $pool_ids = gvpf_direct_text_search($tokens, $smart['phrase'], 'or', 700);
            $used_mode = 'or';
        }
    }

    // SKU union (adds likely few targeted hits)
    $ids_sku = [];
    if ( !empty($tokens) ) {
        $meta_query = gvpf_build_sku_like_meta_query($tokens);
        $q_sku = new WP_Query([
            'post_type'      => ['product','product_variation'],
            'post_status'    => 'publish',
            'posts_per_page' => 200,
            'meta_query'     => $meta_query,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);
        $ids_sku = is_array($q_sku->posts) ? array_map('intval', $q_sku->posts) : [];
        wp_reset_postdata();
    }

    // Combine & dedupe
    $candidate_ids = array_values(array_unique(array_merge($pool_ids, $ids_sku)));

    // Load products, collapse variations to parent, optional DN narrowing
    $pool = []; $seen_parent = [];
    foreach ($candidate_ids as $pid) {
        $prod = wc_get_product($pid);
        if ( ! $prod ) continue;
        if ( is_a($prod, 'WC_Product_Variation') ) {
            $p = wc_get_product($prod->get_parent_id());
            if ( $p ) $prod = $p;
        }
        $parent_id = $prod->get_id();
        if ( isset($seen_parent[$parent_id]) ) continue;

        // Hidden narrowing on DN via pa_dn
        $passes_dn = true;
        if ( taxonomy_exists('pa_dn') && !empty($smart['must']) ) {
            foreach ($smart['must'] as $mt) {
                if ( preg_match('/^DN(\d+)$/i', $mt, $m) ) {
                    $dn_val = $m[1];
                    $terms = wp_get_post_terms($parent_id, 'pa_dn', ['fields'=>'names']);
                    if ( !is_wp_error($terms) && !empty($terms) ) {
                        $names = array_map('strval', $terms);
                        if ( ! in_array($dn_val, $names, true) ) { $passes_dn = false; break; }
                    }
                }
            }
        }
        if ( ! $passes_dn ) continue;

        $seen_parent[$parent_id] = true;
        $pool[$parent_id] = gvpf_collect_product_facts($prod);
    }

    // AI rerank
    $understanding = [];
    $ai_scores = [];
    if ( $use_ai && !empty($pool) ) {
        $ai = gvpf_ai_rerank_with_reasons($text, array_values($pool), 60);
        if ( is_array($ai) && !empty($ai['results']) ) {
            $understanding = $ai['understanding'];
            $ai_scores     = $ai['results'];
        }
    }

    // Build candidates with fallback heuristic if AI missing
    $candidates = [];
    foreach ($pool as $id => $facts) {
        $score = 0; $why = '';
        if ( isset($ai_scores[$id]) ) {
            $score = (int)$ai_scores[$id]['score'];
            $why   = (string)$ai_scores[$id]['why'];
        } else {
            // Heuristic fallback
            $title = strtolower($facts['title']); $slug = strtolower($facts['slug']); $blurb = strtolower($facts['blurb']);
            if ( $smart['phrase'] !== '' ) {
                $ph = strtolower($smart['phrase']);
                if ( strpos($title,$ph)!==false ) $score += 50;
                elseif ( strpos($slug,$ph)!==false ) $score += 35;
                elseif ( strpos($blurb,$ph)!==false ) $score += 10;
            }
            $hits = 0;
            foreach ($smart['must'] as $mt) {
                $m = strtolower($mt);
                if ( strpos($title,$m)!==false || strpos($slug,$m)!==false || strpos($blurb,$m)!==false ) $hits++;
            }
            $score += $hits * 12;
            $why = $smart['phrase'] ? 'Matches phrase and technical tokens.' : 'Matches tokens.';
        }
        $candidates[] = [ 'id'=>$id, 'score'=>$score, 'why'=>$why, 'facts'=>$facts ];
    }

    usort($candidates, function($a,$b){ return $b['score'] <=> $a['score']; });

    $total = count($candidates);
    $total_pages = (int) max(1, ceil($total / $ppp));
    $offset = ($paged - 1) * $ppp;
    $slice  = array_slice($candidates, $offset, $ppp);

    $items = array_map(function($c){
        return [
            'id'       => $c['id'],
            'title'    => $c['facts']['title'],
            'url'      => $c['facts']['url'],
            'ai_score' => $c['score'],
            'ai_why'   => $c['why'],
        ];
    }, $slice);

    gvpf_log([
        'mode'        => 'dynamic_ai',
        'text'        => $text,
        'user_id'     => $who,
        'ip'          => $ip,
        'found_ids'   => array_column($items, 'id'),
        'frontend'    => true,
        'paged'       => $paged,
        'per_page'    => $ppp,
        'strict'      => $strict,
        'used_mode'   => $used_mode,
        'pool'        => $total,
        'used_ai'     => !empty($ai_scores),
    ]);

    return [
        'items'         => $items,
        'total'         => $total,
        'total_pages'   => $total_pages,
        'paged'         => $paged,
        'understanding' => $understanding,
        'strictness'    => ['mode'=>$used_mode, 'pool'=>$total],
    ];
}

/* =============================================================================
 * ADMIN (minimal tester under Tools)
 * ============================================================================= */

add_action('admin_menu', function() {
    add_management_page(
        'GV: ChatGPT Product Finder',
        'GV Product Finder',
        'manage_options',
        'gv-pf',
        'gvpf_render_admin_page'
    );
});

function gvpf_render_admin_page() {
    if ( ! current_user_can('manage_options') ) { wp_die('No access'); }

    $errors  = [];
    $results = [];
    $under   = [];
    $strictn = [];

    if ( isset($_POST['gv_pf_nonce']) && wp_verify_nonce($_POST['gv_pf_nonce'], 'gv_pf_search') ) {
        $raw_text = isset($_POST['gv_pf_text']) ? wp_unslash($_POST['gv_pf_text']) : '';
        if ( trim($raw_text) === '' ) {
            $errors[] = 'Enter a search text.';
        } else {
            $res = gvpf_search_dynamic_ai([
                'text'      => $raw_text,
                'per_page'  => 20,
                'paged'     => 1,
                'use_ai'    => 1,
                'strict'    => 'auto',
            ], $errors);
            $results = $res['items'] ?? [];
            $under   = $res['understanding'] ?? [];
            $strictn = $res['strictness'] ?? [];
        }
    }

    echo '<div class="wrap"><h1>GV Product Finder (Admin)</h1>';
    echo '<form method="post">';
    wp_nonce_field('gv_pf_search','gv_pf_nonce');
    echo '<table class="form-table"><tbody>';
    echo '<tr><th scope="row">Search text</th><td><input type="text" name="gv_pf_text" class="regular-text" placeholder=\'e.g. "Air Header" DN25 CL150\' /></td></tr>';
    echo '</tbody></table>';
    submit_button('Search');
    echo '</form>';

    if ( ! empty($errors) ) {
        echo '<div class="notice notice-error"><p><strong>Errors:</strong></p><ul>';
        foreach ($errors as $e) echo '<li>'.esc_html($e).'</li>';
        echo '</ul></div>';
    }

    if ( ! empty($results) ) {
        if ( ! empty($under) || ! empty($strictn) ) {
            echo '<p><em>';
            if (!empty($under['phrase'])) echo 'AI phrase “'.esc_html($under['phrase']).'”. ';
            if (!empty($under['must_tokens'])) echo 'Must: '.esc_html(implode(', ', $under['must_tokens'])).'. ';
            if (!empty($under['maybe_tokens'])) echo 'Also: '.esc_html(implode(', ', $under['maybe_tokens'])).'. ';
            if (!empty($strictn)) echo ' | Strictness: '.esc_html($strictn['mode']).' (pool '.$strictn['pool'].')';
            echo '</em></p>';
        }

        echo '<h2>Results</h2>';
        echo '<table class="widefat striped"><thead><tr><th>Title</th><th>Score</th><th>Why</th><th>Link</th></tr></thead><tbody>';
        foreach ($results as $r) {
            echo '<tr>';
            echo '<td>'.esc_html($r['title']).'</td>';
            echo '<td>'.(int)$r['ai_score'].'/100</td>';
            echo '<td>'.esc_html($r['ai_why']).'</td>';
            echo '<td><a href="'.esc_url($r['url']).'" target="_blank" rel="noopener">View</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
    echo '</div>';
}

/* =============================================================================
 * HELPERS
 * ============================================================================= */

function gvpf_product_parent_or_self($product) {
    if ( $product && is_a($product, 'WC_Product_Variation') ) {
        $parent_id = $product->get_parent_id();
        if ( $parent_id ) {
            $parent = wc_get_product($parent_id);
            if ( $parent ) return $parent;
        }
    }
    return $product;
}
