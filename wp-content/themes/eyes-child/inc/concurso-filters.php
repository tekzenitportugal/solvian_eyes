<?php
/**
 * Concurso archive filtering (query + AJAX).
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the visitor can use the extended (post-login) filter set.
 */
function eyes_concurso_has_extended_filters() {
	return is_user_logged_in();
}

/**
 * Taxonomy slugs used only after login.
 *
 * @return string[]
 */
function eyes_get_concurso_logged_in_taxonomies() {
	return array(
		'cae_activity',
		'concursos_publicos',
		'beneficios_fiscais',
	);
}

/**
 * Filterable taxonomy slugs on the archive page.
 *
 * @return string[]
 */
function eyes_concurso_filter_taxonomies() {
	$taxonomies = array(
		'regiao',
		'enquadramento_pme',
	);

	if ( eyes_concurso_has_extended_filters() ) {
		$taxonomies = array_merge( $taxonomies, eyes_get_concurso_logged_in_taxonomies() );
	}

	return $taxonomies;
}

/**
 * Archive filter definitions for the UI.
 *
 * Pre-login: Região, Enquadramento PME, Estado de concurso, Financiamento.
 * Post-login: the above plus Atividade (CAE), Concursos públicos, Benefícios fiscais.
 *
 * @param bool|null $extended Whether to include logged-in-only filters.
 * @return array<int, array<string, mixed>>
 */
function eyes_get_concurso_archive_filter_definitions( $extended = null ) {
	if ( null === $extended ) {
		$extended = eyes_concurso_has_extended_filters();
	}

	$filters = array(
		array(
			'type'        => 'taxonomy',
			'key'         => 'regiao',
			'label'       => __( 'Região', 'eyes-child' ),
			'filter_key'  => 'concurso_region',
			'panel_key'   => 'region',
		),
		array(
			'type'        => 'taxonomy',
			'key'         => 'enquadramento_pme',
			'label'       => __( 'Enquadramento PME', 'eyes-child' ),
			'filter_key'  => 'concurso_type',
			'panel_key'   => 'type',
		),
		array(
			'type'        => 'meta',
			'key'         => 'concurso_status',
			'label'       => __( 'Estado de concurso', 'eyes-child' ),
			'filter_key'  => 'concurso_status',
			'panel_key'   => 'status',
			'options'     => eyes_get_concurso_status_labels(),
		),
		array(
			'type'        => 'meta',
			'key'         => 'concurso_activity',
			'label'       => __( 'Financiamento', 'eyes-child' ),
			'filter_key'  => 'concurso_financing',
			'panel_key'   => 'financing',
			'options'     => eyes_get_concurso_activity_labels(),
		),
	);

	if ( ! $extended ) {
		return $filters;
	}

	return eyes_dedupe_filter_definitions(
		array_merge(
			$filters,
			array(
				array(
					'type'       => 'taxonomy',
					'key'        => 'cae_activity',
					'label'      => __( 'Atividade (CAE)', 'eyes-child' ),
					'filter_key' => 'concurso_activity',
					'panel_key'  => 'activity',
				),
				array(
					'type'       => 'taxonomy',
					'key'        => 'concursos_publicos',
					'label'      => __( 'Concursos públicos', 'eyes-child' ),
					'filter_key' => 'concurso_public',
					'panel_key'  => 'public',
				),
				array(
					'type'       => 'taxonomy',
					'key'        => 'beneficios_fiscais',
					'label'      => __( 'Benefícios fiscais', 'eyes-child' ),
					'filter_key' => 'concurso_tax_benefits',
					'panel_key'  => 'tax-benefits',
				),
			)
		)
	);
}

/**
 * Remove duplicate filter definitions by taxonomy/meta key.
 *
 * @param array<int, array<string, mixed>> $filters Filter definitions.
 * @return array<int, array<string, mixed>>
 */
function eyes_dedupe_filter_definitions( $filters ) {
	$seen    = array();
	$unique  = array();

	foreach ( $filters as $definition ) {
		$key = isset( $definition['key'] ) ? (string) $definition['key'] : '';

		if ( '' === $key || isset( $seen[ $key ] ) ) {
			continue;
		}

		$seen[ $key ] = true;
		$unique[]     = $definition;
	}

	return $unique;
}

/**
 * Allowed slugs for curated archive filter taxonomies.
 *
 * @param string $taxonomy Taxonomy slug.
 * @return string[] Empty when the taxonomy is not restricted.
 */
function eyes_get_concurso_filter_term_allowlist( $taxonomy ) {
	switch ( $taxonomy ) {
		case 'regiao':
			return function_exists( 'eyes_get_regiao_terms' )
				? array_keys( eyes_get_regiao_terms() )
				: array(
					'continental-norte',
					'continental-centro',
					'continental-sul',
					'insular-acores',
					'insular-madeira',
				);

		case 'enquadramento_pme':
			return array_keys( eyes_get_pme_enquadramento_options() );

		case 'concursos_publicos':
			return array_keys( eyes_get_concursos_publicos_terms() );

		case 'beneficios_fiscais':
			return array_keys( eyes_get_beneficios_fiscais_terms() );

		default:
			return array();
	}
}

/**
 * Terms for a filter dropdown, excluding legacy/duplicate entries.
 *
 * @param string $taxonomy Taxonomy slug.
 * @return WP_Term[]
 */
function eyes_get_terms_for_concurso_filter( $taxonomy ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	$allowlist = eyes_get_concurso_filter_term_allowlist( $taxonomy );

	if ( empty( $allowlist ) ) {
		return $terms;
	}

	$by_slug = array();

	foreach ( $terms as $term ) {
		if ( in_array( $term->slug, $allowlist, true ) ) {
			$by_slug[ $term->slug ] = $term;
		}
	}

	$ordered = array();

	foreach ( $allowlist as $slug ) {
		if ( isset( $by_slug[ $slug ] ) ) {
			$ordered[] = $by_slug[ $slug ];
		}
	}

	return $ordered;
}

/**
 * Move post assignments from a legacy term slug to the canonical slug.
 *
 * @param string $taxonomy  Taxonomy slug.
 * @param string $from_slug Legacy slug.
 * @param string $to_slug   Canonical slug.
 */
function eyes_migrate_concurso_term_assignments( $taxonomy, $from_slug, $to_slug ) {
	if ( ! taxonomy_exists( $taxonomy ) || $from_slug === $to_slug ) {
		return;
	}

	$from_term = get_term_by( 'slug', $from_slug, $taxonomy );
	$to_term   = get_term_by( 'slug', $to_slug, $taxonomy );

	if ( ! $from_term || is_wp_error( $from_term ) || ! $to_term || is_wp_error( $to_term ) ) {
		return;
	}

	$post_ids = get_objects_in_term( (int) $from_term->term_id, $taxonomy );

	if ( is_wp_error( $post_ids ) || empty( $post_ids ) ) {
		wp_delete_term( (int) $from_term->term_id, $taxonomy );
		return;
	}

	foreach ( $post_ids as $post_id ) {
		$current_slugs = wp_get_object_terms( (int) $post_id, $taxonomy, array( 'fields' => 'slugs' ) );

		if ( is_wp_error( $current_slugs ) ) {
			continue;
		}

		$current_slugs = array_values(
			array_diff(
				array_map( 'strval', $current_slugs ),
				array( $from_slug )
			)
		);

		if ( ! in_array( $to_slug, $current_slugs, true ) ) {
			$current_slugs[] = $to_slug;
		}

		wp_set_object_terms( (int) $post_id, $current_slugs, $taxonomy );
	}

	wp_delete_term( (int) $from_term->term_id, $taxonomy );
}

/**
 * Delete taxonomy terms that are not in the canonical allowlist.
 *
 * @param string   $taxonomy    Taxonomy slug.
 * @param string[] $valid_slugs Allowed slugs.
 */
function eyes_delete_invalid_concurso_terms( $taxonomy, $valid_slugs ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'fields'     => 'all',
		)
	);

	if ( is_wp_error( $terms ) ) {
		return;
	}

	foreach ( $terms as $term ) {
		if ( in_array( $term->slug, $valid_slugs, true ) ) {
			continue;
		}

		wp_delete_term( (int) $term->term_id, $taxonomy );
	}
}

/**
 * Filter keys exposed in the archive UI and AJAX.
 *
 * @return string[]
 */
function eyes_get_concurso_filter_keys() {
	$keys = array();

	foreach ( eyes_get_concurso_archive_filter_definitions() as $definition ) {
		$keys[] = $definition['key'];
	}

	return $keys;
}

/**
 * Whether the current request is the public concurso listing.
 */
function eyes_is_concurso_listing_page() {
	return is_post_type_archive( 'concurso' );
}

/**
 * Redirect legacy /concursos/ URLs to the concurso archive.
 */
function eyes_redirect_concursos_archive_alias() {
	if ( is_admin() || ! is_404() ) {
		return;
	}

	$request_path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	if ( ! is_string( $request_path ) ) {
		return;
	}

	$path = untrailingslashit( $request_path );
	$home = wp_parse_url( home_url( '/' ), PHP_URL_PATH );

	if ( is_string( $home ) && '' !== $home && '/' !== $home ) {
		$prefix = untrailingslashit( $home );
		if ( 0 === strpos( $path, $prefix ) ) {
			$path = substr( $path, strlen( $prefix ) );
		}
	}

	if ( '/concursos' !== $path ) {
		return;
	}

	$archive_url = get_post_type_archive_link( 'concurso' );

	if ( ! $archive_url ) {
		return;
	}

	wp_safe_redirect( $archive_url, 301 );
	exit;
}
add_action( 'template_redirect', 'eyes_redirect_concursos_archive_alias', 0 );

/**
 * Keep the concurso archive reachable for guests (login wall / membership plugins).
 */
function eyes_concurso_archive_public_access() {
	if ( ! is_post_type_archive( 'concurso' ) ) {
		return;
	}

	remove_action( 'template_redirect', 'auth_redirect' );
	remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );
}
add_action( 'template_redirect', 'eyes_concurso_archive_public_access', 0 );

/**
 * Allow guests to browse the concurso archive when a site-wide login wall is active.
 *
 * @param bool $bypass Whether to bypass the restriction.
 */
function eyes_concurso_archive_rsa_bypass( $bypass ) {
	if ( is_post_type_archive( 'concurso' ) ) {
		return true;
	}

	return $bypass;
}
add_filter( 'restricted_site_access_bypass', 'eyes_concurso_archive_rsa_bypass' );

/**
 * @param bool $can_view Whether the post can be viewed.
 */
function eyes_concurso_archive_members_access( $can_view ) {
	if ( is_post_type_archive( 'concurso' ) ) {
		return true;
	}

	return $can_view;
}
add_filter( 'members_can_current_user_view_post', 'eyes_concurso_archive_members_access' );

/**
 * Status filter options (ACF concurso_status values).
 *
 * @return array<string, string>
 */
function eyes_get_concurso_status_labels() {
	return array(
		'abertos'  => __( 'Abertos', 'eyes-child' ),
		'fechados' => __( 'Fechados', 'eyes-child' ),
	);
}

/**
 * CSS modifier class for a concurso status tag.
 *
 * @param string $status ACF concurso_status slug (abertos|fechados).
 */
function eyes_get_concurso_status_tag_class( $status ) {
	return 'fechados' === sanitize_key( (string) $status ) ? 'tag--fechado' : 'tag--aberto';
}

/**
 * Financing filter options (ACF concurso_activity field values).
 *
 * @return array<string, string>
 */
function eyes_get_concurso_activity_labels() {
	return array(
		'incentivos'      => __( 'Incentivos a fundo perdido', 'eyes-child' ),
		'financiamento'   => __( 'Financiamento bancário', 'eyes-child' ),
		'capital_risco'   => __( 'Capital de risco', 'eyes-child' ),
		'outros'          => __( 'Outros', 'eyes-child' ),
	);
}

/**
 * Parse filter params from a request array.
 *
 * @param array<string, mixed>|null $source Request source; defaults to $_GET.
 * @return array<string, mixed>
 */
function eyes_get_concurso_filter_params( $source = null ) {
	if ( null === $source ) {
		$source = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	$params = array(
		'q'                 => '',
		'concurso_status'   => array(),
		'concurso_activity' => array(),
		'sort'              => 'recent',
		'paged'             => 1,
		'dashboard_tab'     => 'todos',
	);

	foreach ( eyes_concurso_filter_taxonomies() as $taxonomy ) {
		$params[ $taxonomy ] = array();
	}

	if ( isset( $source['q'] ) ) {
		$params['q'] = sanitize_text_field( (string) $source['q'] );
	}

	if ( isset( $source['sort'] ) ) {
		$sort = sanitize_key( (string) $source['sort'] );
		if ( in_array( $sort, array( 'recent', 'relevance' ), true ) ) {
			$params['sort'] = $sort;
		}
	}

	if ( isset( $source['paged'] ) ) {
		$params['paged'] = max( 1, absint( $source['paged'] ) );
	}

	if ( eyes_concurso_has_extended_filters() ) {
		$params['dashboard_tab'] = eyes_get_dashboard_tab_slug( $source );
	}

	foreach ( eyes_concurso_filter_taxonomies() as $taxonomy ) {
		$params[ $taxonomy ] = eyes_parse_concurso_filter_values( $source, $taxonomy );
	}

	$params['concurso_status'] = eyes_parse_concurso_filter_values( $source, 'concurso_status' );
	$params['concurso_status'] = array_values(
		array_intersect(
			$params['concurso_status'],
			array_keys( eyes_get_concurso_status_labels() )
		)
	);

	$params['concurso_activity'] = eyes_parse_concurso_filter_values( $source, 'concurso_activity' );
	$params['concurso_activity'] = array_values(
		array_intersect(
			$params['concurso_activity'],
			array_keys( eyes_get_concurso_activity_labels() )
		)
	);

	return $params;
}

/**
 * Parse comma-separated or array filter values from a request key.
 *
 * @param array<string, mixed> $source Request source.
 * @param string               $key    Parameter name.
 * @return string[]
 */
function eyes_parse_concurso_filter_values( $source, $key ) {
	if ( ! isset( $source[ $key ] ) ) {
		return array();
	}

	$raw = $source[ $key ];

	if ( is_array( $raw ) ) {
		$values = $raw;
	} else {
		$values = explode( ',', (string) $raw );
	}

	$values = array_map(
		static function ( $value ) use ( $key ) {
			if ( in_array( $key, array( 'concurso_status', 'concurso_activity' ), true ) ) {
				return sanitize_key( (string) $value );
			}

			return sanitize_title( (string) $value );
		},
		$values
	);

	return array_values( array_filter( array_unique( $values ) ) );
}

/**
 * Build WP_Query args from filter params.
 *
 * @param array<string, mixed> $params Filter params.
 * @return array<string, mixed>
 */
function eyes_build_concurso_query_args( $params ) {
	$args = array(
		'post_type'              => 'concurso',
		'post_status'            => 'publish',
		'posts_per_page'         => (int) get_option( 'posts_per_page', 10 ),
		'paged'                  => max( 1, (int) $params['paged'] ),
		'ignore_sticky_posts'    => true,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	);

	if ( ! empty( $params['q'] ) ) {
		$args['s'] = $params['q'];
	}

	if ( 'relevance' === $params['sort'] && ! empty( $params['q'] ) ) {
		$args['orderby'] = 'relevance';
	} else {
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
	}

	$tax_query = array( 'relation' => 'AND' );

	foreach ( eyes_concurso_filter_taxonomies() as $taxonomy ) {
		if ( empty( $params[ $taxonomy ] ) ) {
			continue;
		}

		$tax_query[] = array(
			'taxonomy' => $taxonomy,
			'field'    => 'slug',
			'terms'    => $params[ $taxonomy ],
			'operator' => 'IN',
		);
	}

	if ( count( $tax_query ) > 1 ) {
		$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	$meta_query = array( 'relation' => 'AND' );

	if ( ! empty( $params['concurso_status'] ) ) {
		$meta_query[] = array(
			'key'     => 'concurso_status',
			'value'   => $params['concurso_status'],
			'compare' => 'IN',
		);
	}

	if ( ! empty( $params['concurso_activity'] ) ) {
		$meta_query[] = array(
			'key'     => 'concurso_activity',
			'value'   => $params['concurso_activity'],
			'compare' => 'IN',
		);
	}

	if ( count( $meta_query ) > 1 ) {
		$args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	}

	if ( eyes_concurso_has_extended_filters() && ! empty( $params['dashboard_tab'] ) ) {
		$args = eyes_apply_dashboard_tab_to_query( $args, $params['dashboard_tab'] );
	}

	return $args;
}

/**
 * Apply archive filters to the main query on initial page load.
 *
 * @param WP_Query $query Main query.
 */
function eyes_filter_concurso_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'concurso' ) ) {
		return;
	}

	$params     = eyes_get_concurso_filter_params();
	$query_args = eyes_build_concurso_query_args( $params );

	foreach ( array( 's', 'orderby', 'order', 'meta_query', 'tax_query', 'post__in', 'eyes_recommended_first' ) as $key ) {
		if ( isset( $query_args[ $key ] ) ) {
			$query->set( $key, $query_args[ $key ] );
		}
	}
}
add_action( 'pre_get_posts', 'eyes_filter_concurso_archive_query' );

/**
 * Render concurso cards HTML for a query.
 *
 * @param WP_Query $query              Query instance.
 * @param bool     $show_empty_message Whether to render the generic empty-state message.
 */
function eyes_render_concurso_cards( $query, $show_empty_message = true, $card_variant = 'default' ) {
	set_query_var( 'eyes_concurso_card_variant', $card_variant );

	if ( is_user_logged_in() && function_exists( 'eyes_prime_recommended_display_cache' ) ) {
		eyes_prime_recommended_display_cache( get_current_user_id() );
	}

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part( 'template-parts/concurso', 'card' );
		}
	} elseif ( $show_empty_message ) {
		echo '<p class="concursos-results__empty">' . esc_html__( 'Nenhum concurso encontrado.', 'eyes-child' ) . '</p>';
	}
}

/**
 * Render pagination markup for a concurso query.
 *
 * @param WP_Query             $query  Query instance.
 * @param array<string, mixed> $params Filter params.
 */
function eyes_render_concurso_pagination( $query, $params ) {
	if ( $query->max_num_pages <= 1 ) {
		return;
	}

	echo paginate_links(
		array(
			'total'     => $query->max_num_pages,
			'current'   => max( 1, (int) $params['paged'] ),
			'prev_text' => '<img src="' . esc_url( eyes_asset_uri( 'images/icon-arrow-left.svg' ) ) . '" width="20" height="20" alt="" /> ' . esc_html__( 'Anterior', 'eyes-child' ),
			'next_text' => esc_html__( 'Próxima', 'eyes-child' ) . ' <img src="' . esc_url( eyes_asset_uri( 'images/icon-arrow-right.svg' ) ) . '" width="20" height="20" alt="" />',
			'type'      => 'plain',
		)
	);
}

/**
 * AJAX: filter concursos and return cards + pagination.
 */
function eyes_ajax_filter_concursos() {
	$params       = eyes_get_concurso_filter_params( wp_unslash( $_GET ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$query        = new WP_Query( eyes_build_concurso_query_args( $params ) );
	$is_dashboard = is_user_logged_in() && ! empty( $params['dashboard_tab'] );
	$card_variant = ( $is_dashboard && 'territorio' === $params['dashboard_tab'] ) ? 'territorio' : 'default';

	ob_start();
	eyes_render_concurso_cards( $query, ! $is_dashboard, $card_variant );
	$results_html = ob_get_clean();

	ob_start();
	eyes_render_concurso_pagination( $query, $params );
	$pagination_html = ob_get_clean();

	wp_reset_postdata();

	$empty_state = array();
	if ( $is_dashboard && is_user_logged_in() && 0 === (int) $query->found_posts && function_exists( 'eyes_get_dashboard_empty_state' ) ) {
		$empty_state = eyes_get_dashboard_empty_state( (string) $params['dashboard_tab'], get_current_user_id() );
	}

	wp_send_json_success(
		array(
			'html'         => $results_html,
			'pagination'   => $pagination_html,
			'found'        => (int) $query->found_posts,
			'max_pages'    => (int) $query->max_num_pages,
			'current_page' => max( 1, (int) $params['paged'] ),
			'empty_state'  => $empty_state,
		)
	);
}
add_action( 'wp_ajax_eyes_filter_concursos', 'eyes_ajax_filter_concursos' );
add_action( 'wp_ajax_nopriv_eyes_filter_concursos', 'eyes_ajax_filter_concursos' );

/**
 * AJAX: return concurso title suggestions for the archive search box.
 */
function eyes_ajax_concurso_search_suggestions() {
	$query_text = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( '' === $query_text ) {
		wp_send_json_success( array( 'items' => array() ) );
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'concurso',
			'post_status'            => 'publish',
			's'                      => $query_text,
			'posts_per_page'         => 8,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	$items = array();

	foreach ( $query->posts as $post ) {
		$items[] = array(
			'id'     => (int) $post->ID,
			'title'  => get_the_title( $post ),
			'entity' => function_exists( 'get_field' ) ? (string) get_field( 'concurso_entity', $post->ID ) : '',
		);
	}

	wp_send_json_success( array( 'items' => $items ) );
}
add_action( 'wp_ajax_eyes_concurso_search_suggestions', 'eyes_ajax_concurso_search_suggestions' );
add_action( 'wp_ajax_nopriv_eyes_concurso_search_suggestions', 'eyes_ajax_concurso_search_suggestions' );

/**
 * Localize concurso filter config for main.js.
 */
function eyes_localize_concurso_filters_script() {
	if ( ! wp_script_is( 'eyes-concursos-filters', 'enqueued' ) || ! eyes_is_concurso_listing_page() ) {
		return;
	}

	wp_localize_script(
		'eyes-concursos-filters',
		'eyesConcursos',
		array(
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
			'archiveUrl'    => get_post_type_archive_link( 'concurso' ),
			'filterKeys'    => eyes_get_concurso_filter_keys(),
			'isLoggedIn'    => is_user_logged_in(),
			'isDashboard'   => is_user_logged_in() && eyes_is_concurso_listing_page(),
			'dashboardTabs' => is_user_logged_in() ? eyes_get_dashboard_tabs() : array(),
			'i18n'          => array(
				'count'      => __( '%d encontradas', 'eyes-child' ),
				'loadError'  => __( 'Não foi possível carregar os concursos. Tente novamente.', 'eyes-child' ),
				'loading'    => __( 'A carregar…', 'eyes-child' ),
				'noResults'  => __( 'Nenhum concurso encontrado.', 'eyes-child' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'eyes_localize_concurso_filters_script', 30 );
