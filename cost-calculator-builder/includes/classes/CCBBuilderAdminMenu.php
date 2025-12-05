<?php

namespace cBuilder\Classes;

class CCBBuilderAdminMenu {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 20 );
		add_action( 'admin_head', array( $this, 'add_menu_badge_styles' ) );
	}

	public static function init() {
		return new CCBBuilderAdminMenu();
	}

	public function settings_menu() {
		$root_page = array( $this, 'render_page' );

		if ( $this::show_welcome_ccb_page() && ! defined( 'CCB_PRO_VERSION' ) ) {
			$root_page = array( $this, 'ccb_welcome_render' );
		}

		add_menu_page(
			'Preview',
			'Preview',
			'manage_options',
			'cost_calculator_preview',
			array( $this, 'render_preview' ),
			'',
			100
		);

		remove_menu_page( 'cost_calculator_preview' );

		add_menu_page(
			esc_html__( 'Cost Calculator', 'cost-calculator-builder' ),
			esc_html__( 'Cost Calculator', 'cost-calculator-builder' ),
			'manage_options',
			'cost_calculator_builder',
			$root_page,
			CALC_URL . '/frontend/dist/img/icon.png',
			110
		);

		add_submenu_page(
			'cost_calculator_builder',
			esc_html__( 'Calculators', 'cost-calculator-builder' ),
			esc_html__( 'Calculators', 'cost-calculator-builder' ),
			'manage_options',
			'cost_calculator_builder',
			$root_page
		);
		add_submenu_page(
			'cost_calculator_builder',
			esc_html__( 'Templates', 'cost-calculator-builder' ),
			esc_html__( 'Templates', 'cost-calculator-builder' ),
			'manage_options',
			'cost_calculator_templates',
			array( $this, 'render_templates' )
		);

		if ( defined( 'CALC_DEV_MODE' ) ) {
			add_submenu_page(
				'cost_calculator_builder',
				esc_html__( 'Categories', 'cost-calculator-builder' ),
				esc_html__( 'Categories', 'cost-calculator-builder' ),
				'manage_options',
				'cost_calculator_categories',
				array( $this, 'render_categories' )
			);
		}

		add_submenu_page(
			'cost_calculator_builder',
			esc_html__( 'Global Settings', 'cost-calculator-builder' ),
			esc_html__( 'Global Settings', 'cost-calculator-builder' ),
			'manage_options',
			'cost_calculator_builder&tab=settings',
			array( $this, 'render_page' )
		);

		if ( defined( 'CCB_PRO_VERSION' ) ) {
			add_submenu_page(
				'cost_calculator_builder',
				esc_html__( 'Orders', 'cost-calculator-builder' ),
				esc_html__( 'Orders', 'cost-calculator-builder' ),
				'manage_options',
				'cost_calculator_orders',
				array( $this, 'calc_orders_page' )
			);

			$show_analytics = apply_filters( 'ccb_maybe_show_analytics_menu', true );
			if ( $show_analytics ) {
				add_submenu_page(
					'cost_calculator_builder',
					esc_html__( 'Analytics', 'cost-calculator-builder' ),
					esc_html__( 'Analytics', 'cost-calculator-builder' ) . ' <span class="ccb-menu-badge" style="display: none;">NEW</span>',
					'manage_options',
					'cost_calculator_analytics',
					array( $this, 'render_analytics' )
				);
			}

			add_submenu_page(
				'cost_calculator_builder',
				esc_html__( 'Help Center', 'cost-calculator-builder' ),
				'<span style="color: #FC7B40; font-weight: 700;">' . esc_html__( 'Help Center', 'cost-calculator-builder' ) . '</span>',
				'manage_options',
				'stm-support-page-ccb',
				function () {
					\STM_Support_Page::render_support_page( 'cost-calculator-builder' );
				}
			);
		} else {
			add_submenu_page(
				'cost_calculator_builder',
				esc_html__( 'Orders', 'cost-calculator-builder' ),
				esc_html__( 'Orders', 'cost-calculator-builder' ),
				'manage_options',
				'cost_calculator_orders',
				array( $this, 'calc_orders_page_demo' )
			);

			$show_analytics = apply_filters( 'ccb_maybe_show_analytics_menu', true );

			if ( $show_analytics ) {
				add_submenu_page(
					'cost_calculator_builder',
					esc_html__( 'Analytics', 'cost-calculator-builder' ),
					esc_html__( 'Analytics', 'cost-calculator-builder' ),
					'manage_options',
					'cost_calculator_analytics',
					array( $this, 'cost_calculator_analytics_demo' )
				);
			}

			add_submenu_page(
				'cost_calculator_builder',
				esc_html__( 'Upgrade', 'cost-calculator-builder' ),
				'<span style="color: #adff2f;"><span style="font-size: 16px;text-align: left;" class="dashicons dashicons-star-filled stm_go_pro_menu"></span>' . esc_html__( 'Upgrade', 'cost-calculator-builder' ) . '</span>',
				'manage_options',
				'cost_calculator_gopro',
				array( $this, 'calc_gopro_page' )
			);

			add_submenu_page(
				'cost_calculator_builder',
				esc_html__( 'Pro Features', 'cost-calculator-builder' ),
				esc_html__( 'Pro Features', 'cost-calculator-builder' ),
				'manage_options',
				'cost_calculator_pro_features',
				array( $this, 'calc_pro_features' )
			);
			add_submenu_page(
				'cost_calculator_builder',
				esc_html__( 'Help Center', 'cost-calculator-builder' ),
				'<span style="color: #FC7B40; font-weight: 700;">' . esc_html__( 'Help Center', 'cost-calculator-builder' ) . '</span>',
				'manage_options',
				'stm-support-page-ccb',
				function () {
					\STM_Support_Page::render_support_page( 'cost-calculator-builder' );
				}
			);
		}
	}

	public function ccb_welcome_render() {
		$this::mark_as_visited_ccb_welcome();
		echo CCBTemplate::load( 'admin/pages/welcome' ); //phpcs:ignore
		exit;
	}

	public static function show_welcome_ccb_page(): bool {
		return get_option( 'ccb__show_welcome_page' );
	}

	public static function mark_as_visited_ccb_welcome() {
		delete_option( 'ccb__show_welcome_page' );
	}

	public function render_templates() {
		echo CCBTemplate::load( 'admin/pages/templates' ); //phpcs:ignore
	}

	public function render_preview() {
		echo CCBTemplate::load( 'admin/pages/preview' ); //phpcs:ignore
	}

	public function render_analytics() {
		echo CCBTemplate::load( 'admin/pages/analytics' ); //phpcs:ignore
	}

	public function render_categories() {
		echo CCBTemplate::load( 'admin/pages/categories' ); //phpcs:ignore
	}

	public function render_page() {
		echo CCBTemplate::load( 'admin/index' ); //phpcs:ignore
	}

	public function calc_orders_page() {
		echo CCBTemplate::load( 'admin/pages/orders' ); //phpcs:ignore
	}

	public function calc_orders_page_demo() {
		echo CCBTemplate::load( 'admin/pages/orders-demo' ); //phpcs:ignore
	}

	public function cost_calculator_analytics_demo() {
		echo CCBTemplate::load( 'admin/pages/analytics-demo' ); //phpcs:ignore
	}

	public function calc_gopro_page() {
		echo CCBTemplate::load( 'admin/pages/go-pro' ); //phpcs:ignore
	}

	public function calc_pro_features() {
		echo CCBTemplate::load( 'admin/pages/pro-features' ); //phpcs:ignore
	}

	public function add_menu_badge_styles() {
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'cost_calculator' ) === false ) {
			return;
		}
		?>
		<style type="text/css">
			#adminmenu a[href*="cost_calculator_analytics"] {
				display: flex !important;
				align-items: center;
				justify-content: space-between;
			}
			
			.ccb-menu-badge {
				background: rgba(255,168,0,.1);
				color: #fff;
				font-size: 9px;
				font-weight: 600;
				line-height: 1;
				padding: 0 5px;
				border-radius: 9px;
				margin-left: 7px;
				vertical-align: top;
				display: inline-block;
				animation: ccb-badge-pulse 2s infinite;
				height: 20px;
				display: flex !important;
				align-items: center;
				justify-content: center;
			}
			
			@keyframes ccb-badge-pulse {
				0% { transform: scale(1); }
				50% { transform: scale(1.1); }
				100% { transform: scale(1); }
			}
			
			/* Dark theme support */
			body.folded .ccb-menu-badge,
			.wp-core-ui.wp-admin #adminmenu .ccb-menu-badge {
				background: rgba(255,168,0,.1);
				color: #FFA800;
				height: 20px;
			}
		</style>
		<?php
	}
}
