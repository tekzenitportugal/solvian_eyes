<?php
/**
 * User profile (edit profile, location meta, account deletion).
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Edit profile page URL.
 */
function eyes_edit_profile_url() {
	return home_url( '/editar-perfil/' );
}

/**
 * Localidade options for profile forms.
 *
 * @return array<string, string>
 */
function eyes_get_localidade_options() {
	return array(
		'porto'  => __( 'Porto', 'eyes-child' ),
		'lisboa' => __( 'Lisboa', 'eyes-child' ),
	);
}

/**
 * NUTS / region data keyed by localidade slug.
 *
 * @return array<string, array<string, string>>
 */
function eyes_get_localidade_nuts_map() {
	return array(
		'porto'  => array(
			'nuts2'  => __( 'Norte', 'eyes-child' ),
			'nuts3'  => __( 'Área Metropolitana do Porto', 'eyes-child' ),
			'regiao' => __( 'Continental - Norte', 'eyes-child' ),
		),
		'lisboa' => array(
			'nuts2'  => __( 'Lisboa e Vale do Tejo', 'eyes-child' ),
			'nuts3'  => __( 'Área Metropolitana de Lisboa', 'eyes-child' ),
			'regiao' => __( 'Continental - Centro', 'eyes-child' ),
		),
	);
}

/**
 * Persist NUTS and region meta from a localidade slug.
 *
 * @param int    $user_id    User ID.
 * @param string $localidade Localidade slug.
 */
function eyes_sync_profile_location_meta( $user_id, $localidade ) {
	$user_id    = (int) $user_id;
	$localidade = sanitize_key( $localidade );
	$map        = eyes_get_localidade_nuts_map();

	update_user_meta( $user_id, 'eyes_localidade', $localidade );

	if ( isset( $map[ $localidade ] ) ) {
		update_user_meta( $user_id, 'eyes_nuts2', $map[ $localidade ]['nuts2'] );
		update_user_meta( $user_id, 'eyes_nuts3', $map[ $localidade ]['nuts3'] );
		update_user_meta( $user_id, 'eyes_regiao', $map[ $localidade ]['regiao'] );
		return;
	}

	delete_user_meta( $user_id, 'eyes_nuts2' );
	delete_user_meta( $user_id, 'eyes_nuts3' );
	delete_user_meta( $user_id, 'eyes_regiao' );
}

/**
 * Persist sector label from CAE code.
 *
 * @param int    $user_id User ID.
 * @param string $cae     CAE code.
 */
function eyes_sync_profile_sector_meta( $user_id, $cae ) {
	$user_id = (int) $user_id;
	$cae     = sanitize_text_field( $cae );

	update_user_meta( $user_id, 'eyes_cae', $cae );

	if ( '' === $cae || ! function_exists( 'eyes_get_cae_codes' ) ) {
		delete_user_meta( $user_id, 'eyes_setor' );
		return;
	}

	$cae_codes = eyes_get_cae_codes();

	if ( ! isset( $cae_codes[ $cae ] ) ) {
		delete_user_meta( $user_id, 'eyes_setor' );
		return;
	}

	$label = function_exists( 'eyes_format_cae_label' )
		? eyes_format_cae_label( $cae, $cae_codes[ $cae ] )
		: $cae_codes[ $cae ];

	update_user_meta( $user_id, 'eyes_setor', $label );
}

/**
 * Profile form values for the current user.
 *
 * @param int $user_id User ID.
 * @return array<string, string>
 */
function eyes_get_user_profile_form_data( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	$user    = get_userdata( $user_id );

	if ( ! $user ) {
		return array();
	}

	$localidade = sanitize_key( (string) get_user_meta( $user_id, 'eyes_localidade', true ) );
	$nuts_map   = eyes_get_localidade_nuts_map();
	$nuts2      = (string) get_user_meta( $user_id, 'eyes_nuts2', true );
	$nuts3      = (string) get_user_meta( $user_id, 'eyes_nuts3', true );

	if ( '' === $nuts2 && isset( $nuts_map[ $localidade ] ) ) {
		$nuts2 = $nuts_map[ $localidade ]['nuts2'];
	}

	if ( '' === $nuts3 && isset( $nuts_map[ $localidade ] ) ) {
		$nuts3 = $nuts_map[ $localidade ]['nuts3'];
	}

	$unico = get_user_meta( $user_id, 'eyes_unico_estabelecimento', true );
	if ( ! in_array( $unico, array( 'sim', 'nao' ), true ) ) {
		$unico = 'sim';
	}

	return array(
		'nome'                  => eyes_current_user_full_name( $user ),
		'email'                 => $user->user_email,
		'telemovel'             => (string) get_user_meta( $user_id, 'eyes_telemovel', true ),
		'empresa'               => (string) get_user_meta( $user_id, 'eyes_empresa', true ),
		'nif'                   => (string) get_user_meta( $user_id, 'eyes_nif', true ),
		'cae'                   => (string) get_user_meta( $user_id, 'eyes_cae', true ),
		'ano'                   => (string) get_user_meta( $user_id, 'eyes_ano', true ),
		'website'               => (string) get_user_meta( $user_id, 'eyes_website', true ),
		'endereco'              => (string) get_user_meta( $user_id, 'eyes_endereco', true ),
		'codigo_postal'         => (string) get_user_meta( $user_id, 'eyes_codigo_postal', true ),
		'localidade'            => $localidade,
		'nuts2'                 => $nuts2,
		'nuts3'                 => $nuts3,
		'enquadramento_pme'     => (string) get_user_meta( $user_id, 'eyes_enquadramento_pme', true ),
		'unico_estabelecimento' => $unico,
	);
}

/**
 * Sanitize shared profile POST fields.
 *
 * @return array<string, string>
 */
function eyes_sanitize_profile_post_fields() {
	return array(
		'nome'                  => isset( $_POST['nome'] ) ? sanitize_text_field( wp_unslash( $_POST['nome'] ) ) : '',
		'email'                 => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
		'telemovel'             => isset( $_POST['telemovel'] ) ? sanitize_text_field( wp_unslash( $_POST['telemovel'] ) ) : '',
		'password'              => isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '',
		'empresa'               => isset( $_POST['empresa'] ) ? sanitize_text_field( wp_unslash( $_POST['empresa'] ) ) : '',
		'nif'                   => isset( $_POST['nif'] ) ? sanitize_text_field( wp_unslash( $_POST['nif'] ) ) : '',
		'cae'                   => isset( $_POST['cae'] ) ? sanitize_text_field( wp_unslash( $_POST['cae'] ) ) : '',
		'ano'                   => isset( $_POST['ano'] ) ? sanitize_text_field( wp_unslash( $_POST['ano'] ) ) : '',
		'website'               => isset( $_POST['website'] ) ? esc_url_raw( wp_unslash( $_POST['website'] ) ) : '',
		'endereco'              => isset( $_POST['endereco'] ) ? sanitize_text_field( wp_unslash( $_POST['endereco'] ) ) : '',
		'codigo_postal'         => isset( $_POST['codigo_postal'] ) ? sanitize_text_field( wp_unslash( $_POST['codigo_postal'] ) ) : '',
		'localidade'            => isset( $_POST['localidade'] ) ? sanitize_key( wp_unslash( $_POST['localidade'] ) ) : '',
		'enquadramento_pme'     => isset( $_POST['enquadramento_pme'] ) ? sanitize_key( wp_unslash( $_POST['enquadramento_pme'] ) ) : '',
		'unico_estabelecimento' => isset( $_POST['unico_estabelecimento'] ) ? sanitize_key( wp_unslash( $_POST['unico_estabelecimento'] ) ) : 'sim',
	);
}

/**
 * Validate shared profile fields.
 *
 * @param array<string, string> $fields   Sanitized fields.
 * @param bool                  $is_edit  Whether this is an edit (password optional).
 * @return true|WP_Error
 */
function eyes_validate_profile_fields( $fields, $is_edit = false ) {
	$required = array(
		'nome'          => __( 'Nome completo', 'eyes-child' ),
		'email'         => __( 'Email', 'eyes-child' ),
		'telemovel'     => __( 'Telemóvel', 'eyes-child' ),
		'empresa'       => __( 'Nome da empresa', 'eyes-child' ),
		'nif'           => __( 'NIF', 'eyes-child' ),
		'cae'           => __( 'CAE principal', 'eyes-child' ),
		'ano'           => __( 'Ano de constituição', 'eyes-child' ),
		'endereco'      => __( 'Endereço', 'eyes-child' ),
		'codigo_postal' => __( 'Código-postal', 'eyes-child' ),
		'localidade'    => __( 'Localidade', 'eyes-child' ),
		'enquadramento_pme' => __( 'Enquadramento PME', 'eyes-child' ),
	);

	foreach ( $required as $key => $label ) {
		if ( '' === $fields[ $key ] ) {
			return new WP_Error( 'eyes_profile', __( 'Preencha todos os campos obrigatórios.', 'eyes-child' ) );
		}
	}

	if ( ! is_email( $fields['email'] ) ) {
		return new WP_Error( 'eyes_profile', __( 'Introduza um email válido.', 'eyes-child' ) );
	}

	$localidades = eyes_get_localidade_options();
	if ( ! array_key_exists( $fields['localidade'], $localidades ) ) {
		return new WP_Error( 'eyes_profile', __( 'Seleccione uma localidade válida.', 'eyes-child' ) );
	}

	if ( function_exists( 'eyes_get_pme_enquadramento_options' ) ) {
		$pme_options = eyes_get_pme_enquadramento_options();
		if ( '' === $fields['enquadramento_pme'] || ! array_key_exists( $fields['enquadramento_pme'], $pme_options ) ) {
			return new WP_Error( 'eyes_profile', __( 'Seleccione um enquadramento PME válido.', 'eyes-child' ) );
		}
	}

	if ( ! in_array( $fields['unico_estabelecimento'], array( 'sim', 'nao' ), true ) ) {
		return new WP_Error( 'eyes_profile', __( 'Indique se este é o único estabelecimento da empresa.', 'eyes-child' ) );
	}

	if ( ! $is_edit && '' === $fields['password'] ) {
		return new WP_Error( 'eyes_profile', __( 'Preencha todos os campos obrigatórios.', 'eyes-child' ) );
	}

	if ( '' !== $fields['password'] && strlen( $fields['password'] ) < 8 ) {
		return new WP_Error( 'eyes_profile', __( 'A password deve ter pelo menos 8 caracteres.', 'eyes-child' ) );
	}

	return true;
}

/**
 * Save profile fields to user meta and WP user record.
 *
 * @param int                   $user_id User ID.
 * @param array<string, string> $fields  Sanitized fields.
 */
function eyes_save_user_profile_fields( $user_id, $fields ) {
	$user_id = (int) $user_id;
	$name_parts = preg_split( '/\s+/', $fields['nome'], 2 );

	$user_args = array(
		'ID'           => $user_id,
		'user_email'   => $fields['email'],
		'display_name' => $fields['nome'],
	);

	if ( '' !== $fields['password'] ) {
		$user_args['user_pass'] = $fields['password'];
	}

	wp_update_user( $user_args );

	update_user_meta( $user_id, 'first_name', $name_parts[0] );
	update_user_meta( $user_id, 'last_name', ! empty( $name_parts[1] ) ? $name_parts[1] : '' );
	update_user_meta( $user_id, 'eyes_telemovel', $fields['telemovel'] );
	update_user_meta( $user_id, 'eyes_empresa', $fields['empresa'] );
	update_user_meta( $user_id, 'eyes_nif', $fields['nif'] );
	update_user_meta( $user_id, 'eyes_ano', $fields['ano'] );
	update_user_meta( $user_id, 'eyes_website', $fields['website'] );
	update_user_meta( $user_id, 'eyes_endereco', $fields['endereco'] );
	update_user_meta( $user_id, 'eyes_codigo_postal', $fields['codigo_postal'] );
	update_user_meta( $user_id, 'eyes_unico_estabelecimento', $fields['unico_estabelecimento'] );
	update_user_meta( $user_id, 'eyes_enquadramento_pme', $fields['enquadramento_pme'] );

	eyes_sync_profile_location_meta( $user_id, $fields['localidade'] );
	eyes_sync_profile_sector_meta( $user_id, $fields['cae'] );
}

/**
 * Process edit profile form submission.
 *
 * @return true|WP_Error
 */
function eyes_process_edit_profile_form() {
	if ( ! is_user_logged_in() ) {
		return new WP_Error( 'eyes_profile', __( 'Tem de iniciar sessão.', 'eyes-child' ) );
	}

	if ( ! isset( $_POST['eyes_edit_profile_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eyes_edit_profile_nonce'] ) ), 'eyes_edit_profile' ) ) {
		return new WP_Error( 'eyes_profile', __( 'Sessão expirada. Tente novamente.', 'eyes-child' ) );
	}

	$user_id = get_current_user_id();
	$user    = get_userdata( $user_id );
	$fields  = eyes_sanitize_profile_post_fields();

	$valid = eyes_validate_profile_fields( $fields, true );
	if ( is_wp_error( $valid ) ) {
		return $valid;
	}

	$existing = get_user_by( 'email', $fields['email'] );
	if ( $existing && (int) $existing->ID !== $user_id ) {
		return new WP_Error( 'eyes_profile', __( 'Já existe uma conta com este email.', 'eyes-child' ) );
	}

	eyes_save_user_profile_fields( $user_id, $fields );

	return true;
}

/**
 * Process delete account request.
 *
 * @return true|WP_Error
 */
function eyes_process_delete_account_form() {
	if ( ! is_user_logged_in() ) {
		return new WP_Error( 'eyes_profile', __( 'Tem de iniciar sessão.', 'eyes-child' ) );
	}

	if ( ! isset( $_POST['eyes_delete_account_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eyes_delete_account_nonce'] ) ), 'eyes_delete_account' ) ) {
		return new WP_Error( 'eyes_profile', __( 'Sessão expirada. Tente novamente.', 'eyes-child' ) );
	}

	$user_id = get_current_user_id();

	if ( user_can( $user_id, 'manage_options' ) ) {
		return new WP_Error( 'eyes_profile', __( 'Não é possível apagar esta conta.', 'eyes-child' ) );
	}

	require_once ABSPATH . 'wp-admin/includes/user.php';

	wp_logout();
	wp_delete_user( $user_id );

	return true;
}

/**
 * Localize profile helpers for main.js.
 */
function eyes_localize_profile_script() {
	if ( ! wp_script_is( 'eyes-main', 'enqueued' ) || ! ( is_page( 'editar-perfil' ) || is_page( 'registo' ) ) ) {
		return;
	}

	wp_localize_script(
		'eyes-main',
		'eyesProfile',
		array(
			'localidadeNuts' => eyes_get_localidade_nuts_map(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'eyes_localize_profile_script', 35 );
