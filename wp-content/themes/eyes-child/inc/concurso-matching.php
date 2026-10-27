<?php
/**
 * Profile-based concurso matching for the Recomendadas dashboard tab (region + PME only).
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Minimum match score (0-100) to appear in Recomendadas.
 */
function eyes_get_match_minimum_score() {
	return (int) apply_filters( 'eyes_match_minimum_score', 40 );
}

/**
 * PME enquadramento options for profile forms and matching.
 *
 * @return array<string, string> slug => label
 */
function eyes_get_pme_enquadramento_options() {
	return array(
		'micro-empresa'   => __( 'Micro-empresa', 'eyes-child' ),
		'pequena-empresa' => __( 'Pequena empresa', 'eyes-child' ),
		'media-empresa'   => __( 'Média empresa', 'eyes-child' ),
	);
}

/**
 * Seed enquadramento_pme taxonomy terms.
 */
function eyes_seed_enquadramento_pme_terms() {
	if ( ! taxonomy_exists( 'enquadramento_pme' ) ) {
		return;
	}

	$seed_version = '2026-06-09';
	if ( get_option( 'eyes_enquadramento_pme_terms_version' ) === $seed_version ) {
		return;
	}

	$legacy_map = array(
		'micro-empresas'    => 'micro-empresa',
		'pequenas-empresas' => 'pequena-empresa',
		'media-empresas'    => 'media-empresa',
	);

	foreach ( $legacy_map as $from_slug => $to_slug ) {
		if ( function_exists( 'eyes_migrate_concurso_term_assignments' ) ) {
			eyes_migrate_concurso_term_assignments( 'enquadramento_pme', $from_slug, $to_slug );
		}
	}

	$options     = eyes_get_pme_enquadramento_options();
	$valid_slugs = array_keys( $options );

	foreach ( $options as $slug => $label ) {
		$existing = term_exists( $slug, 'enquadramento_pme' );

		if ( $existing ) {
			wp_update_term(
				(int) $existing['term_id'],
				'enquadramento_pme',
				array(
					'name' => $label,
					'slug' => $slug,
				)
			);
			continue;
		}

		wp_insert_term(
			$label,
			'enquadramento_pme',
			array(
				'slug' => $slug,
			)
		);
	}

	if ( function_exists( 'eyes_delete_invalid_concurso_terms' ) ) {
		eyes_delete_invalid_concurso_terms( 'enquadramento_pme', $valid_slugs );
	}

	update_option( 'eyes_enquadramento_pme_terms_version', $seed_version, false );
}
add_action( 'init', 'eyes_seed_enquadramento_pme_terms', 20 );

/**
 * Official regiao taxonomy terms for filters, matching, and profiles.
 *
 * @return array<string, string> slug => label
 */
function eyes_get_regiao_terms() {
	return array(
		'continental-norte'  => __( 'Continental - Norte', 'eyes-child' ),
		'continental-centro' => __( 'Continental - Centro', 'eyes-child' ),
		'continental-sul'    => __( 'Continental - Sul', 'eyes-child' ),
		'insular-acores'     => __( 'Insular - Açores', 'eyes-child' ),
		'insular-madeira'    => __( 'Insular - Madeira', 'eyes-child' ),
	);
}

/**
 * Seed regiao taxonomy terms used by matching.
 */
function eyes_seed_regiao_terms() {
	if ( ! taxonomy_exists( 'regiao' ) ) {
		return;
	}

	$seed_version = '2026-06-11';
	if ( get_option( 'eyes_regiao_terms_version' ) === $seed_version ) {
		return;
	}

	$legacy_map = array(
		'norte-continental'     => 'continental-norte',
		'norte'                 => 'continental-norte',
		'centro'                => 'continental-centro',
		'lisboa-e-vale-do-tejo' => 'continental-centro',
		'lisboa'                => 'continental-centro',
		'sul'                   => 'continental-sul',
		'acores'                => 'insular-acores',
		'madeira'               => 'insular-madeira',
	);

	foreach ( $legacy_map as $from_slug => $to_slug ) {
		if ( function_exists( 'eyes_migrate_concurso_term_assignments' ) ) {
			eyes_migrate_concurso_term_assignments( 'regiao', $from_slug, $to_slug );
		}
	}

	$regions = eyes_get_regiao_terms();

	foreach ( $regions as $slug => $label ) {
		$existing = term_exists( $slug, 'regiao' );

		if ( $existing ) {
			wp_update_term(
				(int) $existing['term_id'],
				'regiao',
				array(
					'name' => $label,
					'slug' => $slug,
				)
			);
			continue;
		}

		wp_insert_term(
			$label,
			'regiao',
			array(
				'slug' => $slug,
			)
		);
	}

	$valid_slugs = array_keys( $regions );

	if ( function_exists( 'eyes_delete_invalid_concurso_terms' ) ) {
		eyes_delete_invalid_concurso_terms( 'regiao', $valid_slugs );
	}

	update_option( 'eyes_regiao_terms_version', $seed_version, false );
}
add_action( 'init', 'eyes_seed_regiao_terms', 20 );

/**
 * Region slug aliases for flexible matching.
 *
 * @param string $regiao User region label.
 * @return string[]
 */
function eyes_get_region_match_slugs( $regiao ) {
	$regiao = trim( (string) $regiao );

	$map = array(
		'Continental - Norte'   => array( 'continental-norte' ),
		'Continental - Centro'  => array( 'continental-centro' ),
		'Continental - Sul'     => array( 'continental-sul' ),
		'Insular - Açores'      => array( 'insular-acores' ),
		'Insular - Madeira'     => array( 'insular-madeira' ),
		'Norte Continental'     => array( 'continental-norte' ),
		'Lisboa e Vale do Tejo' => array( 'continental-centro' ),
		'Centro'                => array( 'continental-centro' ),
		'Sul'                   => array( 'continental-sul' ),
	);

	if ( isset( $map[ $regiao ] ) ) {
		return $map[ $regiao ];
	}

	return array( sanitize_title( $regiao ) );
}

/**
 * Normalized user match profile.
 *
 * @param int $user_id User ID.
 * @return array{cae:string,regiao:string,pme:string,sector:string}
 */
function eyes_get_user_match_profile( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	return array(
		'cae'    => trim( (string) get_user_meta( $user_id, 'eyes_cae', true ) ),
		'regiao' => trim( (string) get_user_meta( $user_id, 'eyes_regiao', true ) ),
		'pme'    => trim( (string) get_user_meta( $user_id, 'eyes_enquadramento_pme', true ) ),
		'sector' => trim( (string) get_user_meta( $user_id, 'eyes_setor', true ) ),
	);
}

/**
 * Whether the user profile has enough data for matching.
 *
 * @param array<string, string>|null $profile Optional profile array.
 * @param int                          $user_id User ID when profile omitted.
 */
function eyes_user_profile_ready_for_matching( $profile = null, $user_id = 0 ) {
	if ( null === $profile ) {
		$profile = eyes_get_user_match_profile( $user_id );
	}

	return '' !== $profile['regiao'] && '' !== $profile['pme'];
}

/**
 * Term slugs assigned to a concurso.
 *
 * @param int    $post_id  Post ID.
 * @param string $taxonomy Taxonomy slug.
 * @return string[]
 */
function eyes_get_concurso_term_slugs( $post_id, $taxonomy ) {
	$terms = wp_get_object_terms(
		(int) $post_id,
		$taxonomy,
		array(
			'fields' => 'slugs',
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	return array_values( array_filter( array_map( 'strval', $terms ) ) );
}

/**
 * Score a single concurso against a user profile (region + PME only).
 *
 * @param int                  $post_id Concurso ID.
 * @param array<string,string> $profile User match profile.
 */
function eyes_score_concurso_for_user( $post_id, $profile ) {
	$post_id = (int) $post_id;
	$score   = 0;

	$region_terms = eyes_get_concurso_term_slugs( $post_id, 'regiao' );
	$user_regions = eyes_get_region_match_slugs( $profile['regiao'] );
	$pme_terms    = eyes_get_concurso_term_slugs( $post_id, 'enquadramento_pme' );
	$user_pme     = sanitize_title( $profile['pme'] );

	if ( empty( $region_terms ) ) {
		$score += 30;
	} elseif ( array_intersect( $user_regions, $region_terms ) ) {
		$score += 50;
	}

	if ( empty( $pme_terms ) ) {
		$score += 30;
	} elseif ( in_array( $user_pme, $pme_terms, true ) ) {
		$score += 50;
	}

	return min( 100, $score );
}

/**
 * Match breakdown for a concurso vs user profile (region + PME only).
 *
 * @param int                  $post_id Concurso ID.
 * @param array<string,string> $profile User match profile.
 * @return array{score:int,region_status:string,pme_status:string,user_regiao:string,user_pme_label:string}
 */
function eyes_get_concurso_match_breakdown( $post_id, $profile ) {
	$post_id      = (int) $post_id;
	$region_terms = eyes_get_concurso_term_slugs( $post_id, 'regiao' );
	$user_regions = eyes_get_region_match_slugs( $profile['regiao'] );
	$pme_terms    = eyes_get_concurso_term_slugs( $post_id, 'enquadramento_pme' );
	$user_pme     = sanitize_title( $profile['pme'] );

	$region_status = 'no_match';
	if ( empty( $region_terms ) ) {
		$region_status = 'national';
	} elseif ( array_intersect( $user_regions, $region_terms ) ) {
		$region_status = 'match';
	}

	$pme_status = 'no_match';
	if ( empty( $pme_terms ) ) {
		$pme_status = 'open';
	} elseif ( in_array( $user_pme, $pme_terms, true ) ) {
		$pme_status = 'match';
	}

	$pme_options = eyes_get_pme_enquadramento_options();
	$user_pme_label = $pme_options[ $profile['pme'] ] ?? $profile['pme'];

	return array(
		'score'          => eyes_score_concurso_for_user( $post_id, $profile ),
		'region_status'  => $region_status,
		'pme_status'     => $pme_status,
		'user_regiao'    => $profile['regiao'],
		'user_pme_label' => $user_pme_label,
	);
}

/**
 * Deadline timestamp for sorting (0 when unknown).
 *
 * @param int $post_id Post ID.
 */
function eyes_get_concurso_deadline_timestamp( $post_id ) {
	if ( ! function_exists( 'eyes_get_concurso_meta_value' ) ) {
		return 0;
	}

	$raw = eyes_get_concurso_meta_value( (int) $post_id, 'concurso_deadline' );

	if ( '' === $raw ) {
		return PHP_INT_MAX;
	}

	if ( 8 === strlen( $raw ) && ctype_digit( $raw ) ) {
		$dt = DateTime::createFromFormat( 'Ymd', $raw );
		return $dt ? $dt->getTimestamp() : PHP_INT_MAX;
	}

	if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ) {
		$dt = DateTime::createFromFormat( 'Y-m-d', $raw );
		return $dt ? $dt->getTimestamp() : PHP_INT_MAX;
	}

	if ( preg_match( '/^\d{1,2}\/\d{1,2}\/\d{4}$/', $raw ) ) {
		$dt = DateTime::createFromFormat( 'd/m/Y', $raw );
		if ( ! $dt ) {
			$dt = DateTime::createFromFormat( 'm/d/Y', $raw );
		}
		return $dt ? $dt->getTimestamp() : PHP_INT_MAX;
	}

	$timestamp = strtotime( $raw );

	return $timestamp ? $timestamp : PHP_INT_MAX;
}

/**
 * Recommended concurso IDs for a user, sorted by score then deadline.
 *
 * @param int      $user_id User ID.
 * @param int|null $limit   Max results; 0 or null returns all matches (Free and Pro).
 * @return int[]
 */
function eyes_get_recommended_concurso_ids( $user_id = 0, $limit = null ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	if ( $user_id <= 0 ) {
		return array();
	}

	$profile = eyes_get_user_match_profile( $user_id );

	if ( ! eyes_user_profile_ready_for_matching( $profile ) ) {
		return array();
	}

	if ( null === $limit ) {
		$limit = 0;
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'concurso',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => true,
			'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => 'concurso_status',
					'value'   => 'abertos',
					'compare' => '=',
				),
			),
		)
	);

	$scored = array();

	foreach ( $query->posts as $post_id ) {
		$post_score = eyes_score_concurso_for_user( (int) $post_id, $profile );

		if ( $post_score >= eyes_get_match_minimum_score() ) {
			$scored[ (int) $post_id ] = $post_score;
		}
	}

	if ( empty( $scored ) ) {
		return array();
	}

	$ids = array_keys( $scored );

	usort(
		$ids,
		static function ( $a, $b ) use ( $scored ) {
			if ( $scored[ $a ] !== $scored[ $b ] ) {
				return $scored[ $b ] <=> $scored[ $a ];
			}

			$deadline_a = eyes_get_concurso_deadline_timestamp( $a );
			$deadline_b = eyes_get_concurso_deadline_timestamp( $b );

			if ( $deadline_a !== $deadline_b ) {
				return $deadline_a <=> $deadline_b;
			}

			return $b <=> $a;
		}
	);

	if ( $limit > 0 ) {
		$ids = array_slice( $ids, 0, $limit );
	}

	return $ids;
}

/**
 * All recommended IDs for display/sorting (same as full recommendation list).
 *
 * @param int $user_id User ID.
 * @return int[]
 */
function eyes_get_recommended_display_ids( $user_id = 0 ) {
	return eyes_get_recommended_concurso_ids( $user_id, 0 );
}

/**
 * Prime recommended IDs cache before rendering a card loop.
 *
 * @param int $user_id User ID.
 * @return int[]
 */
function eyes_prime_recommended_display_cache( $user_id = 0 ) {
	static $cache = array();

	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	if ( $user_id <= 0 ) {
		return array();
	}

	if ( ! isset( $cache[ $user_id ] ) ) {
		$cache[ $user_id ] = eyes_get_recommended_display_ids( $user_id );
	}

	return $cache[ $user_id ];
}

/**
 * Whether a concurso is recommended for the current user.
 *
 * @param int $post_id Post ID.
 * @param int $user_id User ID.
 */
function eyes_is_recommended_concurso( $post_id, $user_id = 0 ) {
	if ( ! is_user_logged_in() && ! $user_id ) {
		return false;
	}

	return in_array( (int) $post_id, eyes_prime_recommended_display_cache( $user_id ), true );
}

/**
 * Pin recommended concursos first in list queries (todos / territorio tabs).
 *
 * @param array<string, mixed> $args    Query args.
 * @param int                  $user_id User ID.
 * @return array<string, mixed>
 */
function eyes_apply_recommended_first_query_args( $args, $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	if ( $user_id <= 0 || ! eyes_user_profile_ready_for_matching( null, $user_id ) ) {
		return $args;
	}

	$recommended = eyes_get_recommended_display_ids( $user_id );

	if ( empty( $recommended ) ) {
		return $args;
	}

	$args['eyes_recommended_first'] = $recommended;

	return $args;
}

/**
 * SQL order: recommended concursos first (by match score order), then the rest.
 *
 * @param string   $orderby ORDER BY clause.
 * @param WP_Query $query   Query instance.
 */
function eyes_posts_orderby_recommended_first( $orderby, $query ) {
	$recommended = $query->get( 'eyes_recommended_first' );

	if ( empty( $recommended ) || ! is_array( $recommended ) ) {
		return $orderby;
	}

	global $wpdb;

	$id_list = implode( ',', array_map( 'absint', $recommended ) );

	if ( '' === $id_list ) {
		return $orderby;
	}

	$recommended_order = "CASE WHEN {$wpdb->posts}.ID IN ({$id_list}) THEN 0 ELSE 1 END ASC";
	$recommended_order .= ", CASE WHEN {$wpdb->posts}.ID IN ({$id_list}) THEN FIELD({$wpdb->posts}.ID, {$id_list}) ELSE 999999 END ASC";

	return $recommended_order . ( $orderby ? ', ' . $orderby : '' );
}
add_filter( 'posts_orderby', 'eyes_posts_orderby_recommended_first', 10, 2 );

/**
 * Count open concursos assigned to one or more regiao terms.
 *
 * Includes concursos with no region when $include_national is true.
 *
 * @param string[]             $region_slugs     Regiao term slugs.
 * @param bool                 $include_national Include open concursos without a regiao term.
 * @param array<string, mixed> $filter_params    Optional archive filter params.
 */
function eyes_count_open_concursos_for_regioes( $region_slugs, $include_national = false, $filter_params = array() ) {
	$region_slugs = array_values( array_filter( array_map( 'sanitize_title', (array) $region_slugs ) ) );

	if ( empty( $region_slugs ) && ! $include_national ) {
		return 0;
	}

	if ( ! empty( $filter_params ) && function_exists( 'eyes_build_concurso_query_args' ) ) {
		$count_params = $filter_params;
		unset( $count_params['dashboard_tab'] );
		$args         = eyes_build_concurso_query_args( $count_params );
	} else {
		$args = array(
			'post_type'      => 'concurso',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		);
	}

	$args['posts_per_page'] = -1;
	$args['fields']         = 'ids';
	$args['paged']          = 1;
	unset( $args['eyes_recommended_first'] );

	if ( empty( $args['meta_query'] ) || ! is_array( $args['meta_query'] ) ) {
		$args['meta_query'] = array( 'relation' => 'AND' ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	}

	$has_status = false;
	foreach ( $args['meta_query'] as $clause ) {
		if ( is_array( $clause ) && isset( $clause['key'] ) && 'concurso_status' === $clause['key'] ) {
			$has_status = true;
			break;
		}
	}

	if ( ! $has_status ) {
		$args['meta_query'][] = array(
			'key'     => 'concurso_status',
			'value'   => 'abertos',
			'compare' => '=',
		);
	}

	if ( ! empty( $region_slugs ) ) {
		$region_query = array(
			'taxonomy' => 'regiao',
			'field'    => 'slug',
			'terms'    => $region_slugs,
			'operator' => 'IN',
		);

		if ( ! empty( $args['tax_query'] ) && is_array( $args['tax_query'] ) ) {
			$args['tax_query'][] = $region_query;
		} else {
			$args['tax_query'] = array( $region_query ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}
	} elseif ( $include_national ) {
		$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => 'regiao',
				'operator' => 'NOT EXISTS',
			),
		);
	}

	$query = new WP_Query( $args );

	return count( $query->posts );
}

/**
 * Dashboard empty-state context for AJAX and initial render.
 *
 * @param string $tab     Dashboard tab slug.
 * @param int    $user_id User ID.
 * @return array{type:string,title:string,text:string,cta_label:string,cta_url:string}
 */
function eyes_get_dashboard_empty_state( $tab, $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	$profile = eyes_edit_profile_url();

	$defaults = array(
		'type'       => 'generic',
		'title'      => __( 'Comece hoje a aproveitar as oportunidades disponíveis', 'eyes-child' ),
		'text'       => __( 'Explore as nossas recomendações e encontre oportunidades únicas para o perfil da sua empresa.', 'eyes-child' ),
		'cta_label'  => __( 'Explorar recomendadas', 'eyes-child' ),
		'cta_url'    => '#',
	);

	if ( 'recomendadas' !== $tab ) {
		if ( 'guardadas' === $tab ) {
			return array_merge(
				$defaults,
				array(
					'type'  => 'no_saved',
					'title' => __( 'Ainda não guardou nenhum concurso', 'eyes-child' ),
					'text'  => __( 'Use o ícone de estrela nos concursos para guardar oportunidades para mais tarde.', 'eyes-child' ),
				)
			);
		}

		if ( 'candidaturas' === $tab ) {
			return array_merge(
				$defaults,
				array(
					'type'  => 'no_applied',
					'title' => __( 'Ainda não registou candidaturas', 'eyes-child' ),
					'text'  => __( 'Quando candidatar a um concurso, ele aparecerá aqui para acompanhar.', 'eyes-child' ),
				)
			);
		}

		return $defaults;
	}

	if ( ! eyes_user_profile_ready_for_matching( null, $user_id ) ) {
		return array(
			'type'      => 'incomplete_profile',
			'title'     => __( 'Complete o seu perfil para ver recomendações', 'eyes-child' ),
			'text'      => __( 'Indique localidade e enquadramento PME para receber oportunidades personalizadas.', 'eyes-child' ),
			'cta_label' => __( 'Editar perfil', 'eyes-child' ),
			'cta_url'   => $profile,
		);
	}

	return array(
		'type'      => 'no_matches',
		'title'     => __( 'Sem recomendações por agora', 'eyes-child' ),
		'text'      => __( 'Não encontrámos concursos abertos que correspondam ao perfil da sua empresa. Volte em breve ou explore todos os concursos.', 'eyes-child' ),
		'cta_label' => __( 'Ver todos os concursos', 'eyes-child' ),
		'cta_url'   => add_query_arg( 'dashboard_tab', 'todos', get_post_type_archive_link( 'concurso' ) ?: home_url( '/concurso/' ) ),
	);
}
