<?php
$should_hide_sticky = false;
if ( isset( $sticky_calc['pages'] ) && is_array( $sticky_calc['pages'] ) ) {
	$current_page_id = get_the_ID();
	$blog_page_id    = get_option( 'page_for_posts' );

	foreach ( $sticky_calc['pages'] as $page_data ) {
		if ( is_array( $page_data ) ) {
			if ( isset( $page_data['id'] ) && ( intval( $page_data['id'] ) === $current_page_id || is_home() && intval( $page_data['id'] ) === intval( $blog_page_id ) ) ) {
				$should_hide_sticky = true;
				break;
			}
		}
	}
}


$additional_classes = isset( $sticky_calc['classes'] ) ? strval( $sticky_calc['classes'] ) : '';
$positions          = isset( $sticky_calc['btn_position'] ) ? strval( $sticky_calc['btn_position'] ) : '';

if ( 0 === $position ) {
	$additional_classes = $additional_classes . ' allow-on-mobile';
}

if ( 'banner' === $sticky_calc['display_type'] ) {
	$additional_classes = $additional_classes . ' is-banner';
	$positions          = $sticky_calc['banner_position'] ?? '';
}
if ( ! $should_hide_sticky ) {
	?>
	<div class="ccb-sticky-calc ccb-calc-id-<?php echo esc_attr( $calc_id . ' ' . $additional_classes ); ?>" data-calc-id="<?php echo esc_attr( $calc_id ); ?>" data-position="<?php echo ! empty( $positions ) ? esc_attr( $positions ) : 'hidden'; ?>"></div>
	<?php
}
