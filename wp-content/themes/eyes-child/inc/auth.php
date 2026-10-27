<?php
/**
 * Login, registration, and concurso favourites.
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Login page URL with optional redirect after sign-in.
 *
 * @param string $redirect_to Target URL after login.
 */
function eyes_login_url( $redirect_to = '' ) { $page = get_page_by_path( 'login' ); if ( $page ) { // Get translated page ID for current language 
$translated_id = apply_filters( 'wpml_object_id', $page->ID, 'page', true ); 
$url = get_permalink( $translated_id ); } 
else 
{ 
	$url = home_url( '/login/' ); 
} 
if ( $redirect_to ) 
{ 
	$url = add_query_arg( 'redirect_to', rawurlencode( $redirect_to ), $url ); 
} 
return $url; 
}

/**
 * Registration page URL.
 */
function eyes_registo_url() {
	return home_url( '/registo/' );
}

/**
 * Sanitize redirect target for auth flows.
 *
 * @param string $redirect_to Requested redirect.
 */
function eyes_auth_redirect_target( $redirect_to = '' ) {

	if ( ! $redirect_to && isset( $_GET['redirect_to'] ) ) {
		$redirect_to = wp_unslash( $_GET['redirect_to'] );
	}

	$fallback = get_post_type_archive_link( 'concurso' );

	if ( ! $fallback ) {
		$fallback = home_url( '/' );
	}

	if ( empty( $redirect_to ) ) {
		return $fallback;
	}

	return wp_validate_redirect( $redirect_to, $fallback );
}

/**
 * Stored favourite concurso IDs for a user.
 *
 * @param int $user_id User ID.
 * @return int[]
 */
function eyes_get_favourite_concurso_ids( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	if ( $user_id <= 0 ) {
		return array();
	}

	$ids = get_user_meta( $user_id, 'eyes_favourite_concursos', true );

	if ( ! is_array( $ids ) ) {
		return array();
	}

	return array_values( array_unique( array_filter( array_map( 'intval', $ids ) ) ) );
}

/**
 * Whether a concurso is favourited by the current user.
 *
 * @param int $post_id Concurso post ID.
 */
function eyes_is_favourite_concurso( $post_id ) {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	return in_array( (int) $post_id, eyes_get_favourite_concurso_ids(), true );
}

/**
 * Toggle favourite state. Returns true when saved, false when removed.
 *
 * @param int $user_id User ID.
 * @param int $post_id Concurso post ID.
 */
function eyes_toggle_favourite_concurso( $user_id, $post_id ) {
	$user_id = (int) $user_id;
	$post_id = (int) $post_id;

	if ( $user_id <= 0 || $post_id <= 0 || 'concurso' !== get_post_type( $post_id ) ) {
		return false;
	}

	$ids = eyes_get_favourite_concurso_ids( $user_id );

	if ( in_array( $post_id, $ids, true ) ) {
		$ids = array_values( array_diff( $ids, array( $post_id ) ) );
		update_user_meta( $user_id, 'eyes_favourite_concursos', $ids );
		return false;
	}

	$ids[] = $post_id;
	update_user_meta( $user_id, 'eyes_favourite_concursos', $ids );
	return true;
}

/**
 * Detail URL: permalink when logged in, login with redirect when guest.
 *
 * @param int $post_id Concurso post ID.
 */
function eyes_concurso_detail_url( $post_id ) {
	$post_id = (int) $post_id;
	$target  = get_permalink( $post_id );

	if ( is_user_logged_in() ) {
		return $target ? $target : home_url( '/' );
	}

	return eyes_login_url( $target ? $target : '' );
}

/**
 * Process login form submission.
 *
 * @return true|WP_Error
 */
function eyes_process_login_form() {
	if ( ! isset( $_POST['eyes_login_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eyes_login_nonce'] ) ), 'eyes_login' ) ) {
		return new WP_Error( 'eyes_login', __( 'Sessão expirada. Tente novamente.', 'eyes-child' ) );
	}

	$login    = isset( $_POST['user'] ) ? sanitize_text_field( wp_unslash( $_POST['user'] ) ) : '';
	$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
	$remember = ! empty( $_POST['remember'] );

	if ( '' === $login || '' === $password ) {
		return new WP_Error( 'eyes_login', __( 'Preencha o email/username e a password.', 'eyes-child' ) );
	}

	$creds = array(
		'user_login'    => $login,
		'user_password' => $password,
		'remember'      => $remember,
	);

	if ( is_email( $login ) ) {
		$user = get_user_by( 'email', $login );
		if ( $user ) {
			$creds['user_login'] = $user->user_login;
		}
	}
	
	$user = wp_signon( $creds, is_ssl() );
	

if ( is_wp_error( $user ) ) {
    return $user;
}

return true;
}

/**
 * Build a unique username from an email address.
 *
 * @param string $email Email address.
 */
function eyes_generate_username_from_email( $email ) {
	$base = sanitize_user( current( explode( '@', $email ) ), true );

	if ( '' === $base ) {
		$base = 'user';
	}

	$username = $base;
	$index    = 1;

	while ( username_exists( $username ) ) {
		$username = $base . $index;
		++$index;
	}

	return $username;
}

/**
 * Process registration form submission.
 *
 * @return true|WP_Error
 */
function eyes_process_registo_form() {
	if ( ! isset( $_POST['eyes_registo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eyes_registo_nonce'] ) ), 'eyes_registo' ) ) {
		return new WP_Error( 'eyes_registo', __( 'Sessão expirada. Tente novamente.', 'eyes-child' ) );
	}

	$nome             = isset( $_POST['nome'] ) ? sanitize_text_field( wp_unslash( $_POST['nome'] ) ) : '';
	$email            = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$telemovel        = isset( $_POST['telemovel'] ) ? sanitize_text_field( wp_unslash( $_POST['telemovel'] ) ) : '';
	$password         = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
	$password_repeat  = isset( $_POST['password_repeat'] ) ? (string) wp_unslash( $_POST['password_repeat'] ) : '';
	$empresa          = isset( $_POST['empresa'] ) ? sanitize_text_field( wp_unslash( $_POST['empresa'] ) ) : '';
	$nif              = isset( $_POST['nif'] ) ? sanitize_text_field( wp_unslash( $_POST['nif'] ) ) : '';
	$cae              = isset( $_POST['cae'] ) ? sanitize_text_field( wp_unslash( $_POST['cae'] ) ) : '';
	$ano              = isset( $_POST['ano'] ) ? sanitize_text_field( wp_unslash( $_POST['ano'] ) ) : '';
	$website          = isset( $_POST['website'] ) ? esc_url_raw( wp_unslash( $_POST['website'] ) ) : '';
	$endereco         = isset( $_POST['endereco'] ) ? sanitize_text_field( wp_unslash( $_POST['endereco'] ) ) : '';
	$codigo_postal    = isset( $_POST['codigo_postal'] ) ? sanitize_text_field( wp_unslash( $_POST['codigo_postal'] ) ) : '';
	$localidade       = isset( $_POST['localidade'] ) ? sanitize_text_field( wp_unslash( $_POST['localidade'] ) ) : '';
	$enquadramento    = isset( $_POST['enquadramento_pme'] ) ? sanitize_key( wp_unslash( $_POST['enquadramento_pme'] ) ) : '';
	$termos           = ! empty( $_POST['termos'] );

	if ( '' === $nome || '' === $email || '' === $telemovel || '' === $password || '' === $empresa || '' === $nif ) {
		return new WP_Error( 'eyes_registo', __( 'Preencha todos os campos obrigatórios.', 'eyes-child' ) );
	}

	if ( ! is_email( $email ) ) {
		return new WP_Error( 'eyes_registo', __( 'Introduza um email válido.', 'eyes-child' ) );
	}

	if ( email_exists( $email ) ) {
		return new WP_Error( 'eyes_registo', __( 'Já existe uma conta com este email.', 'eyes-child' ) );
	}

	if ( $password !== $password_repeat ) {
		return new WP_Error( 'eyes_registo', __( 'As passwords não coincidem.', 'eyes-child' ) );
	}

	if ( strlen( $password ) < 8 ) {
		return new WP_Error( 'eyes_registo', __( 'A password deve ter pelo menos 8 caracteres.', 'eyes-child' ) );
	}

	if ( ! $termos ) {
		return new WP_Error( 'eyes_registo', __( 'Tem de aceitar os termos e condições.', 'eyes-child' ) );
	}

	if ( function_exists( 'eyes_get_pme_enquadramento_options' ) ) {
		$pme_options = eyes_get_pme_enquadramento_options();
		if ( '' === $enquadramento || ! array_key_exists( $enquadramento, $pme_options ) ) {
			return new WP_Error( 'eyes_registo', __( 'Seleccione um enquadramento PME válido.', 'eyes-child' ) );
		}
	}

	$username = eyes_generate_username_from_email( $email );
	$user_id  = wp_create_user( $username, $password, $email );

	if ( is_wp_error( $user_id ) ) {
		return new WP_Error( 'eyes_registo', $user_id->get_error_message() );
	}

	$name_parts = preg_split( '/\s+/', $nome, 2 );
	update_user_meta( $user_id, 'first_name', $name_parts[0] );
	if ( ! empty( $name_parts[1] ) ) {
		update_user_meta( $user_id, 'last_name', $name_parts[1] );
	}

	update_user_meta( $user_id, 'eyes_telemovel', $telemovel );
	update_user_meta( $user_id, 'eyes_empresa', $empresa );
	update_user_meta( $user_id, 'eyes_nif', $nif );
	update_user_meta( $user_id, 'eyes_ano', $ano );
	update_user_meta( $user_id, 'eyes_website', $website );
	update_user_meta( $user_id, 'eyes_endereco', $endereco );
	update_user_meta( $user_id, 'eyes_codigo_postal', $codigo_postal );
	eyes_sync_profile_location_meta( $user_id, $localidade );
	eyes_sync_profile_sector_meta( $user_id, $cae );
	update_user_meta( $user_id, 'eyes_enquadramento_pme', $enquadramento );
	update_user_meta( $user_id, 'eyes_unico_estabelecimento', 'sim' );

	wp_update_user(
		array(
			'ID'           => $user_id,
			'display_name' => $nome,
			'role'         => 'subscriber',
		)
	);

	$signon = wp_signon(
		array(
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => true,
		),
		is_ssl()
	);

	if ( is_wp_error( $signon ) ) {
		return new WP_Error( 'eyes_registo', __( 'Conta criada, mas não foi possível iniciar sessão automaticamente. Faça login.', 'eyes-child' ) );
	}

	return true;
}

/**
 * Config object for concursos-actions.js (favourites, apply, consult).
 *
 * @return array<string, mixed>
 */
function eyes_get_auth_script_config() {
	return array(
		'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
		'nonce'           => wp_create_nonce( 'eyes_favourites' ),
		'actionsNonce'    => wp_create_nonce( 'eyes_concurso_actions' ),
		'loginUrl'        => eyes_login_url(),
		'isLoggedIn'      => is_user_logged_in(),
		'favourites'      => eyes_get_favourite_concurso_ids(),
		'consultRequests' => eyes_get_consult_request_ids(),
		'appliedConcursos'=> function_exists( 'eyes_get_applied_concurso_ids' ) ? eyes_get_applied_concurso_ids() : array(),
		'starIcon'        => eyes_asset_uri( 'images/icon-star-outline.svg' ),
		'starIconActive'  => eyes_asset_uri( 'images/icon-star-filled.svg' ),
		'i18n'            => array(
			'saveError'        => __( 'Não foi possível guardar. Tente novamente.', 'eyes-child' ),
			'consultError'     => __( 'Não foi possível enviar o pedido. Tente novamente.', 'eyes-child' ),
			'applyError'       => __( 'Não foi possível registar a candidatura. Tente novamente.', 'eyes-child' ),
			'appliedLabel'     => __( 'Candidatura registada', 'eyes-child' ),
			'consultSentLabel' => __( 'Pedido enviado', 'eyes-child' ),
			'tabCount'         => __( '%d encontradas', 'eyes-child' ),
		),
	);
}

/**
 * Whether concurso action scripts should load.
 */
function eyes_should_enqueue_concurso_actions() {
	if ( is_singular( 'concurso' ) ) {
		return true;
	}

	return function_exists( 'eyes_is_concurso_listing_page' ) && eyes_is_concurso_listing_page();
}

/**
 * AJAX: toggle favourite concurso.
 */
function eyes_ajax_toggle_favourite_concurso() {
	if ( ! check_ajax_referer( 'eyes_favourites', 'nonce', false ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Sessão expirada. Atualize a página e tente novamente.', 'eyes-child' ),
			),
			403
		);
	}

	if ( ! is_user_logged_in() ) {
		wp_send_json_error(
			array(
				'message'   => __( 'Inicie sessão para guardar concursos.', 'eyes-child' ),
				'login_url' => eyes_login_url(),
			),
			401
		);
	}

	$post_id = isset( $_POST['concurso_id'] ) ? absint( $_POST['concurso_id'] ) : 0;

	if ( ! $post_id || 'concurso' !== get_post_type( $post_id ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Concurso inválido.', 'eyes-child' ) ),
			400
		);
	}

	$saved = eyes_toggle_favourite_concurso( get_current_user_id(), $post_id );
	$user_id = get_current_user_id();
	$favourites = eyes_get_favourite_concurso_ids( $user_id );

	wp_send_json_success(
		array(
			'saved'       => $saved,
			'concurso_id' => $post_id,
			'favourites'  => $favourites,
			'saved_count' => count( $favourites ),
		)
	);
}
add_action( 'wp_ajax_eyes_toggle_favourite', 'eyes_ajax_toggle_favourite_concurso' );

/**
 * Concurso IDs with a submitted consult request.
 *
 * @param int $user_id User ID.
 * @return int[]
 */
function eyes_get_consult_request_ids( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	if ( $user_id <= 0 ) {
		return array();
	}

	$ids = get_user_meta( $user_id, 'eyes_consult_requests', true );

	if ( ! is_array( $ids ) ) {
		return array();
	}

	return array_values( array_unique( array_filter( array_map( 'intval', $ids ) ) ) );
}

/**
 * Whether the user submitted a consult request for a concurso.
 *
 * @param int $post_id Concurso post ID.
 * @param int $user_id User ID.
 */
function eyes_has_consult_request( $post_id, $user_id = 0 ) {
	if ( ! is_user_logged_in() && ! $user_id ) {
		return false;
	}

	return in_array( (int) $post_id, eyes_get_consult_request_ids( $user_id ), true );
}

/**
 * Store a consult request for a concurso.
 *
 * @param int $user_id User ID.
 * @param int $post_id Concurso post ID.
 */
function eyes_submit_consult_request( $user_id, $post_id ) {
	$user_id = (int) $user_id;
	$post_id = (int) $post_id;

	if ( $user_id <= 0 || $post_id <= 0 || 'concurso' !== get_post_type( $post_id ) ) {
		return false;
	}

	$ids = eyes_get_consult_request_ids( $user_id );

	if ( in_array( $post_id, $ids, true ) ) {
		return true;
	}

	$ids[] = $post_id;
	update_user_meta( $user_id, 'eyes_consult_requests', $ids );

	/**
	 * Fires after a user requests consult support for a concurso.
	 *
	 * @param int $user_id User ID.
	 * @param int $post_id Concurso post ID.
	 */
	do_action( 'eyes_consult_request_submitted', $user_id, $post_id );

	return true;
}

/**
 * Mark a concurso as applied/candidatura feita for a user.
 *
 * @param int $user_id User ID.
 * @param int $post_id Concurso post ID.
 */
function eyes_mark_concurso_applied( $user_id, $post_id ) {
	$user_id = (int) $user_id;
	$post_id = (int) $post_id;

	if ( $user_id <= 0 || $post_id <= 0 || 'concurso' !== get_post_type( $post_id ) ) {
		return false;
	}

	if ( ! function_exists( 'eyes_get_applied_concurso_ids' ) ) {
		return false;
	}

	$ids = eyes_get_applied_concurso_ids( $user_id );

	if ( in_array( $post_id, $ids, true ) ) {
		return true;
	}

	$ids[] = $post_id;
	update_user_meta( $user_id, 'eyes_applied_concursos', $ids );

	return true;
}

/**
 * Whether the user marked a concurso as applied.
 *
 * @param int $post_id Concurso post ID.
 * @param int $user_id User ID.
 */
function eyes_has_applied_concurso( $post_id, $user_id = 0 ) {
	if ( ! function_exists( 'eyes_get_applied_concurso_ids' ) ) {
		return false;
	}

	return in_array( (int) $post_id, eyes_get_applied_concurso_ids( $user_id ), true );
}

/**
 * AJAX: submit consult request for a concurso.
 */
function eyes_ajax_submit_consult_request() {
	if ( ! check_ajax_referer( 'eyes_concurso_actions', 'nonce', false ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Sessão expirada. Atualize a página e tente novamente.', 'eyes-child' ),
			),
			403
		);
	}

	if ( ! is_user_logged_in() ) {
		wp_send_json_error(
			array(
				'message'   => __( 'Inicie sessão para pedir consultoria.', 'eyes-child' ),
				'login_url' => eyes_login_url(),
			),
			401
		);
	}

	$post_id = isset( $_POST['concurso_id'] ) ? absint( $_POST['concurso_id'] ) : 0;

	if ( ! $post_id || 'concurso' !== get_post_type( $post_id ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Concurso inválido.', 'eyes-child' ) ),
			400
		);
	}

	eyes_submit_consult_request( get_current_user_id(), $post_id );

	wp_send_json_success(
		array(
			'concurso_id' => $post_id,
			'submitted'   => true,
		)
	);
}
add_action( 'wp_ajax_eyes_submit_consult_request', 'eyes_ajax_submit_consult_request' );

/**
 * AJAX: mark concurso as applied (Candidatar).
 */
function eyes_ajax_mark_concurso_applied() {
	if ( ! check_ajax_referer( 'eyes_concurso_actions', 'nonce', false ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Sessão expirada. Atualize a página e tente novamente.', 'eyes-child' ),
			),
			403
		);
	}

	if ( ! is_user_logged_in() ) {
		wp_send_json_error(
			array(
				'message'   => __( 'Inicie sessão para candidatar.', 'eyes-child' ),
				'login_url' => eyes_login_url(),
			),
			401
		);
	}

	$post_id = isset( $_POST['concurso_id'] ) ? absint( $_POST['concurso_id'] ) : 0;

	if ( ! $post_id || 'concurso' !== get_post_type( $post_id ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Concurso inválido.', 'eyes-child' ) ),
			400
		);
	}

	eyes_mark_concurso_applied( get_current_user_id(), $post_id );

	wp_send_json_success(
		array(
			'concurso_id' => $post_id,
			'applied'     => true,
		)
	);
}
add_action( 'wp_ajax_eyes_mark_concurso_applied', 'eyes_ajax_mark_concurso_applied' );

/**
 * Email admin when a user requests consult support.
 *
 * @param int $user_id User ID.
 * @param int $post_id Concurso post ID.
 */
function eyes_notify_admin_consult_request( $user_id, $post_id ) {
	$user_id = (int) $user_id;
	$post_id = (int) $post_id;
	$user    = get_userdata( $user_id );

	if ( ! $user || $post_id <= 0 ) {
		return;
	}

	$to = get_option( 'admin_email' );

	if ( ! is_email( $to ) ) {
		return;
	}

	$title = function_exists( 'get_field' ) ? (string) get_field( 'concurso_title', $post_id ) : get_the_title( $post_id );

	if ( '' === trim( $title ) ) {
		$title = get_the_title( $post_id );
	}

	$subject = sprintf(
		/* translators: %s: concurso title */
		__( '[Solvian Eyes] Pedido de consultoria: %s', 'eyes-child' ),
		$title
	);

	$message_lines = array(
		__( 'Novo pedido de consultoria na plataforma Solvian Eyes.', 'eyes-child' ),
		'',
		sprintf( '%s: %s', __( 'Concurso', 'eyes-child' ), $title ),
		sprintf( '%s: %s', __( 'Utilizador', 'eyes-child' ), $user->display_name ),
		sprintf( '%s: %s', __( 'Email', 'eyes-child' ), $user->user_email ),
		sprintf( '%s: %s', __( 'Empresa', 'eyes-child' ), (string) get_user_meta( $user_id, 'eyes_empresa', true ) ),
		sprintf( '%s: %s', __( 'URL', 'eyes-child' ), get_permalink( $post_id ) ),
	);

	wp_mail( $to, $subject, implode( "\n", $message_lines ) );
}
add_action( 'eyes_consult_request_submitted', 'eyes_notify_admin_consult_request', 10, 2 );

/**
 * Enqueue and localize concursos action script.
 */
function eyes_enqueue_concurso_actions_script() {
	if ( ! eyes_should_enqueue_concurso_actions() ) {
		return;
	}

	$actions_js = get_stylesheet_directory() . '/assets/js/concursos-actions.js';

	if ( ! file_exists( $actions_js ) ) {
		return;
	}

	wp_enqueue_script(
		'eyes-concursos-actions',
		eyes_asset_uri( 'js/concursos-actions.js' ),
		array(),
		(string) filemtime( $actions_js ),
		true
	);

	wp_localize_script(
		'eyes-concursos-actions',
		'eyesAuth',
		eyes_get_auth_script_config()
	);
}
add_action( 'wp_enqueue_scripts', 'eyes_enqueue_concurso_actions_script', 25 );
