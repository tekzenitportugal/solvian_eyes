<?php
/**
 * Theme setup: menus and supports.
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register navigation menus used by custom header/footer templates.
 */
function eyes_register_menus() {
	register_nav_menus(
		array(
			'header_menu_1' => __( 'Header Menu 1', 'eyes-child' ),
			'header_menu_2' => __( 'Header Menu 2', 'eyes-child' ),
			'mobile_menu'   => __( 'Mobile Menu', 'eyes-child' ),
			'footer_menu'   => __( 'Footer Menu', 'eyes-child' ),
		)
	);
}
add_action( 'after_setup_theme', 'eyes_register_menus' );

/**
 * Create or restore a published theme page by slug.
 *
 * @param string $slug  Page slug (post_name).
 * @param string $title Page title.
 * @return int Page ID, or 0 on failure.
 */
function eyes_ensure_theme_page( $slug, $title ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );

	if ( $page instanceof WP_Post ) {
		if ( 'publish' === $page->post_status ) {
			return (int) $page->ID;
		}

		if ( 'trash' === $page->post_status ) {
			wp_untrash_post( $page->ID );
		}

		$updated = wp_update_post(
			array(
				'ID'          => $page->ID,
				'post_title'  => $title,
				'post_name'   => $slug,
				'post_status' => 'publish',
			),
			true
		);

		return is_wp_error( $updated ) ? 0 : (int) $page->ID;
	}

	$page_id = wp_insert_post(
		array(
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
			'post_author'  => 1,
		),
		true
	);

	return is_wp_error( $page_id ) ? 0 : (int) $page_id;
}

/**
 * Ensure theme pages exist (login, registo, reportar-problema).
 *
 * No manual page creation in wp-admin is required.
 */
function eyes_ensure_theme_pages() {
	static $done = false;

	if ( $done ) {
		return;
	}

	$done = true;

	$pages = array(
		'login'             => __( 'Entrar', 'eyes-child' ),
		'registo'           => __( 'Criar conta', 'eyes-child' ),
		'planos'            => __( 'Planos', 'eyes-child' ),
		'editar-perfil'     => __( 'Editar Perfil', 'eyes-child' ),
		'reportar-problema' => __( 'Reportar um problema', 'eyes-child' ),
	);

	$created_any = false;

	foreach ( $pages as $slug => $title ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );

		if ( $existing instanceof WP_Post && 'publish' === $existing->post_status ) {
			continue;
		}

		$page_id = eyes_ensure_theme_page( $slug, $title );

		if ( $page_id ) {
			$created_any = true;

			if ( 'reportar-problema' === $slug && function_exists( 'eyes_seed_report_problem_fields' ) ) {
				eyes_seed_report_problem_fields( $page_id );
			}
		}
	}

	if ( $created_any ) {
		flush_rewrite_rules( false );
	}
}

add_action( 'after_setup_theme', 'eyes_ensure_theme_pages' );
add_action( 'init', 'eyes_ensure_theme_pages', 20 );
add_action( 'after_switch_theme', 'eyes_ensure_theme_pages' );
