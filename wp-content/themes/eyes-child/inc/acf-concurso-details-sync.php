<?php
/**
 * Sync Concurso Details ACF group: remove unused PRO Plan tab, add Pro content tab.
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * ACF field group key for Concurso Details.
 */
function eyes_concurso_details_field_group_key() {
	return 'group_6a1ff18f637e2';
}

/**
 * Legacy PRO Plan tab + fields to remove from Concurso Details.
 *
 * @return string[]
 */
function eyes_concurso_details_legacy_pro_plan_field_keys() {
	return array(
		'field_6a20080cf6eb7',
		'field_6a20081ef6eb8',
		'field_6a20082df6eb9',
		'field_6a200836f6eba',
		'field_6a200845f6ebb',
		'field_6a20084df6ebc',
		'field_6a200854f6ebd',
		'field_6a20086af6ebe',
		'field_6a200871f6ebf',
	);
}

/**
 * Remove every DB row for an ACF field key (handles orphaned/duplicate posts).
 *
 * @param string $field_key ACF field key.
 */
function eyes_concurso_details_purge_acf_field_key( $field_key ) {
	global $wpdb;

	if ( function_exists( 'acf_delete_field' ) ) {
		acf_delete_field( $field_key );
	}

	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'acf-field' AND post_name = %s",
			$field_key
		)
	);

	foreach ( $ids as $id ) {
		wp_delete_post( (int) $id, true );
	}
}

/**
 * Replace unused PRO Plan ACF fields with the editable Pro content tab.
 */
function eyes_sync_concurso_details_acf_group() {
	if ( ! function_exists( 'acf_update_field' ) || ! function_exists( 'acf_delete_field' ) ) {
		return;
	}

	$version = '2026-06-13-pro-tab-v3';
	if ( get_option( 'eyes_concurso_details_acf_sync_version' ) === $version ) {
		return;
	}

	$group_key = eyes_concurso_details_field_group_key();
	$group     = acf_get_field_group( $group_key );

	if ( ! $group || empty( $group['ID'] ) ) {
		return;
	}

	$parent_id = (int) $group['ID'];

	foreach ( eyes_concurso_details_legacy_pro_plan_field_keys() as $field_key ) {
		eyes_concurso_details_purge_acf_field_key( $field_key );
	}

	foreach ( array( 'field_eyes_concurso_details_pro_tab', 'field_eyes_concurso_pro_admin_ui' ) as $field_key ) {
		eyes_concurso_details_purge_acf_field_key( $field_key );
	}

	acf_update_field(
		array(
			'key'        => 'field_eyes_concurso_details_pro_tab',
			'label'      => __( 'Conteúdo Pro (detalhe do concurso)', 'eyes-child' ),
			'name'       => '',
			'type'       => 'tab',
			'parent'     => $parent_id,
			'menu_order' => 26,
			'placement'  => 'top',
			'endpoint'   => 0,
		)
	);

	acf_update_field(
		array(
			'key'         => 'field_eyes_concurso_pro_admin_ui',
			'label'       => '',
			'name'        => 'eyes_pro_content_admin',
			'type'        => 'message',
			'parent'      => $parent_id,
			'menu_order'  => 27,
			'message'     => '',
			'new_lines'   => '',
			'esc_html'    => 0,
			'wrapper'     => array(
				'width' => '',
				'class' => 'eyes-pro-admin-field-wrap',
				'id'    => '',
			),
		)
	);

	update_option( 'eyes_concurso_details_acf_sync_version', $version, false );
}
add_action( 'acf/init', 'eyes_sync_concurso_details_acf_group', 15 );

/**
 * Retry sync when an admin opens wp-admin (acf/init can run before ACF is fully ready).
 */
function eyes_sync_concurso_details_acf_group_admin() {
	if ( ! is_admin() || get_option( 'eyes_concurso_details_acf_sync_version' ) ) {
		return;
	}

	eyes_sync_concurso_details_acf_group();
}
add_action( 'admin_init', 'eyes_sync_concurso_details_acf_group_admin', 20 );
