<?php
/**
 * Plugin Name: GV - WooCommerce LinkedIn OAuth
 * Description: Adds a WooCommerce settings page to configure LinkedIn OAuth (Client ID/Secret), shows Redirect URI, and starts the OAuth flow to obtain an access token.
 * Author: Gemini Valve
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class GV_WC_LinkedIn_OAuth {

    const OPT_CLIENT_ID     = 'gv_li_client_id';
    const OPT_CLIENT_SECRET = 'gv_li_client_secret';
    const OPT_SCOPES        = 'gv_li_scopes';
    const OPT_ACCESS_TOKEN  = 'gv_li_access_token';
    const OPT_EXPIRES_AT    = 'gv_li_access_token_expires_at';
    const TRANSIENT_PREFIX  = 'gv_li_state_';
    const MENU_SLUG         = 'gv-linkedin-oauth';

    const AUTH_URL  = 'https://www.linkedin.com/oauth/v2/authorization';
    const TOKEN_URL = 'https://www.linkedin.com/oauth/v2/accessToken';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu' ] );
        add_action( 'admin_init', [ $this, 'maybe_save_settings' ] );
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
    }

    /** Add submenu under WooCommerce */
    public function add_menu() {
        add_submenu_page(
            'woocommerce',
            'LinkedIn OAuth',
            'LinkedIn OAuth',
            'manage_options',
            self::MENU_SLUG,
            [ $this, 'render_settings_page' ]
        );
    }

    /** Compute Redirect URI using WP REST API endpoint */
    public static function get_redirect_uri() {
        // REST callback: /wp-json/gv/v1/linkedin/callback
        return esc_url_raw( rest_url( 'gv/v1/linkedin/callback' ) );
    }

    /** Save settings */
    public function maybe_save_settings() {
        if ( ! isset( $_POST['gv_li_save_settings'] ) ) return;
        if ( ! current_user_can( 'manage_options' ) ) return;
        check_admin_referer( 'gv_li_settings' );

        $client_id     = isset($_POST['gv_li_client_id']) ? sanitize_text_field( wp_unslash($_POST['gv_li_client_id']) ) : '';
        $client_secret = isset($_POST['gv_li_client_secret']) ? sanitize_text_field( wp_unslash($_POST['gv_li_client_secret']) ) : '';
        $scopes        = isset($_POST['gv_li_scopes']) ? sanitize_text_field( wp_unslash($_POST['gv_li_scopes']) ) : 'r_liteprofile r_emailaddress';

        update_option( self::OPT_CLIENT_ID, $client_id );
        update_option( self::OPT_CLIENT_SECRET, $client_secret );
        update_option( self::OPT_SCOPES, $scopes );

        add_settings_error( self::MENU_SLUG, 'settings_saved', 'Settings saved.', 'updated' );
    }

    /** Generate a CSRF state token and persist it briefly */
    protected function create_state_token() {
        $state = wp_generate_password( 24, false, false );
        set_transient( self::TRANSIENT_PREFIX . $state, [
            'created' => time(),
            'user'    => get_current_user_id(),
        ], 15 * MINUTE_IN_SECONDS );
        return $state;
    }

    /** Build LinkedIn Authorization URL */
    protected function build_authorize_url() {
        $client_id = get_option( self::OPT_CLIENT_ID, '' );
        $scopes    = get_option( self::OPT_SCOPES, 'r_liteprofile r_emailaddress' );
        $redirect  = self::get_redirect_uri();

        if ( empty($client_id) ) return '';

        $state = $this->create_state_token();

        $args = [
            'response_type' => 'code',
            'client_id'     => $client_id,
            'redirect_uri'  => $redirect,
            'state'         => $state,
            // LinkedIn expects space-delimited scopes, which must be URL-encoded as %20
            'scope'         => $scopes,
        ];
        return esc_url_raw( add_query_arg( $args, self::AUTH_URL ) );
    }

    /** Settings screen */
    public function render_settings_page() {
        if ( ! current_user_can('manage_options') ) return;
        settings_errors( self::MENU_SLUG );

        $client_id     = get_option( self::OPT_CLIENT_ID, '' );
        $client_secret = get_option( self::OPT_CLIENT_SECRET, '' );
        $scopes        = get_option( self::OPT_SCOPES, 'r_liteprofile r_emailaddress' );
        $redirect_uri  = self::get_redirect_uri();
        $auth_url      = $this->build_authorize_url();

        $access_token  = get_option( self::OPT_ACCESS_TOKEN, '' );
        $expires_at    = (int) get_option( self::OPT_EXPIRES_AT, 0 );
        $expires_human = $expires_at ? date_i18n( get_option('date_format') . ' ' . get_option('time_format'), $expires_at ) : '';

        $status = isset($_GET['status']) ? sanitize_text_field( wp_unslash($_GET['status']) ) : '';
        if ( $status === 'connected' ) {
            echo '<div class="notice notice-success"><p>LinkedIn connected successfully.</p></div>';
        } elseif ( $status === 'error' ) {
            $msg = isset($_GET['msg']) ? sanitize_text_field( wp_unslash($_GET['msg']) ) : 'Unknown error.';
            echo '<div class="notice notice-error"><p>LinkedIn connection failed: ' . esc_html($msg) . '</p></div>';
        }

        ?>
        <div class="wrap">
            <h1>LinkedIn OAuth (WooCommerce)</h1>
            <form method="post" action="">
                <?php wp_nonce_field( 'gv_li_settings' ); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="gv_li_client_id">Client ID</label></th>
                        <td><input name="gv_li_client_id" id="gv_li_client_id" type="text" class="regular-text" value="<?php echo esc_attr( $client_id ); ?>" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="gv_li_client_secret">Primary Client Secret</label></th>
                        <td><input name="gv_li_client_secret" id="gv_li_client_secret" type="password" class="regular-text" value="<?php echo esc_attr( $client_secret ); ?>" autocomplete="new-password" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="gv_li_scopes">Scopes</label></th>
                        <td>
                            <input name="gv_li_scopes" id="gv_li_scopes" type="text" class="regular-text" value="<?php echo esc_attr( $scopes ); ?>">
                            <p class="description">Space-separated LinkedIn scopes (e.g. <code>r_liteprofile r_emailaddress</code>). Add others (like <code>w_member_social</code>) if your app requires it.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Redirect URI</th>
                        <td>
                            <code><?php echo esc_html( $redirect_uri ); ?></code>
                            <p class="description">Copy this Redirect URI into your LinkedIn App configuration.</p>
                        </td>
                    </tr>
                    <?php if ( $access_token ) : ?>
                        <tr>
                            <th scope="row">Access Token</th>
                            <td>
                                <code>stored (hidden)</code>
                                <?php if ( $expires_human ) : ?>
                                    <p class="description">Expires: <?php echo esc_html( $expires_human ); ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>

                <p class="submit">
                    <button type="submit" name="gv_li_save_settings" class="button button-primary">Save settings</button>
                    <?php if ( $client_id && $client_secret ) : ?>
                        <a href="<?php echo esc_url( $auth_url ); ?>" class="button">Connect to LinkedIn</a>
                    <?php else: ?>
                        <span class="description" style="margin-left:10px;">Enter Client ID & Secret, save, then click “Connect to LinkedIn”.</span>
                    <?php endif; ?>
                </p>
            </form>
        </div>
        <?php
    }

    /** Register REST route for LinkedIn callback */
    public function register_rest_routes() {
        register_rest_route( 'gv/v1', '/linkedin/callback', [
            'methods'  => 'GET',
            'callback' => [ $this, 'handle_callback' ],
            'permission_callback' => '__return_true',
            'args' => [
                'code'  => [ 'required' => false ],
                'state' => [ 'required' => false ],
                'error' => [ 'required' => false ],
            ],
        ] );
    }

    /** Handle OAuth callback: verify state, exchange code for token, store results, and redirect back to admin */
    public function handle_callback( WP_REST_Request $request ) {
        $error = sanitize_text_field( (string) $request->get_param('error') );
        if ( $error ) {
            $msg = sanitize_text_field( (string) $request->get_param('error_description') );
            $this->redirect_back( 'error', $msg ? $msg : $error );
        }

        $code  = sanitize_text_field( (string) $request->get_param('code') );
        $state = sanitize_text_field( (string) $request->get_param('state') );

        if ( ! $code || ! $state ) {
            $this->redirect_back( 'error', 'Missing code or state.' );
        }

        $tkey = self::TRANSIENT_PREFIX . $state;
        $state_data = get_transient( $tkey );
        if ( ! $state_data ) {
            $this->redirect_back( 'error', 'Invalid or expired state.' );
        }
        delete_transient( $tkey );

        $client_id     = get_option( self::OPT_CLIENT_ID, '' );
        $client_secret = get_option( self::OPT_CLIENT_SECRET, '' );
        $redirect_uri  = self::get_redirect_uri();

        if ( ! $client_id || ! $client_secret ) {
            $this->redirect_back( 'error', 'Client ID/Secret not configured.' );
        }

        // Exchange authorization code for access token
        $resp = wp_remote_post( self::TOKEN_URL, [
            'timeout' => 20,
            'headers' => [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
            'body'    => [
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => $redirect_uri,
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
            ],
        ] );

        if ( is_wp_error( $resp ) ) {
            $this->redirect_back( 'error', 'HTTP error: ' . $resp->get_error_message() );
        }

        $code_http = wp_remote_retrieve_response_code( $resp );
        $body      = wp_remote_retrieve_body( $resp );
        $data      = json_decode( $body, true );

        if ( $code_http !== 200 || empty( $data['access_token'] ) ) {
            $err_text = ! empty( $data['error_description'] ) ? $data['error_description'] : 'Token exchange failed.';
            $this->redirect_back( 'error', $err_text );
        }

        $access_token = sanitize_text_field( $data['access_token'] );
        $expires_in   = isset( $data['expires_in'] ) ? intval( $data['expires_in'] ) : 0;
        $expires_at   = $expires_in ? time() + $expires_in : 0;

        update_option( self::OPT_ACCESS_TOKEN, $access_token );
        update_option( self::OPT_EXPIRES_AT, $expires_at );

        $this->redirect_back( 'connected' );
    }

    /** Redirect back to the settings page with a status */
    protected function redirect_back( $status = 'connected', $msg = '' ) {
        $url = add_query_arg( [
            'page'   => self::MENU_SLUG,
            'status' => $status,
        ], admin_url( 'admin.php' ) );

        if ( $msg ) {
            $url = add_query_arg( [ 'msg' => rawurlencode( $msg ) ], $url );
        }

        wp_safe_redirect( $url );
        exit;
    }
}

new GV_WC_LinkedIn_OAuth();
