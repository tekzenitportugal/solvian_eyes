<?php
/**
 * Shared helpers for ACF fields, assets, header settings, and links.
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Child theme asset URL (always resolves to eyes-child/assets/).
 *
 * @param string $path Path relative to assets/, e.g. "images/logo-nav.png".
 */
function eyes_asset_uri( $path ) {
	return trailingslashit( get_stylesheet_directory_uri() ) . 'assets/' . ltrim( $path, '/' );
}

/**
 * Normalize an ACF image field value to a URL.
 *
 * @param mixed $value Raw ACF image value.
 */
function eyes_acf_resolve_image( $value ) {
	if ( is_array( $value ) && ! empty( $value['url'] ) ) {
		return $value['url'];
	}

	if ( is_numeric( $value ) ) {
		$url = wp_get_attachment_image_url( (int) $value, 'full' );
		return $url ? $url : '';
	}

	if ( is_string( $value ) && '' !== trim( $value ) ) {
		return $value;
	}

	return '';
}

/**
 * Read an ACF text field, optionally from a specific post ID.
 *
 * @param string      $field_name Field name.
 * @param string      $fallback   Fallback when empty.
 * @param int|null    $post_id    Post ID, or null for current post.
 */
function eyes_acf_text( $field_name, $fallback = '', $post_id = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $fallback;
	}

	$value = get_field( $field_name, $post_id );

	if ( is_string( $value ) && '' !== trim( $value ) ) {
		return $value;
	}

	return $fallback;
}

/**
 * Read an ACF link field (Link or URL type).
 *
 * @param string   $field_name     Field name.
 * @param string   $fallback_url   Default URL.
 * @param string   $fallback_title Default title.
 * @param int|null $post_id        Post ID, or null for current post.
 * @return array{url:string,title:string,target:string}
 */
function eyes_acf_link( $field_name, $fallback_url = '#', $fallback_title = '', $post_id = null ) {
	$link = array(
		'url'    => $fallback_url,
		'title'  => $fallback_title,
		'target' => '',
	);

	if ( ! function_exists( 'get_field' ) ) {
		return $link;
	}

	$value = get_field( $field_name, $post_id );

	if ( is_array( $value ) && ! empty( $value['url'] ) ) {
		$link['url']    = $value['url'];
		$link['title']  = ! empty( $value['title'] ) ? $value['title'] : $fallback_title;
		$link['target'] = ! empty( $value['target'] ) ? $value['target'] : '';
	} elseif ( is_string( $value ) && '' !== trim( $value ) ) {
		$link['url'] = $value;
	}

	return $link;
}

/**
 * Read an ACF image field as a URL.
 *
 * @param string   $field_name Field name.
 * @param int|null $post_id    Post ID, or null for current post.
 */
function eyes_acf_image_url( $field_name, $post_id = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}

	return eyes_acf_resolve_image( get_field( $field_name, $post_id ) );
}

/**
 * Build safe anchor attributes from a link array.
 *
 * @param array{url?:string,title?:string,target?:string} $link Link data.
 */
function eyes_acf_link_attrs( $link ) {
	$url = ! empty( $link['url'] ) ? $link['url'] : '#';
	$attrs = 'href="' . esc_url( $url ) . '"';

	if ( ! empty( $link['target'] ) ) {
		$attrs .= ' target="' . esc_attr( $link['target'] ) . '" rel="noopener noreferrer"';
	}

	return $attrs;
}

/**
 * Page ID for header-settings (WPML-aware).
 */
function eyes_header_settings_id() {
	$page = get_page_by_path( 'header-settings' );

	if ( ! $page ) {
		return 0;
	}

	$page_id = (int) $page->ID;

	if ( has_filter( 'wpml_object_id' ) ) {
		$page_id = (int) apply_filters( 'wpml_object_id', $page_id, 'page', true );
	}

	return $page_id;
}

/**
 * Header ACF text from the header-settings page.
 */
function eyes_header_text( $field_name ) {
	return eyes_acf_text( $field_name, '', eyes_header_settings_id() );
}

/**
 * Header ACF image from the header-settings page.
 */
function eyes_header_image_url( $field_name ) {
	return eyes_acf_image_url( $field_name, eyes_header_settings_id() );
}

/**
 * Header ACF link from the header-settings page.
 */
function eyes_header_link( $field_name ) {
	return eyes_acf_link( $field_name, '', '', eyes_header_settings_id() );
}

/**
 * Active WPML languages for the header switcher.
 */
function eyes_header_languages() {
	if ( function_exists( 'icl_get_languages' ) ) {
		return icl_get_languages( 'skip_missing=0&orderby=code' );
	}

	if ( has_filter( 'wpml_active_languages' ) ) {
		$languages = apply_filters(
			'wpml_active_languages',
			null,
			array(
				'skip_missing' => 0,
				'orderby'      => 'code',
			)
		);

		return is_array( $languages ) ? $languages : array();
	}

	return array();
}

/**
 * Display name for the logged-in user.
 *
 * @param WP_User $user User object.
 */
function eyes_current_user_full_name( $user ) {
	$first_name = trim( (string) get_user_meta( $user->ID, 'first_name', true ) );
	$last_name  = trim( (string) get_user_meta( $user->ID, 'last_name', true ) );
	$name       = trim( $first_name . ' ' . $last_name );

	if ( '' !== $name ) {
		return $name;
	}

	return (string) $user->display_name;
}

/**
 * Icon URL for concursos card meta rows (replace SVGs in assets/images when design exports are ready).
 *
 * @param string $type One of: deadline, location, company.
 */
function eyes_concursos_meta_icon_uri( $type ) {
	$icons = array(
		'deadline'  => 'images/concursos-meta-calendar.svg',
		'location'  => 'images/concursos-meta-location.svg',
		'company'   => 'images/concursos-meta-company.svg',
	);

	$key = array_key_exists( $type, $icons ) ? $type : 'deadline';

	return eyes_asset_uri( $icons[ $key ] );
}

/**
 * Format a Concurso deadline ACF value for display.
 *
 * @param mixed $raw Raw ACF date value.
 */
function eyes_format_concurso_deadline( $raw ) {
	if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
		return '';
	}

	$raw = trim( $raw );

	if ( preg_match( '/^\d{8}$/', $raw ) ) {
		$date = DateTime::createFromFormat( 'Ymd', $raw );
		return $date ? $date->format( 'd/m/Y' ) : $raw;
	}

	if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ) {
		$date = DateTime::createFromFormat( 'Y-m-d', $raw );
		return $date ? $date->format( 'd/m/Y' ) : $raw;
	}

	if ( preg_match( '/^\d{1,2}\/\d{1,2}\/\d{4}$/', $raw ) ) {
		$date = DateTime::createFromFormat( 'd/m/Y', $raw );
		if ( $date ) {
			return $date->format( 'd/m/Y' );
		}
		$date = DateTime::createFromFormat( 'm/d/Y', $raw );
		return $date ? $date->format( 'd/m/Y' ) : $raw;
	}

	return $raw;
}

/**
 * Default-language post ID for WPML translations (falls back to the given ID).
 *
 * @param int $post_id Post ID.
 */
function eyes_get_wpml_source_post_id( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 ) {
		return 0;
	}

	if ( ! has_filter( 'wpml_object_id' ) ) {
		return $post_id;
	}

	$post_type = get_post_type( $post_id );
	if ( ! $post_type ) {
		return $post_id;
	}

	$default_lang = apply_filters( 'wpml_default_language', null );
	$source_id    = apply_filters( 'wpml_object_id', $post_id, $post_type, true, $default_lang );

	return $source_id ? (int) $source_id : $post_id;
}

/**
 * Comma-separated term names for a post taxonomy (WPML-safe).
 *
 * @param int    $post_id  Post ID.
 * @param string $taxonomy Taxonomy slug.
 */
function eyes_get_taxonomy_term_list( $post_id, $taxonomy ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 || ! taxonomy_exists( $taxonomy ) ) {
		return '';
	}

	$ids_to_try = array( $post_id );
	$source_id  = eyes_get_wpml_source_post_id( $post_id );

	if ( $source_id && $source_id !== $post_id ) {
		$ids_to_try[] = $source_id;
	}

	foreach ( $ids_to_try as $try_id ) {
		$terms = wp_get_object_terms(
			$try_id,
			$taxonomy,
			array(
				'orderby' => 'name',
				'order'   => 'ASC',
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			continue;
		}

		$names = array();

		foreach ( $terms as $term ) {
			if ( ! empty( $term->name ) ) {
				$names[] = $term->name;
			}
		}

		if ( $names ) {
			return implode( ', ', $names );
		}
	}

	return '';
}

/**
 * Region label for concurso detail hero (avoids listing every region term).
 *
 * @param int $post_id Post ID.
 */
function eyes_get_concurso_detail_region_label( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 ) {
		return '';
	}

	$ids_to_try = array( $post_id );
	$source_id  = eyes_get_wpml_source_post_id( $post_id );

	if ( $source_id && $source_id !== $post_id ) {
		$ids_to_try[] = $source_id;
	}

	foreach ( $ids_to_try as $try_id ) {
		$public_terms = wp_get_object_terms( $try_id, 'concursos_publicos', array( 'fields' => 'slugs' ) );

		if ( ! is_wp_error( $public_terms ) && in_array( 'nacional', $public_terms, true ) ) {
			return __( 'Nacional', 'eyes-child' );
		}
	}

	$title_lower = strtolower( (string) get_the_title( $post_id ) );

	if ( ! str_contains( $title_lower, 'internacional' ) && str_contains( $title_lower, 'nacional' ) ) {
		return __( 'Nacional', 'eyes-child' );
	}

	foreach ( $ids_to_try as $try_id ) {
		$regions = wp_get_object_terms( $try_id, 'regiao' );

		if ( is_wp_error( $regions ) || empty( $regions ) ) {
			continue;
		}

		if ( count( $regions ) >= 4 ) {
			return __( 'Nacional', 'eyes-child' );
		}

		break;
	}

	return eyes_get_taxonomy_term_list( $post_id, 'regiao' );
}

/**
 * PME label for concurso detail hero (shows applicable types only).
 *
 * @param int $post_id Post ID.
 */
function eyes_get_concurso_detail_pme_label( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 ) {
		return '';
	}

	$ids_to_try = array( $post_id );
	$source_id  = eyes_get_wpml_source_post_id( $post_id );

	if ( $source_id && $source_id !== $post_id ) {
		$ids_to_try[] = $source_id;
	}

	foreach ( $ids_to_try as $try_id ) {
		$terms = wp_get_object_terms(
			$try_id,
			'enquadramento_pme',
			array(
				'orderby' => 'name',
				'order'   => 'ASC',
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			continue;
		}

		if ( count( $terms ) === 1 && ! empty( $terms[0]->name ) ) {
			return (string) $terms[0]->name;
		}

		break;
	}

	if ( eyes_concurso_is_open_to_all_pme( $post_id ) ) {
		return __( 'Todas as PME', 'eyes-child' );
	}

	foreach ( $ids_to_try as $try_id ) {
		$terms = wp_get_object_terms(
			$try_id,
			'enquadramento_pme',
			array(
				'orderby' => 'name',
				'order'   => 'ASC',
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			continue;
		}

		if ( count( $terms ) >= 3 ) {
			return __( 'Todas as PME', 'eyes-child' );
		}

		break;
	}

	$type_map = array(
		'micro'   => __( 'Micro-empresa', 'eyes-child' ),
		'pequena' => __( 'Pequena empresa', 'eyes-child' ),
		'media'   => __( 'Média empresa', 'eyes-child' ),
	);
	$legacy_type = (string) get_post_meta( $post_id, 'concurso_type', true );

	return $type_map[ $legacy_type ] ?? '';
}

/**
 * Whether a concurso is open to every PME size (no single-size restriction).
 *
 * @param int $post_id Post ID.
 */
function eyes_concurso_is_open_to_all_pme( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 ) {
		return false;
	}

	$ids_to_try = array( $post_id );
	$source_id  = eyes_get_wpml_source_post_id( $post_id );

	if ( $source_id && $source_id !== $post_id ) {
		$ids_to_try[] = $source_id;
	}

	foreach ( $ids_to_try as $try_id ) {
		$public_terms = wp_get_object_terms( $try_id, 'concursos_publicos', array( 'fields' => 'slugs' ) );

		if ( ! is_wp_error( $public_terms ) && in_array( 'nacional', $public_terms, true ) ) {
			return true;
		}
	}

	$title_lower = strtolower( (string) get_the_title( $post_id ) );

	if ( str_contains( $title_lower, 'internacional' ) ) {
		return false;
	}

	return str_contains( $title_lower, 'nacional' );
}

/**
 * Read an ACF/meta value with WPML source fallback.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 */
function eyes_get_concurso_meta_value( $post_id, $key ) {
	$post_id = (int) $post_id;
	$ids     = array( $post_id );
	$source  = eyes_get_wpml_source_post_id( $post_id );

	if ( $source && $source !== $post_id ) {
		$ids[] = $source;
	}

	foreach ( $ids as $try_id ) {
		$value = function_exists( 'get_field' ) ? get_field( $key, $try_id ) : null;

		if ( is_string( $value ) && '' !== trim( $value ) ) {
			return trim( $value );
		}

		if ( is_numeric( $value ) && ! is_float( $value ) ) {
			return (string) $value;
		}

		$meta = get_post_meta( $try_id, $key, true );
		if ( is_string( $meta ) && '' !== trim( $meta ) ) {
			return trim( $meta );
		}
	}

	return '';
}

/**
 * Formatted concurso value for the detail hero.
 *
 * @param int $post_id Post ID.
 */
function eyes_get_concurso_value_display( $post_id ) {
	$value = eyes_get_concurso_meta_value( $post_id, 'concurso_value' );

	if ( '' === $value ) {
		return '';
	}

	return str_replace( '?', '€', $value );
}

/**
 * Financing label for the detail aside card.
 *
 * @param int $post_id Post ID.
 */
function eyes_get_concurso_detail_financing_label( $post_id ) {
	$label = eyes_get_taxonomy_term_list( $post_id, 'financing_type' );

	if ( '' !== $label ) {
		return $label;
	}

	$activity = eyes_get_concurso_meta_value( $post_id, 'concurso_activity' );
	$map      = function_exists( 'eyes_get_financing_types' ) ? eyes_get_financing_types() : array();

	$activity_map = array(
		'incentivos'    => $map['subsidio'] ?? __( 'Subsídio', 'eyes-child' ),
		'financiamento' => $map['emprestimo'] ?? __( 'Empréstimo', 'eyes-child' ),
		'capital_risco' => __( 'Capital de risco', 'eyes-child' ),
		'outros'        => $map['outros'] ?? __( 'Outros', 'eyes-child' ),
	);

	return $activity_map[ $activity ] ?? '';
}

/**
 * Sector label for the detail aside card.
 *
 * @param int $post_id Post ID.
 */
function eyes_get_concurso_detail_sector_label( $post_id ) {
	return eyes_get_taxonomy_term_list( $post_id, 'sector_activity' );
}

/**
 * Audience tags for the detail aside panel.
 *
 * @param int $post_id Post ID.
 * @return string[]
 */
function eyes_get_concurso_detail_audience_tags( $post_id ) {
	$post_id = (int) $post_id;
	$tags    = array();
	$ids     = array( $post_id );
	$source  = eyes_get_wpml_source_post_id( $post_id );

	if ( $source && $source !== $post_id ) {
		$ids[] = $source;
	}

	foreach ( $ids as $try_id ) {
		$terms = wp_get_object_terms(
			$try_id,
			'concurso_audience',
			array(
				'orderby' => 'name',
				'order'   => 'ASC',
			)
		);

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( ! empty( $term->name ) ) {
					$tags[] = $term->name;
				}
			}
			break;
		}
	}

	if ( empty( $tags ) ) {
		$pme_terms = wp_get_object_terms( $post_id, 'enquadramento_pme', array( 'orderby' => 'name' ) );
		if ( ! is_wp_error( $pme_terms ) && ! empty( $pme_terms ) ) {
			$tags[] = __( 'PME', 'eyes-child' );
		}
	}

	$sector = eyes_get_taxonomy_term_list( $post_id, 'sector_activity' );
	if ( '' !== $sector ) {
		foreach ( explode( ', ', $sector ) as $sector_name ) {
			$sector_name = trim( $sector_name );
			if ( '' !== $sector_name ) {
				$tags[] = $sector_name;
			}
		}
	}

	return array_values( array_unique( $tags ) );
}

/**
 * Display placeholder for empty detail values.
 *
 * @param string $value Raw value.
 */
function eyes_concurso_detail_display_value( $value ) {
	$value = is_string( $value ) ? trim( $value ) : '';
	return '' !== $value ? $value : '—';
}

/**
 * Variables used by header.php.
 *
 * @return array<string, mixed>
 */
function eyes_header_get_context() {
	$logo_link = eyes_header_link( 'header_logo_link' );
	if ( empty( $logo_link['url'] ) ) {
		$logo_link['url'] = home_url( '/' );
	}

	$logo = eyes_header_image_url( 'header_logo' );
	if ( ! $logo ) {
		$logo = eyes_asset_uri( 'images/logo-nav.png' );
	}

	$logo_alt = eyes_header_text( 'header_logo_alt' );
	if ( ! $logo_alt ) {
		$logo_alt = get_bloginfo( 'name' );
	}

	$logo_aria = eyes_header_text( 'header_logo_aria_label' );
	if ( ! $logo_aria ) {
		$logo_aria = $logo_alt;
	}

	$login_label = eyes_header_text( 'header_login_label' );
	$login_url   = eyes_header_text( 'header_login_url' );
	$login       = array(
		'url'    => $login_url ? $login_url : eyes_login_url(),
		'title'  => $login_label ? $login_label : __( 'Entrar', 'eyes-child' ),
		'target' => '',
	);
	$login = eyes_header_normalize_auth_link( $login, eyes_login_url(), __( 'Entrar', 'eyes-child' ) );

	$register_label = eyes_header_text( 'header_register_label' );
	$register_url   = eyes_header_text( 'header_register_url' );
	$register       = array(
		'url'    => $register_url ? $register_url : eyes_registo_url(),
		'title'  => $register_label ? $register_label : __( 'Registo Gratuito', 'eyes-child' ),
		'target' => '',
	);
	$register = eyes_header_normalize_auth_link( $register, eyes_registo_url(), __( 'Registo Gratuito', 'eyes-child' ) );

	return array(
		'logo'           => $logo,
		'logo_alt'       => $logo_alt,
		'logo_aria'      => $logo_aria,
		'logo_link'      => $logo_link,
		'nav_1'          => eyes_header_link( 'header_nav_1_link' ),
		'nav_2'          => eyes_header_link( 'header_nav_2_link' ),
		'nav_3'          => eyes_header_link( 'header_nav_3_link' ),
		'login'          => $login,
		'register'       => $register,
		'languages'      => eyes_header_languages(),
		'avatar_uri'     => eyes_asset_uri( 'images/user-avatar.svg' ),
	);
}

/**
 * Ensure header auth buttons use theme login/registo pages, not wp-login.php.
 *
 * @param array  $link     ACF link array.
 * @param string $fallback Fallback URL.
 * @param string $title    Fallback link text.
 * @return array<string, string>
 */
function eyes_header_normalize_auth_link( $link, $fallback, $title ) {
	if ( empty( $link['url'] ) || false !== strpos( $link['url'], 'wp-login.php' ) ) {
		$link['url'] = $fallback;
	}

	if ( empty( $link['title'] ) ) {
		$link['title'] = $title;
	}

	return $link;
}
