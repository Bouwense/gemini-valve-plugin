<?php
/**
 * Plugin Name: GV - OpenAI Post Planner
 * Description: Adds a Post Planner to the editor: generate an SEO plan (editable) and then generate post content from it using OpenAI. Also Adds the OpenAI key manager
 * Version:     1.1.4
 * Author:      Gemini Valve
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ────────────────────────────────────────────────────────────────────────── */
/* Settings                                                                   */
/* ────────────────────────────────────────────────────────────────────────── */

add_action( 'admin_init', function () {
    register_setting( 'gv_openai_content_assistant', 'gv_openai_api_key', [
        'type'              => 'string',
        'sanitize_callback' => function( $v ){ return is_string($v) ? trim( $v ) : ''; },
        'default'           => '',
    ] );
} );

add_action( 'admin_menu', function () {
    add_options_page(
        'OpenAI Content Assistant',
        'OpenAI Content Assistant',
        'manage_options',
        'gv-openai-content-assistant',
        function () {
            ?>
            <div class="wrap">
                <h1>OpenAI Content Assistant</h1>
                <form method="post" action="options.php">
                    <?php
                    settings_fields( 'gv_openai_content_assistant' );
                    $key = get_option( 'gv_openai_api_key', '' );
                    ?>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="gv_openai_api_key">OpenAI API Key</label></th>
                            <td>
                                <input type="password" id="gv_openai_api_key" name="gv_openai_api_key" value="<?php echo esc_attr( $key ); ?>" class="regular-text" placeholder="sk-..." />
                                <p class="description">API billing must be enabled for this key.</p>
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

/* ────────────────────────────────────────────────────────────────────────── */
/* Post Meta for Plan                                                         */
/* ────────────────────────────────────────────────────────────────────────── */

add_action( 'init', function () {
    register_post_meta( 'post', '_gv_post_plan', [
        'show_in_rest'  => true,
        'single'        => true,
        'type'          => 'string',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); },
    ] );
} );

/* ────────────────────────────────────────────────────────────────────────── */
/* REST: /plan, /plan/save, /content                                          */
/* ────────────────────────────────────────────────────────────────────────── */

add_action( 'rest_api_init', function () {

    register_rest_route( 'gv-openai/v1', '/plan', [
        'methods'             => 'POST',
        'permission_callback' => function( WP_REST_Request $req ) {
            $post_id = intval( $req->get_param('post_id') );
            return $post_id > 0 ? current_user_can( 'edit_post', $post_id ) : current_user_can( 'edit_posts' );
        },
        'args' => [
            'post_id' => [ 'type' => 'integer', 'required' => false ],
            'title'   => [ 'type' => 'string',  'required' => false ],
            'content' => [ 'type' => 'string',  'required' => false ],
            'language'=> [ 'type' => 'string',  'required' => false, 'enum' => ['en','nl'] ],
        ],
        'callback' => function( WP_REST_Request $req ) {
            $api_key = get_option( 'gv_openai_api_key', '' );
            if ( empty( $api_key ) ) {
                return new WP_Error( 'no_api_key', 'OpenAI API key is not configured (Settings → OpenAI Content Assistant).', [ 'status' => 400 ] );
            }

            $post_id = intval( $req->get_param('post_id') ?: 0 );
            $title   = wp_strip_all_tags( (string) $req->get_param('title') );
            $content = wp_kses_post( (string) $req->get_param('content') );
            $lang    = $req->get_param('language') ?: 'en';

            // Fallback to saved post values when missing
            if ( ($title === '' || $content === '') && $post_id > 0 ) {
                $p = get_post( $post_id );
                if ( $p ) {
                    if ( $title === '' )   { $title = wp_strip_all_tags( $p->post_title ); }
                    if ( $content === '' ) { $content = $p->post_content !== '' ? $p->post_content : $p->post_excerpt; }
                }
            }
            if ( $title === '' && $content === '' ) {
                return new WP_Error( 'no_input', 'Nothing to analyze. Add a title or some content, or Save draft once.', [ 'status' => 400 ] );
            }

            $content = wp_strip_all_tags( (string) $content );
            if ( strlen( $content ) > 12000 ) { $content = mb_substr( $content, 0, 12000 ); }

            $locale_line = $lang === 'nl' ? "Write the plan in Dutch." : "Write the plan in English.";
            $prompt = $locale_line . " Create an SEO post plan given the title and content. "
                    . "Return ONLY a Markdown plan with these headings:\n\n"
                    . "Primary keyword:\nSecondary keywords: (comma-separated)\nSearch intent:\nSuggested outline:\n- H2: ...\n  - H3: ... (optional)\nCTA:\n\n"
                    . "Constraints: Practical, B2B-friendly, concise.\n\n"
                    . "TITLE: {$title}\n\nCONTENT PREVIEW:\n{$content}";

            $body = [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [ 'role' => 'system', 'content' => 'You generate tight, practical SEO content plans for WordPress posts.' ],
                    [ 'role' => 'user',   'content' => $prompt ],
                ],
                'temperature' => 0.3,
            ];

            $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                ],
                'body'    => wp_json_encode( $body ),
                'timeout' => 45,
            ] );

            if ( is_wp_error( $response ) ) {
                return new WP_Error( 'openai_failed', $response->get_error_message(), [ 'status' => 502 ] );
            }

            $code = wp_remote_retrieve_response_code( $response );
            $json = json_decode( wp_remote_retrieve_body( $response ), true );
            $text = $json['choices'][0]['message']['content'] ?? '';

            if ( $code === 429 && (($json['error']['code'] ?? '') === 'insufficient_quota') ) {
                return new WP_Error( 'openai_quota', 'OpenAI API quota is exhausted for this key. Add billing or raise your monthly limit, then try again.', [ 'status' => 429 ] );
            }

            if ( $code < 200 || $code >= 300 || $text === '' ) {
                $msg = $json['error']['message'] ?? 'Unexpected OpenAI response.';
                return new WP_Error( 'openai_bad_response', $msg, [ 'status' => 502 ] );
            }

            return [ 'plan_md' => trim( $text ) ];
        }
    ] );

    register_rest_route( 'gv-openai/v1', '/plan/save', [
        'methods'             => 'POST',
        'permission_callback' => function( WP_REST_Request $req ) {
            $post_id = intval( $req->get_param('post_id') );
            return $post_id > 0 && current_user_can( 'edit_post', $post_id );
        },
        'args' => [
            'post_id' => [ 'type' => 'integer', 'required' => true ],
            'plan'    => [ 'type' => 'string',  'required' => true ],
        ],
        'callback' => function( WP_REST_Request $req ) {
            $post_id = intval( $req->get_param('post_id') );
            $plan    = (string) $req->get_param('plan');
            update_post_meta( $post_id, '_gv_post_plan', wp_kses_post( $plan ) );
            return [ 'saved' => true ];
        }
    ] );

    register_rest_route( 'gv-openai/v1', '/content', [
        'methods'             => 'POST',
        'permission_callback' => function( WP_REST_Request $req ) {
            $post_id = intval( $req->get_param('post_id') );
            return $post_id > 0 ? current_user_can( 'edit_post', $post_id ) : current_user_can( 'edit_posts' );
        },
        'args' => [
            'post_id' => [ 'type' => 'integer', 'required' => false ],
            'title'   => [ 'type' => 'string',  'required' => false ],
            'plan'    => [ 'type' => 'string',  'required' => true ],
            'language'=> [ 'type' => 'string',  'required' => false, 'enum' => ['en','nl'] ],
            'words'   => [ 'type' => 'integer', 'required' => false ],
        ],
        'callback' => function( WP_REST_Request $req ) {
            $api_key = get_option( 'gv_openai_api_key', '' );
            if ( empty( $api_key ) ) {
                return new WP_Error( 'no_api_key', 'OpenAI API key is not configured.', [ 'status' => 400 ] );
            }

            $post_id = intval( $req->get_param('post_id') ?: 0 );
            $title   = wp_strip_all_tags( (string) $req->get_param('title') );
            $plan    = (string) $req->get_param('plan');
            $lang    = $req->get_param('language') ?: 'en';
            $words   = intval( $req->get_param('words') ?: 1000 );
            $words   = max( 500, min( 2000, $words ) );

            if ( $title === '' && $post_id > 0 ) {
                $p = get_post( $post_id );
                if ( $p ) { $title = wp_strip_all_tags( $p->post_title ); }
            }
            if ( trim( $plan ) === '' ) {
                return new WP_Error( 'no_plan', 'Plan is empty. Generate or paste a plan first.', [ 'status' => 400 ] );
            }

            $locale_line = $lang === 'nl' ? "Write the article in Dutch." : "Write the article in English.";
            $prompt = $locale_line . " Using the following plan and title, write a WordPress-friendly article body in clean HTML. "
                    . "Do NOT include an <h1>. Start with a short intro paragraph. "
                    . "Use <h2>/<h3> per the outline; standard <p>, <ul>, <ol>; practical B2B tone. "
                    . "Target ~{$words} words. Include the CTA at the end.\n\n"
                    . "TITLE: {$title}\n\nPLAN (Markdown):\n{$plan}";

            $body = [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [ 'role' => 'system', 'content' => 'You write structured, accurate B2B articles for WordPress. You output clean, semantic HTML without <h1>.' ],
                    [ 'role' => 'user',   'content' => $prompt ],
                ],
                'temperature' => 0.35,
            ];

            $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                ],
                'body'    => wp_json_encode( $body ),
                'timeout' => 60,
            ] );

            if ( is_wp_error( $response ) ) {
                return new WP_Error( 'openai_failed', $response->get_error_message(), [ 'status' => 502 ] );
            }

            $code = wp_remote_retrieve_response_code( $response );
            $json = json_decode( wp_remote_retrieve_body( $response ), true );
            $html = $json['choices'][0]['message']['content'] ?? '';

            if ( $code === 429 && (($json['error']['code'] ?? '') === 'insufficient_quota') ) {
                return new WP_Error( 'openai_quota', 'OpenAI API quota is exhausted for this key. Add billing or raise your monthly limit, then try again.', [ 'status' => 429 ] );
            }

            if ( $code < 200 || $code >= 300 || $html === '' ) {
                $msg = $json['error']['message'] ?? 'Unexpected OpenAI response.';
                return new WP_Error( 'openai_bad_response', $msg, [ 'status' => 502 ] );
            }

            // Strip code fences if present
            $html = trim( preg_replace( '/^```(?:html)?\s*|\s*```$/', '', $html ) );

            return [ 'html' => $html ];
        }
    ] );
} );

/* ────────────────────────────────────────────────────────────────────────── */
/* Gutenberg Panel: rawHandler + fallbacks + Test insert                      */
/* ────────────────────────────────────────────────────────────────────────── */

add_action( 'enqueue_block_editor_assets', function () {
    $nonce = wp_create_nonce( 'wp_rest' );
    $data  = [
        'restPlan'     => esc_url_raw( rest_url( 'gv-openai/v1/plan' ) ),
        'restPlanSave' => esc_url_raw( rest_url( 'gv-openai/v1/plan/save' ) ),
        'restContent'  => esc_url_raw( rest_url( 'gv-openai/v1/content' ) ),
        'nonce'        => $nonce,
        'lang'         => ( get_user_locale() && strpos( get_user_locale(), 'nl' ) === 0 ) ? 'nl' : 'en',
    ];
    wp_register_script(
        'gv-openai-post-planner',
        '',
        [ 'wp-plugins', 'wp-edit-post', 'wp-components', 'wp-element', 'wp-data', 'wp-api-fetch', 'wp-blocks', 'wp-block-editor', 'wp-notices' ],
        '1.1.4',
        true
    );
    wp_enqueue_script( 'gv-openai-post-planner' );
    wp_add_inline_script( 'gv-openai-post-planner', 'window.GV_OPENAI_PP=' . wp_json_encode( $data ) . ';' );

    wp_add_inline_script( 'gv-openai-post-planner', <<<JS
(function(wp){
    const { registerPlugin } = wp.plugins || {};
    const { PluginDocumentSettingPanel } = wp.editPost || {};
    const { Button, Notice, Spinner, TextareaControl, ToggleControl, SelectControl } = wp.components || {};
    const { useState, useEffect } = wp.element || {};
    const { select, dispatch } = wp.data || {};
    const apiFetch = wp.apiFetch || window.wp.apiFetch;

    if (!registerPlugin || !PluginDocumentSettingPanel || !apiFetch) return;

    const Panel = () => {
        const [plan, setPlan]   = useState('');
        const [busy, setBusy]   = useState(false);
        const [msg, setMsg]     = useState('');
        const [append, setAppend] = useState(false);
        const [lang, setLang]   = useState(window.GV_OPENAI_PP.lang || 'en');

        useEffect(() => {
            const meta = select('core/editor').getEditedPostAttribute('meta') || {};
            setPlan(meta['_gv_post_plan'] || '');
        }, []);

        const inform = (text) => {
            setMsg(text);
            const n = wp.data && wp.data.dispatch && wp.data.dispatch('core/notices');
            if (n) n.createNotice('info', text, { isDismissible: true });
        };

        const savePlanToMeta = (value) => {
            setPlan(value);
            const meta = Object.assign({}, select('core/editor').getEditedPostAttribute('meta') || {});
            meta['_gv_post_plan'] = value;
            dispatch('core/editor').editPost({ meta });
        };

        const generatePlan = async () => {
            setBusy(true); setMsg('');
            const postId  = select('core/editor').getCurrentPostId() || 0;
            const title   = select('core/editor').getEditedPostAttribute('title') || '';
            const content = select('core/editor').getEditedPostAttribute('content') || '';
            try {
                const res = await apiFetch({
                    url: window.GV_OPENAI_PP.restPlan,
                    method: 'POST',
                    headers: {'X-WP-Nonce': window.GV_OPENAI_PP.nonce, 'Content-Type':'application/json'},
                    body: JSON.stringify({ post_id: postId, title, content, language: lang })
                });
                if (res.plan_md) { savePlanToMeta(res.plan_md); inform('Plan generated.'); }
                else { inform('No plan returned.'); }
            } catch(e) {
                inform('Failed: ' + (e && e.message ? e.message : 'Request error'));
            } finally {
                setBusy(false);
            }
        };

        // Convert HTML to blocks reliably
        const htmlToBlocks = (html) => {
            try {
                if (wp.blocks && wp.blocks.rawHandler) {
                    return wp.blocks.rawHandler({ HTML: html });
                }
                if (wp.blocks && wp.blocks.parse) {
                    return wp.blocks.parse(html);
                }
            } catch(e) {}
            return null;
        };

        const insertBlocksSmart = (blocks, html, append) => {
            const beDispatch = wp.data.dispatch('core/block-editor');
            const beSelect   = wp.data.select('core/block-editor');
            const beforeCnt  = (beSelect.getBlocks() || []).length;

            if (blocks && blocks.length) {
                if (append) {
                    beDispatch.insertBlocks(blocks, beforeCnt);
                } else if (beDispatch.resetBlocks) {
                    beDispatch.resetBlocks(blocks);
                } else if (beDispatch.replaceBlocks) {
                    const allIds = (beSelect.getBlocks() || []).map(b => b.clientId);
                    if (allIds.length) beDispatch.replaceBlocks(allIds, blocks); else beDispatch.insertBlocks(blocks, 0);
                } else {
                    beDispatch.insertBlocks(blocks, 0);
                }
            } else if (wp.blocks && wp.blocks.createBlock) {
                // Fallback: single HTML block
                const htmlBlock = wp.blocks.createBlock('core/html', { content: html });
                if (append) beDispatch.insertBlocks([htmlBlock], beforeCnt);
                else if (beDispatch.resetBlocks) beDispatch.resetBlocks([htmlBlock]);
                else beDispatch.insertBlocks([htmlBlock], 0);
            } else {
                // Final fallback: raw content
                const current = wp.data.select('core/editor').getEditedPostAttribute('content') || '';
                const newContent = append ? (current + "\\n\\n" + html) : html;
                wp.data.dispatch('core/editor').editPost({ content: newContent });
            }

            const afterCnt = (beSelect.getBlocks() || []).length;
            return { beforeCnt, afterCnt };
        };

        const generateContent = async () => {
            if (!plan || plan.trim() === '') { inform('Add or generate a plan first.'); return; }
            setBusy(true); setMsg('');
            const postId  = select('core/editor').getCurrentPostId() || 0;
            const title   = select('core/editor').getEditedPostAttribute('title') || '';

            try {
                const res = await apiFetch({
                    url: window.GV_OPENAI_PP.restContent,
                    method: 'POST',
                    headers: {'X-WP-Nonce': window.GV_OPENAI_PP.nonce, 'Content-Type':'application/json'},
                    body: JSON.stringify({ post_id: postId, title, plan, language: lang, words: 1000 })
                });
                if (!res.html) { inform('No content returned.'); return; }

                const html = res.html;
                const blocks = htmlToBlocks(html);
                const { beforeCnt, afterCnt } = insertBlocksSmart(blocks, html, append);

                if (afterCnt > beforeCnt || beforeCnt === 0) {
                    inform('Content inserted into editor.');
                } else {
                    const current = select('core/editor').getEditedPostAttribute('content') || '';
                    const newContent = append ? (current + "\\n\\n" + html) : html;
                    wp.data.dispatch('core/editor').editPost({ content: newContent });
                    inform('Content inserted (raw HTML fallback).');
                }
            } catch(e) {
                inform('Failed: ' + (e && e.message ? e.message : 'Request error'));
            } finally {
                setBusy(false);
            }
        };

        // Test insert (no API): proves block insertion path
        const testInsert = () => {
            const beDispatch = wp.data.dispatch('core/block-editor');
            const beSelect   = wp.data.select('core/block-editor');
            const before = (beSelect.getBlocks() || []).length;
            if (wp.blocks && wp.blocks.createBlock) {
                const h = wp.blocks.createBlock('core/heading',  { content: 'Test Heading', level: 2 });
                const p = wp.blocks.createBlock('core/paragraph', { content: 'This is a test paragraph inserted by the Post Planner.' });
                beDispatch.insertBlocks([h, p], before);
            } else {
                const html = '<h2>Test Heading</h2><p>This is a test paragraph inserted by the Post Planner.</p>';
                const blocks = htmlToBlocks(html);
                insertBlocksSmart(blocks, html, true);
            }
            const after = (beSelect.getBlocks() || []).length;
            inform(after > before ? 'Test blocks inserted.' : 'Test insert failed.');
        };

        return wp.element.createElement(
            PluginDocumentSettingPanel,
            { name: 'gv-openai-post-planner', title: 'OpenAI Post Planner', className: 'gv-openai-post-planner' },
            wp.element.createElement(SelectControl, {
                label: 'Language',
                value: lang,
                options: [ {label:'English', value:'en'}, {label:'Nederlands', value:'nl'} ],
                onChange: (v) => setLang(v)
            }),
            wp.element.createElement(TextareaControl, {
                label: 'Plan (editable)',
                help: 'Primary keyword, secondary keywords, search intent, outline, CTA.',
                rows: 12,
                value: plan,
                onChange: savePlanToMeta
            }),
            wp.element.createElement(ToggleControl, {
                label: 'Append when generating content (instead of replace)',
                checked: append,
                onChange: () => setAppend(!append)
            }),
            wp.element.createElement('div', { style:{ display:'flex', gap:'8px', marginTop:'6px', flexWrap:'wrap' } },
                wp.element.createElement(Button, { variant:'secondary', onClick: generatePlan, disabled: busy },
                    busy ? wp.element.createElement(Spinner) : 'Generate Plan'
                ),
                wp.element.createElement(Button, { variant:'primary', onClick: generateContent, disabled: busy || !plan.trim() },
                    busy ? wp.element.createElement(Spinner) : 'Generate Content'
                ),
                wp.element.createElement(Button, { variant:'tertiary', onClick: testInsert, disabled: busy }, 'Test insert')
            ),
            msg && wp.element.createElement(Notice, { status:'info', isDismissible:true }, msg)
        );
    };

    registerPlugin('gv-openai-post-planner', { render: Panel, icon: 'editor-kitchensink' });
})(window.wp);
JS
    );
} );

/* ────────────────────────────────────────────────────────────────────────── */
/* Classic Editor meta box + Test insert                                      */
/* ────────────────────────────────────────────────────────────────────────── */

add_action( 'add_meta_boxes', function () {
    add_meta_box(
        'gv_openai_plan_box',
        'OpenAI Post Planner',
        function( WP_Post $post ) {
            $plan  = get_post_meta( $post->ID, '_gv_post_plan', true );
            $nonce = wp_create_nonce( 'wp_rest' );
            $epPlan    = esc_url( rest_url( 'gv-openai/v1/plan' ) );
            $epPlanSave= esc_url( rest_url( 'gv-openai/v1/plan/save' ) );
            $epContent = esc_url( rest_url( 'gv-openai/v1/content' ) );
            $lang      = ( get_user_locale() && strpos( get_user_locale(), 'nl' ) === 0 ) ? 'nl' : 'en';
            ?>
            <p><em>Create a plan, edit it, then generate content into the editor.</em></p>
            <p>
                <label for="gv_plan_lang"><strong>Language</strong></label><br/>
                <select id="gv_plan_lang">
                    <option value="en" <?php selected('en',$lang); ?>>English</option>
                    <option value="nl" <?php selected('nl',$lang); ?>>Nederlands</option>
                </select>
            </p>
            <textarea id="gv_post_plan" style="width:100%;min-height:220px;"><?php echo esc_textarea( $plan ); ?></textarea>
            <p><label><input type="checkbox" id="gv_append"> Append content (instead of replace)</label></p>
            <p>
                <button type="button" class="button" id="gv_gen_plan">Generate Plan</button>
                <button type="button" class="button button-primary" id="gv_gen_content">Generate Content</button>
                <button type="button" class="button" id="gv_test_insert">Test insert</button>
                <span id="gv_pp_busy" style="display:none;margin-left:8px;">Generating…</span>
            </p>
            <div id="gv_pp_msg" class="notice inline" style="display:none;"></div>
            <script>
            (function(){
                const nonce   = '<?php echo esc_js( $nonce ); ?>';
                const planEP  = '<?php echo $epPlan; ?>';
                const planSave= '<?php echo $epPlanSave; ?>';
                const contentEP='<?php echo $epContent; ?>';
                const postId  = <?php echo (int) $post->ID; ?>;

                const $plan   = document.getElementById('gv_post_plan');
                const $append = document.getElementById('gv_append');
                const $lang   = document.getElementById('gv_plan_lang');
                const $msg    = document.getElementById('gv_pp_msg');
                const $busy   = document.getElementById('gv_pp_busy');

                function showMsg(txt, ok){
                    $msg.style.display='block';
                    $msg.className = 'notice inline ' + (ok ? 'notice-success' : 'notice-error');
                    $msg.innerHTML = '<p>' + txt + '</p>';
                }
                async function savePlan(){
                    try{
                        await fetch(planSave, {
                            method:'POST',
                            headers:{'Content-Type':'application/json','X-WP-Nonce':nonce},
                            body: JSON.stringify({ post_id: postId, plan: $plan.value })
                        });
                    }catch(e){}
                }
                function triggerEditorChange(){
                    if (window.tinyMCE && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()){
                        tinyMCE.triggerSave();
                        tinyMCE.activeEditor.fire('change');
                    } else {
                        const el = document.getElementById('content');
                        if (el){
                            el.dispatchEvent(new Event('input', {bubbles:true}));
                            el.dispatchEvent(new Event('change', {bubbles:true}));
                        }
                    }
                }

                document.getElementById('gv_gen_plan').addEventListener('click', async function(){
                    $busy.style.display='inline'; $msg.style.display='none';
                    try{
                        const titleEl = document.getElementById('title');
                        const title   = titleEl ? titleEl.value : '';
                        const content = (typeof tinyMCE!=='undefined' && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden())
                            ? tinyMCE.activeEditor.getContent({format:'text'})
                            : (document.getElementById('content') ? document.getElementById('content').value : '');

                        const res = await fetch(planEP, {
                            method:'POST',
                            headers:{'Content-Type':'application/json','X-WP-Nonce':nonce},
                            body: JSON.stringify({ post_id: postId, title, content, language: $lang.value })
                        });
                        const data = await res.json();
                        if(!res.ok){ throw new Error(data && data.message ? data.message : ('HTTP '+res.status)); }
                        $plan.value = data.plan_md || '';
                        await savePlan();
                        showMsg('Plan generated and saved.', true);
                    }catch(e){
                        showMsg('Failed: ' + (e.message||e), false);
                    }finally{
                        $busy.style.display='none';
                    }
                });

                document.getElementById('gv_gen_content').addEventListener('click', async function(){
                    if(!$plan.value.trim()){ showMsg('Add or generate a plan first.', false); return; }
                    $busy.style.display='inline'; $msg.style.display='none';
                    try{
                        const titleEl = document.getElementById('title');
                        const title   = titleEl ? titleEl.value : '';
                        const res = await fetch(contentEP, {
                            method:'POST',
                            headers:{'Content-Type':'application/json','X-WP-Nonce':nonce},
                            body: JSON.stringify({ post_id: postId, title, plan: $plan.value, language: $lang.value, words: 1000 })
                        });
                        const data = await res.json();
                        if(!res.ok){ throw new Error(data && data.message ? data.message : ('HTTP '+res.status)); }

                        const html = data.html || '';
                        if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()){
                            const cur = tinyMCE.activeEditor.getContent();
                            tinyMCE.activeEditor.setContent( $append.checked ? (cur + "\\n\\n" + html) : html );
                            tinyMCE.triggerSave();
                        } else {
                            const el = document.getElementById('content');
                            if (el) {
                                el.value = $append.checked ? (el.value + "\\n\\n" + html) : html;
                            }
                        }
                        triggerEditorChange();
                        await savePlan();
                        showMsg('Content inserted into editor.', true);
                    }catch(e){
                        showMsg('Failed: ' + (e.message||e), false);
                    }finally{
                        $busy.style.display='none';
                    }
                });

                // Test insert button — proves editor insertion path without the API
                document.getElementById('gv_test_insert').addEventListener('click', function(){
                    const sample = '<h2>Test Heading</h2><p>This is a test paragraph inserted by the Post Planner.</p>';
                    try {
                        if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()){
                            const cur = tinyMCE.activeEditor.getContent();
                            tinyMCE.activeEditor.setContent(cur + "\\n\\n" + sample);
                            tinyMCE.triggerSave();
                            tinyMCE.activeEditor.fire('change');
                        } else {
                            const el = document.getElementById('content');
                            if (el) {
                                el.value = (el.value ? (el.value + "\\n\\n") : '') + sample;
                                el.dispatchEvent(new Event('input', {bubbles:true}));
                                el.dispatchEvent(new Event('change', {bubbles:true}));
                            }
                        }
                        showMsg('Test content inserted.', true);
                    } catch (e) {
                        showMsg('Test insert failed: ' + (e.message || e), false);
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
