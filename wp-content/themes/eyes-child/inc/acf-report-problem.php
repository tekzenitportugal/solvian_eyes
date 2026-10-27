<?php
/**
 * ACF field group: Reportar Problema (slug reportar-problema).
 *
 * Imports into the database on first run so it appears in ACF → Field Groups
 * like Home page / Header Settings. Also available via acf-json sync.
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default field values (Portuguese copy from reportar-problema.html).
 *
 * @return array<string, string>
 */
function eyes_report_problem_default_fields() {
	return array(
		'report_page_title'              => 'Reportar um problema',
		'report_page_lead'               => 'Se detetou um erro, falha técnica ou qualquer comportamento inesperado na plataforma, descreva-nos o ocorrido para que a nossa equipa possa resolver a situação o mais rapidamente possível.',
		'report_title_label'             => 'Título do problema',
		'report_title_placeholder'       => 'Assunto do problema',
		'report_description_label'       => 'Descrição',
		'report_description_placeholder' => 'Descreva o seu problema',
		'report_submit_label'            => 'Submeter',
		'report_success_message'         => 'Obrigado. O seu reporte foi enviado com sucesso.',
		'report_recipient_email'         => '',
	);
}

/**
 * Full ACF field group definition (export-compatible).
 *
 * @return array<string, mixed>
 */
function eyes_acf_get_report_problem_field_group() {
	$defaults = eyes_report_problem_default_fields();
	$group_key = 'group_eyes_report_problem';

	return array(
		'key'                   => $group_key,
		'title'                 => 'Reportar Problema',
		'fields'                => array(
			array(
				'key'   => 'field_eyes_report_tab_content',
				'label' => 'Conteúdo da página',
				'name'  => '',
				'type'  => 'tab',
				'parent' => $group_key,
			),
			array(
				'key'           => 'field_eyes_report_page_title',
				'label'         => 'Título',
				'name'          => 'report_page_title',
				'type'          => 'text',
				'parent'        => $group_key,
				'default_value' => $defaults['report_page_title'],
			),
			array(
				'key'           => 'field_eyes_report_page_lead',
				'label'         => 'Texto introdutório',
				'name'          => 'report_page_lead',
				'type'          => 'textarea',
				'parent'        => $group_key,
				'rows'          => 4,
				'default_value' => $defaults['report_page_lead'],
			),
			array(
				'key'   => 'field_eyes_report_tab_form',
				'label' => 'Formulário',
				'name'  => '',
				'type'  => 'tab',
				'parent' => $group_key,
			),
			array(
				'key'           => 'field_eyes_report_title_label',
				'label'         => 'Etiqueta — título do problema',
				'name'          => 'report_title_label',
				'type'          => 'text',
				'parent'        => $group_key,
				'default_value' => $defaults['report_title_label'],
			),
			array(
				'key'           => 'field_eyes_report_title_placeholder',
				'label'         => 'Placeholder — título',
				'name'          => 'report_title_placeholder',
				'type'          => 'text',
				'parent'        => $group_key,
				'default_value' => $defaults['report_title_placeholder'],
			),
			array(
				'key'           => 'field_eyes_report_description_label',
				'label'         => 'Etiqueta — descrição',
				'name'          => 'report_description_label',
				'type'          => 'text',
				'parent'        => $group_key,
				'default_value' => $defaults['report_description_label'],
			),
			array(
				'key'           => 'field_eyes_report_description_placeholder',
				'label'         => 'Placeholder — descrição',
				'name'          => 'report_description_placeholder',
				'type'          => 'text',
				'parent'        => $group_key,
				'default_value' => $defaults['report_description_placeholder'],
			),
			array(
				'key'           => 'field_eyes_report_submit_label',
				'label'         => 'Texto do botão',
				'name'          => 'report_submit_label',
				'type'          => 'text',
				'parent'        => $group_key,
				'default_value' => $defaults['report_submit_label'],
			),
			array(
				'key'   => 'field_eyes_report_tab_messages',
				'label' => 'Mensagens e envio',
				'name'  => '',
				'type'  => 'tab',
				'parent' => $group_key,
			),
			array(
				'key'           => 'field_eyes_report_success_message',
				'label'         => 'Mensagem de sucesso',
				'name'          => 'report_success_message',
				'type'          => 'textarea',
				'parent'        => $group_key,
				'rows'          => 3,
				'default_value' => $defaults['report_success_message'],
			),
			array(
				'key'          => 'field_eyes_report_recipient_email',
				'label'        => 'Email de destino',
				'name'         => 'report_recipient_email',
				'type'         => 'email',
				'parent'       => $group_key,
				'instructions' => 'Deixe vazio para usar o email de administrador do WordPress.',
				'placeholder'  => 'geral@solvian.pt',
			),
		),
		'location'              => eyes_acf_get_report_problem_location(),
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'active'                => true,
	);
}

/**
 * ACF location rules: only on the reportar-problema page (Page is equal to …).
 *
 * @return array<int, array<int, array<string, string>>>
 */
function eyes_acf_get_report_problem_location() {
	$page_id = eyes_report_problem_page_id_for_acf();

	if ( $page_id > 0 ) {
		return array(
			array(
				array(
					'param'    => 'page',
					'operator' => '==',
					'value'    => (string) $page_id,
				),
			),
		);
	}

	return array(
		array(
			array(
				'param'    => 'post_type',
				'operator' => '==',
				'value'    => 'page',
			),
		),
	);
}

/**
 * Whether the saved location rule is invalid (empty value, etc.).
 *
 * @param mixed $location ACF location array.
 */
function eyes_acf_report_location_is_broken( $location ) {
	if ( ! is_array( $location ) || empty( $location[0][0] ) ) {
		return true;
	}

	$rule = $location[0][0];

	if ( empty( $rule['param'] ) || empty( $rule['operator'] ) ) {
		return true;
	}

	if ( ! isset( $rule['value'] ) || '' === (string) $rule['value'] ) {
		return true;
	}

	if ( 'page' === $rule['param'] ) {
		$page_id = eyes_report_problem_page_id_for_acf();
		return $page_id > 0 && (string) $page_id !== (string) $rule['value'];
	}

	// Fell back to "all pages" or invalid post type rule — repair when we have a target page.
	if ( 'post_type' === $rule['param'] && eyes_report_problem_page_id_for_acf() > 0 ) {
		return true;
	}

	return false;
}

/**
 * Fix location rules in the database when ACF shows an empty/broken rule.
 */
function eyes_acf_repair_report_problem_field_group() {
	if ( ! function_exists( 'acf_get_field_group' ) || ! function_exists( 'acf_update_field_group' ) ) {
		return;
	}

	$page_id = eyes_report_problem_page_id_for_acf();

	if ( $page_id <= 0 ) {
		return;
	}

	$group = acf_get_field_group( 'group_eyes_report_problem' );

	if ( ! $group || empty( $group['ID'] ) ) {
		return;
	}

	$location = isset( $group['location'] ) ? $group['location'] : array();

	if ( ! eyes_acf_report_location_is_broken( $location ) ) {
		return;
	}

	$group['location'] = eyes_acf_get_report_problem_location();
	acf_update_field_group( $group );
}

/**
 * Page ID for ACF location rule (0 until page exists).
 */
function eyes_report_problem_page_id_for_acf() {
	if ( function_exists( 'eyes_report_problem_page_id' ) ) {
		$page_id = eyes_report_problem_page_id();
		if ( $page_id ) {
			return $page_id;
		}
	}

	$page = get_page_by_path( 'reportar-problema', OBJECT, 'page' );

	return $page instanceof WP_Post ? (int) $page->ID : 0;
}

/**
 * Import field group into DB so it appears under ACF → Field Groups.
 */
function eyes_acf_import_report_problem_field_group() {
	if ( ! function_exists( 'acf_import_field_group' ) ) {
		return false;
	}

	$key = 'group_eyes_report_problem';

	$existing_posts = get_posts(
		array(
			'post_type'              => 'acf-field-group',
			'posts_per_page'         => 1,
			'post_status'            => 'any',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array(
					'key'   => 'key',
					'value' => $key,
				),
			),
		)
	);

	if ( $existing_posts ) {
		update_option( 'eyes_report_acf_group_imported', '1', false );
		return true;
	}

	$group  = eyes_acf_get_report_problem_field_group();
	$result = acf_import_field_group( $group );

	if ( false !== $result && null !== $result ) {
		update_option( 'eyes_report_acf_group_imported', '1', false );
		return true;
	}

	return false;
}

/**
 * Register / import on ACF init and when opening Field Groups admin.
 */
function eyes_acf_bootstrap_report_problem_field_group() {
	if ( ! function_exists( 'acf_get_field_group' ) ) {
		return;
	}

	if ( get_option( 'eyes_report_acf_group_imported' ) ) {
		return;
	}

	eyes_acf_import_report_problem_field_group();
}
add_action( 'acf/init', 'eyes_acf_bootstrap_report_problem_field_group', 5 );

/**
 * Retry import after theme pages are ensured (page ID needed for location rule).
 */
function eyes_acf_retry_report_problem_import() {
	if ( get_option( 'eyes_report_acf_group_imported' ) ) {
		return;
	}

	if ( ! eyes_report_problem_page_id_for_acf() ) {
		return;
	}

	eyes_acf_import_report_problem_field_group();
}
add_action( 'init', 'eyes_acf_retry_report_problem_import', 99 );

/**
 * Keep location rules correct after the report page exists.
 */
function eyes_acf_repair_report_problem_on_init() {
	eyes_acf_repair_report_problem_field_group();
}
add_action( 'init', 'eyes_acf_repair_report_problem_on_init', 100 );

/**
 * Import when an admin loads wp-admin (acf/init can run before pages exist).
 */
function eyes_acf_admin_import_report_problem_field_group() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	eyes_acf_bootstrap_report_problem_field_group();
	eyes_acf_repair_report_problem_field_group();
}
add_action( 'admin_init', 'eyes_acf_admin_import_report_problem_field_group', 20 );

/**
 * Hint when JSON sync is available but the group is not in the database yet.
 */
function eyes_acf_report_problem_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || 'edit-acf-field-group' !== $screen->id ) {
		return;
	}

	if ( get_option( 'eyes_report_acf_group_imported' ) ) {
		return;
	}

	echo '<div class="notice notice-info"><p>';
	echo esc_html__( 'Reportar Problema: set Location to Page → is equal to → Reportar um problema, then Save. Or reload this screen to auto-fix.', 'eyes-child' );
	echo '</p></div>';
}
add_action( 'admin_notices', 'eyes_acf_report_problem_admin_notice' );

/**
 * Theme acf-json folder (sync in ACF admin).
 *
 * @param string $path Default save path.
 */
function eyes_acf_json_save_path( $path ) {
	return get_stylesheet_directory() . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'eyes_acf_json_save_path' );

/**
 * @param array<int, string> $paths Load paths.
 * @return array<int, string>
 */
function eyes_acf_json_load_paths( $paths ) {
	$paths[] = get_stylesheet_directory() . '/acf-json';

	return $paths;
}
add_filter( 'acf/settings/load_json', 'eyes_acf_json_load_paths' );

/**
 * Seed default ACF values on the reportar-problema page (once per page).
 *
 * @param int $page_id Page ID.
 */
function eyes_seed_report_problem_fields( $page_id ) {
	$page_id = (int) $page_id;

	if ( $page_id <= 0 || ! function_exists( 'update_field' ) ) {
		return;
	}

	if ( get_post_meta( $page_id, '_eyes_report_defaults_seeded', true ) ) {
		return;
	}

	foreach ( eyes_report_problem_default_fields() as $field_name => $value ) {
		$existing = get_field( $field_name, $page_id );

		if ( is_string( $existing ) && '' !== trim( $existing ) ) {
			continue;
		}

		update_field( $field_name, $value, $page_id );
	}

	update_post_meta( $page_id, '_eyes_report_defaults_seeded', '1' );
}

/**
 * Seed defaults for an existing page after ACF is available.
 */
function eyes_maybe_seed_report_problem_fields() {
	if ( ! function_exists( 'get_field' ) ) {
		return;
	}

	$page_id = eyes_report_problem_page_id();

	if ( $page_id ) {
		eyes_seed_report_problem_fields( $page_id );
	}
}
add_action( 'acf/init', 'eyes_maybe_seed_report_problem_fields', 20 );
