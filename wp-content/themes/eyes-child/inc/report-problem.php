<?php
/**
 * Report a problem form handling.
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Report problem page URL.
 */
function eyes_report_problem_url() {
	return home_url( '/reportar-problema/' );
}

/**
 * Page ID for reportar-problema (WPML-aware).
 */
function eyes_report_problem_page_id() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$page = get_page_by_path( 'reportar-problema', OBJECT, 'page' );

	if ( ! $page instanceof WP_Post ) {
		$cached = 0;
		return 0;
	}

	$page_id = (int) $page->ID;

	if ( has_filter( 'wpml_object_id' ) ) {
		$page_id = (int) apply_filters( 'wpml_object_id', $page_id, 'page', true );
	}

	$cached = $page_id;

	return $page_id;
}

/**
 * ACF text for the report page (same pattern as front-page fields on the current page).
 *
 * @param string $field_name Field name.
 * @param string $fallback   Fallback when empty or ACF inactive.
 */
function eyes_report_acf_text( $field_name, $fallback = '' ) {
	$post_id = eyes_report_problem_page_id();

	if ( ! $post_id && in_the_loop() ) {
		$post_id = null;
	}

	return eyes_acf_text( $field_name, $fallback, $post_id ? $post_id : null );
}

/**
 * Whether the current request is the report-problem page.
 */
function eyes_is_report_problem_page() {
	if ( is_page( 'reportar-problema' ) ) {
		return true;
	}

	$queried = get_queried_object();

	return $queried instanceof WP_Post
		&& 'page' === $queried->post_type
		&& 'reportar-problema' === $queried->post_name;
}

/**
 * Use the custom /login/ page instead of wp-login.php on the front end.
 *
 * @param string $login_url   Default login URL.
 * @param string $redirect    Redirect target.
 * @param bool   $force_reauth Force reauthentication.
 */
function eyes_front_login_url( $login_url, $redirect, $force_reauth ) {
	if ( is_admin() ) {
		return $login_url;
	}

	$url = eyes_login_url( $redirect ? $redirect : '' );

	if ( $force_reauth ) {
		$url = add_query_arg( 'reauth', '1', $url );
	}

	return $url;
}
add_filter( 'login_url', 'eyes_front_login_url', 10, 3 );

/**
 * Keep the report page reachable for guests (common membership / RSA plugins).
 */
function eyes_report_problem_public_access() {
	if ( ! eyes_is_report_problem_page() ) {
		return;
	}

	remove_action( 'template_redirect', 'auth_redirect' );
	remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );
}
add_action( 'template_redirect', 'eyes_report_problem_public_access', 0 );

/**
 * Allow guests to view the report page when a site-wide login wall is active.
 *
 * @param bool $bypass Whether to bypass the restriction.
 */
function eyes_report_problem_rsa_bypass( $bypass ) {
	if ( eyes_is_report_problem_page() ) {
		return true;
	}

	return $bypass;
}
add_filter( 'restricted_site_access_bypass', 'eyes_report_problem_rsa_bypass' );

/**
 * @param bool $can_view Whether the post can be viewed.
 */
function eyes_report_problem_members_access( $can_view ) {
	if ( eyes_is_report_problem_page() ) {
		return true;
	}

	return $can_view;
}
add_filter( 'members_can_current_user_view_post', 'eyes_report_problem_members_access' );

/**
 * Process report problem form submission.
 *
 * @return true|WP_Error
 */
function eyes_process_report_problem_form() {
	if ( empty( $_POST['eyes_report_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eyes_report_nonce'] ) ), 'eyes_report_problem' ) ) {
		return new WP_Error( 'eyes_report_nonce', __( 'Sessão expirada. Atualize a página e tente novamente.', 'eyes-child' ) );
	}

	$title = isset( $_POST['report_title'] ) ? sanitize_text_field( wp_unslash( $_POST['report_title'] ) ) : '';
	$body  = isset( $_POST['report_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['report_description'] ) ) : '';

	if ( '' === $title ) {
		return new WP_Error( 'eyes_report_title', __( 'Indique o título do problema.', 'eyes-child' ) );
	}

	if ( '' === $body ) {
		return new WP_Error( 'eyes_report_description', __( 'Descreva o problema.', 'eyes-child' ) );
	}

	$user = wp_get_current_user();
	$to   = get_option( 'admin_email' );

	$acf_recipient = eyes_report_acf_text( 'report_recipient_email', '' );

	if ( is_email( $acf_recipient ) ) {
		$to = $acf_recipient;
	}
	$subject = sprintf(
		/* translators: %s: problem title */
		__( '[Solvian Eyes] Reportar problema: %s', 'eyes-child' ),
		$title
	);

	$message_lines = array(
		__( 'Novo reporte de problema na plataforma Solvian Eyes.', 'eyes-child' ),
		'',
		sprintf( '%s: %s', __( 'Título', 'eyes-child' ), $title ),
		sprintf( '%s:', __( 'Descrição', 'eyes-child' ) ),
		$body,
		'',
	);

	if ( $user->exists() ) {
		$message_lines[] = sprintf( '%s: %s', __( 'Utilizador', 'eyes-child' ), $user->display_name );
		$message_lines[] = sprintf( '%s: %s', __( 'Email', 'eyes-child' ), $user->user_email );
	} else {
		$message_lines[] = __( 'Enviado por visitante (sem sessão iniciada).', 'eyes-child' );
	}

	$message_lines[] = sprintf( '%s: %s', __( 'URL', 'eyes-child' ), isset( $_POST['report_page_url'] ) ? esc_url_raw( wp_unslash( $_POST['report_page_url'] ) ) : eyes_report_problem_url() );

	$sent = wp_mail( $to, $subject, implode( "\n", $message_lines ) );

	if ( ! $sent ) {
		return new WP_Error( 'eyes_report_mail', __( 'Não foi possível enviar o reporte. Tente novamente mais tarde.', 'eyes-child' ) );
	}

	return true;
}
