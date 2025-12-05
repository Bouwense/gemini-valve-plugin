<?php
/**
 * Plugin Name: WooCommerce Customer Admin Approval
 * Description: Requires admin approval for newly registered WooCommerce customers before they can log in.
 * Version: 1.0.0
 * Author: your-friendly-helper
 * Text Domain: gv-admin-approval
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'GV_AA_META', '_gv_account_approved' );

/**
 * Mark new WooCommerce customers as "pending" and email admin.
 */
add_action( 'woocommerce_created_customer', function( $customer_id, $new_customer_data, $password_generated ) {
    // Only set if not already set.
    if ( '' === get_user_meta( $customer_id, GV_AA_META, true ) ) {
        update_user_meta( $customer_id, GV_AA_META, '0' );
    }

    // Email admin with one-click approve link.
    $admin_email = get_option( 'admin_email' );
    $approve_url = wp_nonce_url(
        add_query_arg(
            ['action' => 'gv_approve_user', 'user_id' => $customer_id],
            admin_url( 'users.php' )
        ),
        'gv_approve_user_' . $customer_id
    );

    $user    = get_userdata( $customer_id );
    $subject = sprintf( '[%s] New customer pending approval', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
    $message = sprintf(
        "A new customer registered and is pending approval:\n\nUsername: %s\nEmail: %s\n\nApprove now: %s\n\n(Users → All Users → Approval column)",
        $user->user_login,
        $user->user_email,
        $approve_url
    );

    if ( function_exists( 'wc_mail' ) ) {
        wc_mail( $admin_email, $subject, nl2br( esc_html( $message ) ) );
    } else {
        wp_mail( $admin_email, $subject, $message );
    }
}, 10, 3 );

/**
 * Block login for unapproved accounts.
 * Existing users without the meta are allowed; only explicit '0' is blocked.
 * Admins/Shop Managers always allowed.
 */
add_filter( 'wp_authenticate_user', function( $user ) {
    if ( is_wp_error( $user ) ) return $user;

    if ( user_can( $user, 'manage_options' ) || user_can( $user, 'manage_woocommerce' ) ) {
        return $user;
    }

    $approved = get_user_meta( $user->ID, GV_AA_META, true );

    if ( '0' === $approved ) {
        return new WP_Error(
            'account_pending_approval',
            __( 'Your account is pending approval by an administrator. You will be notified once it is approved.', 'gv-admin-approval' )
        );
    }

    return $user;
}, 10 );

/**
 * Handle one-click approval in wp-admin.
 */
add_action( 'admin_init', function() {
    if ( ! is_admin() || ! current_user_can( 'edit_users' ) ) return;

    if ( isset( $_GET['action'], $_GET['user_id'] ) && 'gv_approve_user' === $_GET['action'] ) {
        $user_id = absint( $_GET['user_id'] );
        check_admin_referer( 'gv_approve_user_' . $user_id );

        update_user_meta( $user_id, GV_AA_META, '1' );

        // Notify the user.
        $user      = get_userdata( $user_id );
        $subject   = sprintf( __( '[%s] Your account has been approved', 'gv-admin-approval' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
        $login_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
        $message   = sprintf(
            __( "Hi %s,\n\nYour account at %s has been approved. You can now log in:\n%s\n\nThank you!", 'gv-admin-approval' ),
            $user->display_name ?: $user->user_login,
            home_url(),
            $login_url
        );

        if ( function_exists( 'wc_mail' ) ) {
            wc_mail( $user->user_email, $subject, nl2br( esc_html( $message ) ) );
        } else {
            wp_mail( $user->user_email, $subject, $message );
        }

        wp_safe_redirect( add_query_arg( [ 'gv_approved' => 1 ], admin_url( 'users.php' ) ) );
        exit;
    }
} );

/**
 * Users list: Approval column + row action.
 */
add_filter( 'manage_users_columns', function( $columns ) {
    $columns['gv_approval'] = __( 'Approval', 'gv-admin-approval' );
    return $columns;
} );

add_filter( 'manage_users_custom_column', function( $output, $column_name, $user_id ) {
    if ( 'gv_approval' !== $column_name ) return $output;

    $approved = get_user_meta( $user_id, GV_AA_META, true );
    if ( '1' === $approved ) {
        return '<span style="color:#15803d;">' . esc_html__( 'Approved', 'gv-admin-approval' ) . '</span>';
    }
    if ( '0' === $approved ) {
        $approve_url = wp_nonce_url(
            add_query_arg( ['action' => 'gv_approve_user', 'user_id' => $user_id], admin_url( 'users.php' ) ),
            'gv_approve_user_' . $user_id
        );
        return '<span style="color:#b91c1c;">' . esc_html__( 'Pending', 'gv-admin-approval' ) . '</span> — <a href="' . esc_url( $approve_url ) . '">' . esc_html__( 'Approve', 'gv-admin-approval' ) . '</a>';
    }
    return esc_html__( '—', 'gv-admin-approval' );
}, 10, 3 );

add_filter( 'user_row_actions', function( $actions, $user ) {
    $approved = get_user_meta( $user->ID, GV_AA_META, true );
    if ( current_user_can( 'edit_users' ) && '0' === $approved ) {
        $approve_url = wp_nonce_url(
            add_query_arg( ['action' => 'gv_approve_user', 'user_id' => $user->ID], admin_url( 'users.php' ) ),
            'gv_approve_user_' . $user->ID
        );
        $actions['gv_approve'] = '<a href="' . esc_url( $approve_url ) . '">' . esc_html__( 'Approve account', 'gv-admin-approval' ) . '</a>';
    }
    return $actions;
}, 10, 2 );

/**
 * User profile: manual approval checkbox.
 */
add_action( 'show_user_profile', 'gv_account_approval_field' );
add_action( 'edit_user_profile', 'gv_account_approval_field' );
function gv_account_approval_field( $user ) {
    if ( ! current_user_can( 'edit_users' ) ) return;
    $approved = get_user_meta( $user->ID, GV_AA_META, true );
    ?>
    <h2><?php esc_html_e( 'WooCommerce Account Approval', 'gv-admin-approval' ); ?></h2>
    <table class="form-table" role="presentation">
        <tr>
            <th><label for="gv_account_approved"><?php esc_html_e( 'Approved', 'gv-admin-approval' ); ?></label></th>
            <td>
                <label>
                    <input type="checkbox" name="gv_account_approved" id="gv_account_approved" value="1" <?php checked( '1', $approved ); ?> />
                    <?php esc_html_e( 'Allow this user to log in.', 'gv-admin-approval' ); ?>
                </label>
                <?php if ( '0' === $approved ) : ?>
                    <p class="description"><?php esc_html_e( 'Currently pending approval. Checking this will approve the account.', 'gv-admin-approval' ); ?></p>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'personal_options_update', 'gv_save_account_approval_field' );
add_action( 'edit_user_profile_update', 'gv_save_account_approval_field' );
function gv_save_account_approval_field( $user_id ) {
    if ( ! current_user_can( 'edit_users' ) ) return;
    $approved = isset( $_POST['gv_account_approved'] ) ? '1' : '0';
    update_user_meta( $user_id, GV_AA_META, $approved );
}

/**
 * Admin notice after approving.
 */
add_action( 'admin_notices', function() {
    if ( isset( $_GET['gv_approved'] ) ) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'User account approved.', 'gv-admin-approval' ) . '</p></div>';
    }
} );
