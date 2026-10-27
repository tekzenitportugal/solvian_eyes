<?php
/**
 * Front-end assets: custom CSS/JS and dequeue unused parent (Blocksy) bundles.
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current request uses the custom Eyes layout (not Blocksy builder UI).
 */
function eyes_uses_custom_layout() {
	if ( is_admin() || is_customize_preview() ) {
		return false;
	}

	return true;
}

/**
 * Enqueue child theme assets.
 */
function eyes_enqueue_assets() {
	$main_css = get_stylesheet_directory() . '/assets/css/main.css';
	$main_js  = get_stylesheet_directory() . '/assets/js/main.js';

	wp_enqueue_style(
		'eyes-google-fonts',
		'https://fonts.googleapis.com/css2?family=Albert+Sans:wght@300;400;500&display=swap',
		array(),
		null
	);

	if ( file_exists( $main_css ) ) {
		wp_enqueue_style(
			'eyes-main',
        eyes_asset_uri( 'css/main.css' ),
			array( 'eyes-google-fonts' ),
			(string) filemtime( $main_css )
		);

		wp_add_inline_style(
			'eyes-main',
			<<<'CSS'
.feature-title__u {
	text-decoration: underline;
	text-decoration-color: #fff;
	text-decoration-thickness: 3px;
	text-underline-offset: 4px;
}
.hero__title-accent {
	text-decoration: underline;
	text-decoration-color: #78eeee;
	text-decoration-thickness: 4px;
	text-underline-offset: 6px;
}
.hero__title-line {
	display: inline;
}
CSS
		);
	}

	if ( file_exists( $main_js ) ) {
		wp_enqueue_script(
			'eyes-main',
			eyes_asset_uri( 'js/main.js' ),
			array(),
			(string) filemtime( $main_js ),
			true
		);
	}

	$concursos_filters_js = get_stylesheet_directory() . '/assets/js/concursos-filters.js';
	if (
		file_exists( $concursos_filters_js )
		&& function_exists( 'eyes_is_concurso_listing_page' )
		&& eyes_is_concurso_listing_page()
	) {
		wp_enqueue_script(
			'eyes-concursos-filters',
			eyes_asset_uri( 'js/concursos-filters.js' ),
			array(),
			(string) filemtime( $concursos_filters_js ),
			true
		);
	}

	$planos_js = get_stylesheet_directory() . '/assets/js/planos.js';
	if (
		file_exists( $planos_js )
		&& function_exists( 'eyes_is_planos_page' )
		&& eyes_is_planos_page()
	) {
		wp_enqueue_script(
			'eyes-planos',
			eyes_asset_uri( 'js/planos.js' ),
			array(),
			(string) filemtime( $planos_js ),
			true
		);

		$billing = 'monthly';
		if ( is_user_logged_in() ) {
			$user_billing = sanitize_key( (string) get_user_meta( get_current_user_id(), 'eyes_plan_billing', true ) );
			if ( in_array( $user_billing, array( 'monthly', 'annual' ), true ) ) {
				$billing = $user_billing;
			}
		}

		if ( isset( $_GET['billing'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$query_billing = sanitize_key( wp_unslash( $_GET['billing'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( in_array( $query_billing, array( 'monthly', 'annual' ), true ) ) {
				$billing = $query_billing;
			}
		}

		$initial_view = 'plans';
		if ( isset( $_GET['step'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$step = sanitize_key( wp_unslash( $_GET['step'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( in_array( $step, array( 'checkout', 'success' ), true ) ) {
				$initial_view = $step;
			}
		}

		$summary = array();
		if ( is_user_logged_in() && function_exists( 'eyes_get_plan_success_summary' ) && function_exists( 'eyes_get_user_current_plan' ) && 'advanced' === eyes_get_user_current_plan() ) {
			$summary = eyes_get_plan_success_summary();
		}

		wp_localize_script(
			'eyes-planos',
			'eyesPlanos',
			array(
				'ajaxurl'           => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( 'eyes_planos' ),
				'isLoggedIn'        => is_user_logged_in(),
				'loginUrl'          => eyes_login_url(),
				'planosUrl'         => eyes_planos_url(),
				'registoUrl'        => eyes_registo_url(),
				'initialView'       => $initial_view,
				'summary'           => $summary,
				'errorMessage'      => __( 'Não foi possível guardar a seleção. Tente novamente.', 'eyes-child' ),
			)
		);
	}

	$dashboard_tabs_js = get_stylesheet_directory() . '/assets/js/dashboard-tabs.js';
	if (
		file_exists( $dashboard_tabs_js )
		&& is_user_logged_in()
		&& function_exists( 'eyes_is_concurso_listing_page' )
		&& eyes_is_concurso_listing_page()
	) {
		wp_enqueue_script(
			'eyes-dashboard-tabs',
			eyes_asset_uri( 'js/dashboard-tabs.js' ),
			array( 'eyes-concursos-filters' ),
			(string) filemtime( $dashboard_tabs_js ),
			true
		);
		// Localize script to pass data to our JS file, e.g., for AJAX URL
		wp_localize_script(
			'eyes-dashboard-tabs',
			'eyesDashboardAjax',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'dashboard_tab_nonce' ),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'eyes_enqueue_assets', 20 );

/**
 * Dequeue Blocksy parent styles/scripts when the custom layout is active.
 *
 * Only dequeue — never deregister. Other Blocksy bundles (e.g. ct-cf-7-styles for
 * Contact Form 7) register with a dependency on ct-main-styles; deregistering the
 * parent handle triggers WP 6.9.1+ "_doing_it_wrong" notices.
 */
function eyes_dequeue_parent_assets() {
	if ( ! eyes_uses_custom_layout() ) {
		return;
	}

	global $wp_styles, $wp_scripts;

	wp_dequeue_style( 'ct-main-styles' );
	wp_dequeue_style( 'ct-main-rtl-styles' );
	wp_dequeue_style( 'blocksy-fonts-font-source-google' );

	if ( $wp_styles instanceof WP_Styles ) {
		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( ! is_string( $handle ) || 0 !== strpos( $handle, 'ct-' ) ) {
				continue;
			}

			wp_dequeue_style( $handle );
		}
	}

	wp_dequeue_script( 'ct-scripts' );
	wp_dequeue_script( 'ct-events' );

	if ( $wp_scripts instanceof WP_Scripts ) {
		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( ! is_string( $handle ) || 0 !== strpos( $handle, 'ct-' ) ) {
				continue;
			}

			wp_dequeue_script( $handle );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'eyes_dequeue_parent_assets', 100 );
