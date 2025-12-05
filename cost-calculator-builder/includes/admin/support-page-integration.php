<?php
if ( isset( $_GET['page'] ) && ( 'stm-support-page-ccb' === $_GET['page'] ) ) {
	require_once CALC_DIR . '/includes/lib/support-page/support-page.php';

	add_action(
		'admin_enqueue_scripts',
		function() {
			wp_enqueue_style( 'ccb-admin-app-css', CALC_URL . '/frontend/dist/css/admin.css', array(), CALC_VERSION );
			wp_enqueue_script( 'cbb-feedback', CALC_URL . '/frontend/dist/feedback.js', array(), CALC_VERSION, true );
			wp_localize_script(
				'cbb-feedback',
				'ajax_window',
				array(
					'ajax_url'   => admin_url( 'admin-ajax.php' ),
					'plugin_url' => CALC_URL,
				)
			);
		}
	);

	add_action(
		'admin_init',
		function() {
			/**
			 * Add Support Page
			 */
			STM_Support_Page::set_api_urls(
				'cost-calculator-builder',
				array(
					'promo'    => 'https://promo-dashboard.stylemixthemes.com/wp-content/dashboard-promo/cost-calculator-builder_posts.json',
					'freemius' => array(
						'plugin_slug' => 'cost-calculator-builder-pro',
						'item_id'     => 29,
					),
				)
			);
			STM_Support_Page::set_data(
				'cost-calculator-builder',
				array(
					'header'     => array(
						array(
							'title' => __( 'Cost Calculator Help Center', 'cost-calculator-builder' ),
						),
					),
					'help_items' => array(
						'documentation' => array(
							'buttons' => array(
								array(
									'href' => 'https://docs.stylemixthemes.com/cost-calculator-builder',
								),
							),
						),
						'ticket'        => array(
							'has-pro'      => '',
							'has-pro-plus' => defined( 'CCB_PRO' ) && defined( 'CCB_PRO_PATH' ) && defined( 'CCB_PRO_URL' ) ? true : false,
						),
						'video'         => array(
							'buttons' => array(
								array(
									'href' => 'https://www.youtube.com/playlist?list=PL3Pyh_1kFGGBevD2qrFjDGH2w9XNGHTz6',
								),
							),
						),
						'requests'      => array(
							'buttons' => array(
								array(
									'href' => 'https://stylemixthemes.cnflx.io/boards/cost-calculator-builder',
								),
							),
						),
						'community'     => array(
							'buttons' => array(
								array(
									'href' => 'https://www.facebook.com/groups/costcalculator',
								),
							),
						),
						'customization' => array(
							'buttons' => array(
								array(
									'href' => 'https://stylemix.net/ticket-form/?utm_source=wpadmin&utm_medium=help_center&utm_campaign=ccb_get_quotes',
								),
							),
						),
						'features'      => array(
							'title'        => __( 'Get Cost Calculator and Enjoy PRO Features', 'cost-calculator-builder' ),
							'description'  => __( 'Upgrade now and unlock multi-step forms, conditional logic, file uploads, and advanced styling options. Create smarter, more interactive calculators that fit your business.', 'cost-calculator-builder' ),
							'buttons'      => array(
								array(
									'href' => 'https://stylemixthemes.com/cost-calculator-plugin/pricing/?utm_source=wpadmin&utm_medium=help_center&utm_campaign=ccb_promo_banner',
								),
								array(
									'href' => 'https://stylemixthemes.com/cost-calculator-plugin/?utm_source=wpadmin&utm_medium=help_center&utm_campaign=ccb_promo_banner',
								),
							),
							'image'        => CALC_URL . '/includes/lib/support-page/assets/images/feature-bg-ccb.jpg',
							'has-pro-plus' => defined( 'CCB_PRO' ) && defined( 'CCB_PRO_PATH' ) && defined( 'CCB_PRO_URL' ) ? true : false,
						),
						'expert'        => array(
							'buttons' => array(
								array(
									'href' => 'https://stylemix.net/?utm_source=wpadmin&utm_medium=help_center&utm_campaign=ccb_hire_us',
								),
							),
						),
					),
					'review'     => array(
						'review_form' => array(
							'has_review' => get_option( 'ccb_feedback_added', false ),
							'buttons'    => array(
								array(
									'href' => 'https://bit.ly/33EkIN3',
								),
							),
						),
					),
					'news'       => array(
						'blog_list' => array(
							'category_id' => '393',
							'buttons'     => array(
								array(
									'href' => 'https://stylemixthemes.com/wp/category/cost-calculator-builder/',
								),
							),
						),
					),
				)
			);
		}
	);
}
