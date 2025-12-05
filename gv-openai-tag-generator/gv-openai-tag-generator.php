<?php
/**
 * Plugin Name: GV - OpenAI Tag Generator
 * Description: Adds a button in the post editor to generate and assign relevant tags using OpenAI.
 * Version:     1.0.2
 * Author:      Gemini Valve
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* SETTINGS */
add_action( 'admin_init', function () {
    register_setting( 'gv_openai_tagger', 'gv_openai_api_key', [
        'type'              => 'string',
        'sanitize_callback' => function( $v ){ return is_string($v) ? trim( $v ) : ''; },
        'default'           => '',
    ] );
} );

add_action( 'admin_menu', function () {
    add_options_page(
        'OpenAI Tagger',
        'OpenAI Tagger',
        'manage_options',
        'gv-openai-tagger',
        function () {
            ?>
            <div class="wrap">
                <h1>OpenAI Tagger</h1>
                <form method="post" action="options.php">
                    <?php
                    settings_fields( 'gv_openai_tagger' );
                    $key = get_option( 'gv_openai_api_key', '' );
                    ?>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="gv_openai_api_key">OpenAI API Key</label></th>
                            <td>
                                <input type="password" id="gv_openai_api_key" name="gv_openai_api_key" value="<?php echo esc_attr( $key ); ?>" class="regular-text" placeholder="sk-..." />
                                <p class="description">Paste a valid OpenAI API key.</p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>
            <?php
        }
    );
} );

/* REST: generate (and optionally apply) tags */
add_action( 'rest_api_init', function () {
    register_rest_route( 'gv-openai/v1', '/tags', [
        'methods'             => 'POST',
        'permission_callback' => function( WP_REST_Request $req ) {
            $post_id = intval( $req->get_param('post_id') );
            if ( $post_id > 0 ) {
                return current_user_can( 'edit_post', $post_id );
            }
            return current_user_can( 'edit_posts' );
        },
        'args' => [
            'post_id' => [ 'type' => 'integer', 'required' => false ],
            'title'   => [ 'type' => 'string',  'required' => true ],
            'content' => [ 'type' => 'string',  'required' => false ],
            'mode'    => [ 'type' => 'string',  'required' => false, 'enum' => [ 'append', 'replace' ] ],
            'limit'   => [ 'type' => 'integer', 'required' => false ],
        ],
        'callback' => function( WP_REST_Request $req ) {
            $api_key = get_option( 'gv_openai_api_key', '' );
            if ( empty( $api_key ) ) {
                return new WP_Error( 'no_api_key', 'OpenAI API key is not configured (Settings → OpenAI Tagger).', [ 'status' => 400 ] );
            }

            $post_id = intval( $req->get_param('post_id') ?: 0 );
            $title   = wp_strip_all_tags( (string) $req->get_param('title') );
            $content = wp_kses_post( (string) $req->get_param('content') );
            $mode    = $req->get_param('mode') ?: 'append';
            $limit   = intval( $req->get_param('limit') ?: 8 );
            $limit   = max( 3, min( 12, $limit ) );

// vanaf hier!!!!

// Fallbacks if editor didn't send anything
if ( $title === '' || $content === '' ) {
    if ( $post_id > 0 ) {
        $p = get_post( $post_id );
        if ( $p && $p instanceof WP_Post ) {
            if ( $title === '' )   { $title = wp_strip_all_tags( $p->post_title ); }
            if ( $content === '' ) { 
                // Prefer full content, fallback to excerpt
                $fallback = $p->post_content !== '' ? $p->post_content : $p->post_excerpt;
                $content  = wp_kses_post( $fallback );
            }
        }
    }
}

// Fix voor title 
if ( $title === '' && $content === '' ) {
    return new WP_Error(
        'no_input',
        'Nothing to analyze. Add a title or some content, or Save draft once so I can read the post.',
        [ 'status' => 400 ]
    );
}

// Trim very long content to keep requests light (optional)
if ( strlen( $content ) > 12000 ) {
    $content = mb_substr( $content, 0, 12000 );
}


// Einde Fix voor title 






            $prompt = "You are a tagging assistant for a WordPress blog. "
                    . "Given the post title and content, return between 3 and {$limit} highly relevant tags. "
                    . "Rules: output ONLY a single comma-separated line of tags; no extra text; no hashtags; "
                    . "tags should be concise (1–4 words), English; avoid duplicates.\n\n"
                    . "TITLE: {$title}\n\nCONTENT:\n" . wp_strip_all_tags( $content );

            $body = [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [ 'role' => 'system', 'content' => 'You generate clean, relevant WordPress post tags.' ],
                    [ 'role' => 'user',   'content' => $prompt ],
                ],
                'temperature' => 0.2,
            ];

            $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                ],
                'body'    => wp_json_encode( $body ),
                'timeout' => 30,
            ] );

            if ( is_wp_error( $response ) ) {
                return new WP_Error( 'openai_failed', $response->get_error_message(), [ 'status' => 502 ] );
            }

            $code = wp_remote_retrieve_response_code( $response );
            $json = json_decode( wp_remote_retrieve_body( $response ), true );

            if ( $code < 200 || $code >= 300 || empty( $json['choices'][0]['message']['content'] ) ) {
                $msg = ! empty( $json['error']['message'] ) ? $json['error']['message'] : 'Unexpected OpenAI response.';
                return new WP_Error( 'openai_bad_response', $msg, [ 'status' => 502 ] );
            }

            $raw   = trim( $json['choices'][0]['message']['content'] );
            $parts = array_filter( array_map( function( $t ) {
                $t = trim( wp_strip_all_tags( $t ) );
                $t = preg_replace( '/^[#•\-\s]+/u', '', $t );
                return $t;
            }, preg_split( '/,|\n/', $raw ) ) );

            $uniq = [];
            foreach ( $parts as $p ) {
                $k = strtolower( $p );
                if ( ! isset( $uniq[ $k ] ) && $p !== '' ) {
                    $uniq[ $k ] = $p;
                }
            }
            $tags = array_values( $uniq );

            if ( empty( $tags ) ) {
                return new WP_Error( 'no_tags', 'The model did not return any tags.', [ 'status' => 422 ] );
            }

            $applied = false;
            $current = [];
            if ( $post_id > 0 ) {
                if ( $mode === 'replace' ) {
                    wp_set_post_terms( $post_id, $tags, 'post_tag', false );
                } else {
                    foreach ( $tags as $t ) {
                        wp_set_post_terms( $post_id, [ $t ], 'post_tag', true );
                    }
                }
                $applied = true;
                $current = wp_get_post_terms( $post_id, 'post_tag', [ 'fields' => 'names' ] );
            }

            return [
                'added'        => array_values( $tags ),
                'current'      => array_values( $current ),
                'applied'      => $applied,
                'needs_save'   => ( $post_id <= 0 ),
                'message'      => $post_id > 0 ? 'Tags updated successfully.' : 'Generated tags only. Save the post first to apply.',
                'raw'          => $raw,
            ];
        }
    ] );
} );

/* Gutenberg panel */
add_action( 'enqueue_block_editor_assets', function () {
    $nonce = wp_create_nonce( 'wp_rest' );
    $data  = [
        'restUrl' => esc_url_raw( rest_url( 'gv-openai/v1/tags' ) ),
        'nonce'   => $nonce,
    ];
    wp_register_script(
        'gv-openai-tagger',
        '', // inline below
        [ 'wp-plugins', 'wp-edit-post', 'wp-components', 'wp-element', 'wp-data', 'wp-api-fetch' ],
        '1.0.2',
        true
    );
    wp_enqueue_script( 'gv-openai-tagger' );
    wp_add_inline_script( 'gv-openai-tagger', 'window.GV_OPENAI_TAGGER=' . wp_json_encode( $data ) . ';' );

    // ✅ Correct function name:
    wp_add_inline_script( 'gv-openai-tagger', <<<JS
( function( wp ) {
    const { registerPlugin } = wp.plugins || {};
    const { PluginDocumentSettingPanel } = wp.editPost || {};
    const { Button, Notice, ToggleControl, Spinner } = wp.components || {};
    const { useState } = wp.element || {};
    const { select } = wp.data || {};
    const apiFetch = wp.apiFetch || window.wp.apiFetch;

    if ( ! registerPlugin || ! PluginDocumentSettingPanel || ! apiFetch ) { return; }

    const Panel = () => {
        const [ busy, setBusy ] = useState(false);
        const [ msg, setMsg ] = useState('');
        const [ modeReplace, setModeReplace ] = useState(false);
        const [ added, setAdded ] = useState([]);
        const [ current, setCurrent ] = useState([]);
        const [ info, setInfo ] = useState('');

        const doGenerate = async () => {
            setBusy(true); setMsg(''); setInfo(''); setAdded([]); setCurrent([]);

            const postId  = select('core/editor').getCurrentPostId() || 0;
            const title   = select('core/editor').getEditedPostAttribute('title') || '';
            const content = select('core/editor').getEditedPostAttribute('content') || '';

            try {
                const res = await apiFetch({
                    url: window.GV_OPENAI_TAGGER.restUrl,
                    method: 'POST',
                    headers: { 'X-WP-Nonce': window.GV_OPENAI_TAGGER.nonce, 'Content-Type':'application/json' },
                    body: JSON.stringify({
                        post_id: postId,
                        title,
                        content,
                        mode: modeReplace ? 'replace' : 'append',
                        limit: 8
                    })
                });

                setAdded(res.added || []);
                setCurrent(res.current || []);
                setMsg(res.message || 'Done.');
                if (res.needs_save) setInfo('Save draft or publish to apply tags automatically.');
            } catch (e) {
                let text = 'Failed to generate tags.';
                if (e && e.message) text += ' ' + e.message;
                setMsg(text);
            } finally {
                setBusy(false);
            }
        };

        return wp.element.createElement(
            PluginDocumentSettingPanel,
            { name: 'gv-openai-tags', title: 'OpenAI Tagger', className: 'gv-openai-tagger-panel' },
            wp.element.createElement(ToggleControl, {
                label: 'Replace existing tags (instead of append)',
                checked: modeReplace,
                onChange: () => setModeReplace(!modeReplace)
            }),
            wp.element.createElement(Button, {
                variant: 'primary',
                onClick: doGenerate,
                disabled: busy
            }, busy ? wp.element.createElement(Spinner) : 'Generate Tags (OpenAI)'),
            msg && wp.element.createElement(Notice, { status: 'info', isDismissible: true }, msg),
            info && wp.element.createElement('p', { style:{ marginTop:'6px', fontStyle:'italic' } }, info),
            (added && added.length > 0) && wp.element.createElement('div', null,
                wp.element.createElement('strong', null, 'Suggested:'),
                wp.element.createElement('div', null, added.join(', '))
            ),
            (current && current.length > 0) && wp.element.createElement('div', { style:{marginTop:'8px'} },
                wp.element.createElement('strong', null, 'Current tags:'),
                wp.element.createElement('div', null, current.join(', '))
            )
        );
    };

    registerPlugin('gv-openai-tagger', { render: Panel, icon: 'tag' });
} )( window.wp );
JS
    );
} );

/* Classic Editor meta box */
add_action( 'add_meta_boxes', function () {
    add_meta_box(
        'gv_openai_tags_box',
        'OpenAI Tagger',
        function( WP_Post $post ) {
            wp_nonce_field( 'gv_openai_box', 'gv_openai_box_nonce' );
            echo '<p>Generate tags using OpenAI. If this is a new post, click “Save draft” first to apply tags automatically.</p>';
            echo '<button type="button" class="button button-primary" id="gv-generate-tags">Generate Tags (OpenAI)</button>';
            echo '<div id="gv-openai-result" style="margin-top:8px;"></div>';

            $nonce = wp_create_nonce( 'wp_rest' );
            $rest  = esc_url( rest_url( 'gv-openai/v1/tags' ) );
            $pid   = (int) $post->ID;
            ?>
            <script>
                (function(){
                    const btn = document.getElementById('gv-generate-tags');
                    const out = document.getElementById('gv-openai-result');
                    if(!btn) return;
                    btn.addEventListener('click', async function(){
                        out.textContent = 'Generating...';
                        try{
                            const titleEl = document.getElementById('title');
                            const title = titleEl ? titleEl.value : '';
                            const content = (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden())
                                ? tinyMCE.activeEditor.getContent()
                                : (document.getElementById('content') ? document.getElementById('content').value : '');

                            const res = await fetch('<?php echo $rest; ?>', {
                                method: 'POST',
                                headers: {'Content-Type':'application/json','X-WP-Nonce':'<?php echo esc_js( $nonce ); ?>'},
                                body: JSON.stringify({ post_id: <?php echo $pid; ?>, title, content, mode:'append', limit:8 })
                            });

                            let data;
                            try { data = await res.json(); } catch(_){ data = {}; }

                            if(!res.ok){
                                const msg = (data && data.message) ? data.message : ('HTTP ' + res.status + ' ' + res.statusText);
                                throw new Error(msg);
                            }

                            out.innerHTML =
                                '<div class="notice notice-success inline"><p>' +
                                (data.message || 'Tags generated.') +
                                '</p></div>' +
                                '<p><strong>Suggested:</strong> ' + (data.added||[]).join(', ') + '</p>' +
                                (data.current && data.current.length ? '<p><strong>Current:</strong> ' + data.current.join(', ') + '</p>' : '') +
                                (data.needs_save ? '<p><em>Save draft or publish to apply tags.</em></p>' : '');
                        }catch(e){
                            out.innerHTML = '<div class="notice notice-error inline"><p>Failed: ' + (e.message||e) + '</p></div>';
                        }
                    });
                })();
            </script>
            <?php
        },
        'post',
        'side',
        'high'
    );
} );
