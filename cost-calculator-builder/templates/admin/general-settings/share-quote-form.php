<div class="ccb-tab-container">
	<?php if ( ! defined( 'CCB_PRO' ) ) : ?>
		<settings-pro-banner
			title="<?php esc_html_e( 'Share Quote Form', 'cost-calculator-builder' ); ?>"
			subtitle="<?php esc_html_e( 'Available in PRO version', 'cost-calculator-builder' ); ?>"
			text="<?php esc_html_e( 'Allow customers to send their ready quote via email to others.', 'cost-calculator-builder' ); ?>"
			link="https://stylemixthemes.com/cost-calculator-plugin/pricing/?utm_source=calcwpadmin&utm_medium=freetoprobutton&utm_campaign=share_quote_form"
			img="<?php echo esc_attr( CALC_URL . '/frontend/dist/img/pro-features/share-quote.webp' ); ?>"
			img-height="317px"
		/>
	<?php else : ?>
		<?php do_action( 'render-general-share-quote-form' ); //phpcs:ignore ?>
	<?php endif; ?>
</div>
