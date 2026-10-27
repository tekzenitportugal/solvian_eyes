<?php
/**
 * Concurso detail page helpers (Free vs Pro content gating).
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current user may view Pro-only concurso detail sections.
 *
 * @param int $user_id User ID.
 */
function eyes_user_can_view_concurso_pro_content( $user_id = 0 ) {
	if ( ! function_exists( 'eyes_user_has_pro_plan' ) ) {
		return false;
	}

	return eyes_user_has_pro_plan( $user_id );
}

/**
 * Pro-only ACF field names (never expose to Free users).
 *
 * @return string[]
 */
function eyes_get_concurso_pro_field_names() {
	return array(
		'concurso_legal_intro',
		'concurso_legal_points',
		'concurso_documents',
		'concurso_application_forms',
		'concurso_legislation',
	);
}

/**
 * Strip Pro ACF meta from REST responses for non-Pro users.
 *
 * @param WP_REST_Response $response Response object.
 * @param WP_Post          $post     Post object.
 */
function eyes_rest_strip_concurso_pro_fields( $response, $post ) {
	if ( 'concurso' !== $post->post_type || eyes_user_can_view_concurso_pro_content() ) {
		return $response;
	}

	$data = $response->get_data();

	if ( isset( $data['acf'] ) && is_array( $data['acf'] ) ) {
		foreach ( eyes_get_concurso_pro_field_names() as $field_name ) {
			unset( $data['acf'][ $field_name ] );
		}
		$response->set_data( $data );
	}

	return $response;
}
add_filter( 'rest_prepare_concurso', 'eyes_rest_strip_concurso_pro_fields', 10, 2 );

/**
 * Resolve an ACF file field to a public URL.
 *
 * @param mixed $file ACF file value.
 */
function eyes_get_acf_file_url( $file ) {
	if ( is_array( $file ) && ! empty( $file['url'] ) ) {
		return (string) $file['url'];
	}

	if ( is_numeric( $file ) ) {
		$url = wp_get_attachment_url( (int) $file );

		return $url ? $url : '';
	}

	return '';
}

/**
 * Resolve an ACF file field to a display label.
 *
 * @param mixed  $file     ACF file value.
 * @param string $fallback Fallback label.
 */
function eyes_get_acf_file_label( $file, $fallback = '' ) {
	if ( is_array( $file ) && ! empty( $file['filename'] ) ) {
		return (string) $file['filename'];
	}

	if ( is_array( $file ) && ! empty( $file['title'] ) ) {
		return (string) $file['title'];
	}

	if ( is_numeric( $file ) ) {
		$path = get_attached_file( (int) $file );

		if ( $path ) {
			return basename( $path );
		}
	}

	return $fallback;
}

/**
 * Pro-only field reader.
 *
 * @param string $field   ACF field name.
 * @param int    $post_id Post ID.
 */
function eyes_get_concurso_pro_field( $field, $post_id = 0 ) {
	if ( ! eyes_user_can_view_concurso_pro_content() ) {
		return null;
	}

	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( ! function_exists( 'get_field' ) || $post_id <= 0 ) {
		return null;
	}

	return get_field( $field, $post_id );
}

/**
 * Whether a concurso has any Pro detail content stored.
 *
 * @param int $post_id Post ID.
 */
function eyes_concurso_has_pro_content( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( $post_id <= 0 || ! function_exists( 'get_field' ) ) {
		return false;
	}

	foreach ( eyes_get_concurso_pro_field_names() as $field_name ) {
		$value = get_field( $field_name, $post_id );

		if ( empty( $value ) ) {
			continue;
		}

		if ( is_array( $value ) ) {
			return true;
		}

		if ( is_string( $value ) && '' !== trim( $value ) ) {
			return true;
		}
	}

	return false;
}
