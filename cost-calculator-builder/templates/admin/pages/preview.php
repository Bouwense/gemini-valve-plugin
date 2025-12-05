<?php

use cBuilder\Classes\Appearance\CCBCssLoader;

$id = isset( $_GET['preview_calc_id'] ) ? intval( $_GET['preview_calc_id'] ) : null; // phpcs:ignore

wp_enqueue_style( 'ccb-admin-preview-css', CALC_URL . '/frontend/dist/css/preview.css', array(), CALC_VERSION );
wp_enqueue_script( 'ccb-preview-js', CALC_URL . '/frontend/dist/preview.js', array(), CALC_VERSION, true );
wp_localize_script(
	'ccb-preview-js',
	'ajax_window',
	array(
		'ajax_url'     => admin_url( 'admin-ajax.php' ),
		'plugin_url'   => CALC_URL,
		'site_url'     => site_url(),
		'translations' => array_merge( \cBuilder\Classes\CCBTranslations::get_frontend_translations(), \cBuilder\Classes\CCBTranslations::get_backend_translations() ),
		'pro_active'   => ccb_pro_active(),
	)
);

$appearance_css = CCBCssLoader::ccb_load_appearance( $id );
if ( ! empty( $appearance_css ) ) {
	wp_enqueue_style( ccb_generate_random_handle(), $appearance_css, array(), CALC_VERSION );
}
?>

<div id="ccb-preview-overlay">
	<div class="ccb-preview-container">
		<div class="ccb-preview-container__sticky-header" id="ccb-preview">
			<div class="ccb-preview-container__sticky-header--menu">
				<a href="<?php echo esc_attr( admin_url( 'admin.php?page=cost_calculator_builder&action=edit&id=' . $id ) ); ?>" class="ccb-preview-container__sticky-header--menu-item ccb-back">
					<i class="ccb-icon-Path-3398"></i>
					<span><?php echo esc_html_e( 'Back', 'cost-calculator-builder' ); ?></span>
				</a>
				<div class="ccb-preview-container__sticky-header--menu-item">
					<i class="ccb-icon-new-calculator"></i>
					<a href="<?php echo esc_attr( admin_url( 'admin.php?page=cost_calculator_builder' ) ); ?>"><?php echo esc_html_e( 'Calculators', 'cost-calculator-builder' ); ?></a>
				</div>
			</div>
			<div>
				<div class="ccb-preview-switcher">
					<div class="ccb-preview-switcher__item" :class="{ 'ccb-active': active_device === 'desktop' }" @click="() => updateDevice('desktop')">
						<i class="ccb-icon-Path-3501"></i>
					</div>
					<div class="ccb-preview-switcher__item" :class="{ 'ccb-active': active_device === 'mobile' }" @click="() => updateDevice('mobile')">
						<i class="ccb-icon-Path-3502"></i>
					</div>
				</div>
			</div>
			<div class="ccb-preview-toggle-sidebar">
				<label class="ccb-checkboxes" style="display: none;">
					<input type="checkbox">
					<span class="ccb-checkboxes-label" style="cursor: pointer;"><?php esc_html_e( 'Preview Mode', 'cost-calculator-builder' ); ?></span>
				</label>
				<div class="ccb-preview-embed">
					<button class="ccb-button success" @click="() => openEmbedPopup('<?php echo esc_attr( $id ); ?>')">
						<i class="ccb-icon-html"></i>
						<span><?php esc_html_e( 'Embed', 'cost-calculator-builder' ); ?></span>
					</button>
				</div>
			</div>

			<ccb-embed
				embed-text="<?php esc_attr_e( json_encode( ccb_embed_popup_text() ) ); //phpcs:ignore ?>"
				ref="embedCalc"
			>
			</ccb-embed>
		</div>

		<div class="ccb-preview-container__body">			
			<div class="ccb-preview-container__body-content">
				<div class="ccb-preview-container__calc-block ccb-desktop-preview">
					<?php echo do_shortcode( '[stm-calc id="' . $id . '"]' ); ?>
				</div>

				<div class="ccb-preview-container__calc-block ccb-mobile-preview">
					<div class="calc-preview-mobile ccb-custom-scrollbar" id="calc-preview-mobile" v-if="preview === 'mobile'">
						<div class="calc-preview-mobile__header">
							<div class="calc-preview-mobile__header-attrs">
								<div class="calc-attr-left">
									<span class="calc-attr-time">9:41</span>
								</div>
								<div class="calc-attr-center">
									<span class="calc-attr-camera">
										<img src="<?php echo esc_attr( CALC_URL . '/frontend/dist/img/preview/camera.png' ); ?>" alt="camera">
									</span>
								</div>
								<div class="calc-attr-right">
									<img src="<?php echo esc_attr( CALC_URL . '/frontend/dist/img/preview/battery.png' ); ?>" alt="battery">
									<img src="<?php echo esc_attr( CALC_URL . '/frontend/dist/img/preview/wifi.png' ); ?>" alt="wifi">
									<img src="<?php echo esc_attr( CALC_URL . '/frontend/dist/img/preview/connection.png' ); ?>" alt="connection">
								</div>
							</div>
							<div class="calc-preview-mobile__header-search">
								<span class="calc-attr-search-bar">
									<span>
										yourwebsite.com
									</span>
								</span>
							</div>
						</div>
						<div class="calc-preview-mobile__content">
							<?php echo do_shortcode( '[stm-calc id="' . $id . '"]' ); ?>
						</div>
					</div>
				</div>
			</div>

			<div class="ccb-preview-container__sidebar">
				<div class="ccb-preview-container__sidebar-header">
					<div class="ccb-preview-container__sidebar-header-title">
						<span><?php esc_html_e( 'Get Pro Version', 'cost-calculator-builder' ); ?></span>
					</div>
					<div class="ccb-preview-container__sidebar-header-action">
						<a href="https://stylemixthemes.com/cost-calculator-plugin/pricing/" class="ccb-button success" target="_blank">
							<?php esc_html_e( 'Upgrade Now', 'cost-calculator-builder' ); ?>
						</a>
					</div>
				</div>

				<div class="ccb-preview-container__sidebar-content">
					<div class="ccb-preview-container__sidebar-content-item">
						<i class="ccb-icon-Checkmark-Circle-2"></i>
						<span><?php esc_html_e( 'Connect 5000+ apps with Cost Calculator', 'cost-calculator-builder' ); ?></span>
					</div>
					<div class="ccb-preview-container__sidebar-content-item">
						<i class="ccb-icon-Checkmark-Circle-2"></i>
						<span><?php esc_html_e( 'Connect 5000+ apps with Cost Calculator', 'cost-calculator-builder' ); ?></span>
					</div>
					<div class="ccb-preview-container__sidebar-content-item">
						<i class="ccb-icon-Checkmark-Circle-2"></i>
						<span><?php esc_html_e( 'Connect 5000+ apps with Cost Calculator', 'cost-calculator-builder' ); ?></span>
					</div>
					<div class="ccb-preview-container__sidebar-content-item">
						<i class="ccb-icon-Checkmark-Circle-2"></i>
						<span><?php esc_html_e( 'Connect 5000+ apps with Cost Calculator', 'cost-calculator-builder' ); ?></span>
					</div>
					<div class="ccb-preview-container__sidebar-content-item">
						<i class="ccb-icon-Checkmark-Circle-2"></i>
						<span><?php esc_html_e( 'Connect 5000+ apps with Cost Calculator', 'cost-calculator-builder' ); ?></span>
					</div>
					<div class="ccb-preview-container__sidebar-content-item">
						<i class="ccb-icon-Checkmark-Circle-2"></i>
						<span><?php esc_html_e( 'Connect 5000+ apps with Cost Calculator', 'cost-calculator-builder' ); ?></span>
					</div>
					<div class="ccb-preview-container__sidebar-content-item">
						<i class="ccb-icon-Checkmark-Circle-2"></i>
						<span><?php esc_html_e( 'Connect 5000+ apps with Cost Calculator', 'cost-calculator-builder' ); ?></span>
					</div>
					<div class="ccb-preview-container__sidebar-content-item">
						<i class="ccb-icon-Checkmark-Circle-2"></i>
						<span><?php esc_html_e( 'Connect 5000+ apps with Cost Calculator', 'cost-calculator-builder' ); ?></span>
					</div>
					<div class="ccb-preview-container__sidebar-content-item">
						<i class="ccb-icon-Checkmark-Circle-2"></i>
						<span><?php esc_html_e( 'Connect 5000+ apps with Cost Calculator', 'cost-calculator-builder' ); ?></span>
					</div>
					<div class="ccb-preview-container__sidebar-content-item">
						<i class="ccb-icon-Checkmark-Circle-2"></i>
						<span><?php esc_html_e( 'Connect 5000+ apps with Cost Calculator', 'cost-calculator-builder' ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
