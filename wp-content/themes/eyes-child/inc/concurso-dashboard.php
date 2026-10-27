<?php

/**

 * Logged-in concurso dashboard (profile, stats, tabs).

 *

 * @package Eyes_Child

 */



defined( 'ABSPATH' ) || exit;



/**

 * Dashboard tab definitions.

 *

 * @return array<string, array<string, mixed>>

 */

function eyes_get_dashboard_tabs() {

	return array(

		'recomendadas'  => array(

			'label'       => __( 'Recomendadas', 'eyes-child' ),

			'panel_title' => __( 'Oportunidades Recomendadas', 'eyes-child' ),

			'icon'        => 'icon-tab-recomendadas.svg',

			'layout'      => 'default',

		),

		'guardadas'     => array(

			'label'       => __( 'Guardadas', 'eyes-child' ),

			'panel_title' => __( 'Oportunidades Guardadas', 'eyes-child' ),

			'icon'        => 'icon-tab-guardadas.svg',

			'layout'      => 'default',

		),

		'candidaturas'  => array(

			'label'       => __( 'Candidatura feita', 'eyes-child' ),

			'panel_title' => __( 'Candidaturas Feitas', 'eyes-child' ),

			'icon'        => 'icon-tab-candidaturas.svg',

			'layout'      => 'default',

		),

		'todos'         => array(

			'label'       => __( 'Todos os concursos', 'eyes-child' ),

			'panel_title' => __( 'Todos os concursos', 'eyes-child' ),

			'icon'        => 'icon-tab-todos.svg',

			'layout'      => 'default',

			'pagination'  => true,

		),

		'territorio'    => array(

			'label'       => __( 'Oportunidades por território', 'eyes-child' ),

			'panel_title' => __( 'Oportunidades por território', 'eyes-child' ),

			'panel_lead'  => __( 'Explore oportunidades de financiamento por região', 'eyes-child' ),

			'icon'        => 'icon-tab-territorio.svg',

			'layout'      => 'territorio',

			'pagination'  => true,

		),

	);

}



/**

 * Output an inline dashboard tab icon so currentColor can style active/inactive states.

 *

 * @param string $tab_id Tab slug.

 */

function eyes_render_dashboard_tab_icon( $tab_id ) {

	$tabs = eyes_get_dashboard_tabs();



	if ( empty( $tabs[ $tab_id ]['icon'] ) ) {

		return;

	}



	$icon_file = get_stylesheet_directory() . '/assets/images/' . $tabs[ $tab_id ]['icon'];



	if ( ! is_readable( $icon_file ) ) {

		return;

	}



	$svg = file_get_contents( $icon_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents



	if ( ! is_string( $svg ) || '' === $svg ) {

		return;

	}



	if ( false === strpos( $svg, 'class="dashboard-tabs__svg"' ) ) {

		$svg = preg_replace( '/<svg\\b/', '<svg class="dashboard-tabs__svg"', $svg, 1 );

	}



	if ( 'todos' === $tab_id ) {

		$clip_id = 'icon-tab-todos-clip-' . sanitize_html_class( $tab_id );

		$svg     = str_replace( 'icon-tab-todos-clip', $clip_id, $svg );

	}



	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted theme SVG asset.

	echo $svg;

}



/**

 * Whether the current user has a Pro plan.

 *

 * @param int $user_id User ID.

 */

function eyes_user_has_pro_plan( $user_id = 0 ) {

	$user_id = $user_id ? (int) $user_id : get_current_user_id();



	if ( $user_id <= 0 ) {

		return false;

	}



	return 'pro' === get_user_meta( $user_id, 'eyes_plan', true );

}



/**

 * Dashboard headline (company name preferred).

 *

 * @param int $user_id User ID.

 */

function eyes_get_dashboard_display_name( $user_id = 0 ) {

	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	$user    = get_userdata( $user_id );



	if ( ! $user ) {

		return '';

	}



	$empresa = trim( (string) get_user_meta( $user_id, 'eyes_empresa', true ) );



	if ( '' !== $empresa ) {

		return $empresa;

	}



	$name = eyes_current_user_full_name( $user );



	return '' !== $name ? $name : $user->display_name;

}



/**

 * Profile field values for the welcome banner.

 *

 * @param int $user_id User ID.

 * @return array<string, string>

 */

function eyes_get_dashboard_profile_fields( $user_id = 0 ) {

	$user_id = $user_id ? (int) $user_id : get_current_user_id();



	$cae_code = trim( (string) get_user_meta( $user_id, 'eyes_cae', true ) );

	$setor    = trim( (string) get_user_meta( $user_id, 'eyes_setor', true ) );



	if ( '' === $setor && '' !== $cae_code && function_exists( 'eyes_get_cae_codes' ) ) {

		$cae_codes = eyes_get_cae_codes();

		if ( isset( $cae_codes[ $cae_code ] ) ) {

			$setor = function_exists( 'eyes_format_cae_label' )

				? eyes_format_cae_label( $cae_code, $cae_codes[ $cae_code ] )

				: $cae_codes[ $cae_code ];

		}

	}



	$regiao = trim( (string) get_user_meta( $user_id, 'eyes_regiao', true ) );



	if ( '' === $regiao ) {

		$localidade = trim( (string) get_user_meta( $user_id, 'eyes_localidade', true ) );

		$regiao_map = array(

			'porto'  => __( 'Continental - Norte', 'eyes-child' ),

			'lisboa' => __( 'Continental - Centro', 'eyes-child' ),

		);

		$regiao     = $regiao_map[ $localidade ] ?? '';

	}



	$enquadramento = trim( (string) get_user_meta( $user_id, 'eyes_enquadramento_pme', true ) );

	if ( '' !== $enquadramento && function_exists( 'eyes_get_pme_enquadramento_options' ) ) {
		$pme_options = eyes_get_pme_enquadramento_options();
		if ( isset( $pme_options[ $enquadramento ] ) ) {
			$enquadramento = $pme_options[ $enquadramento ];
		}
	}

	if ( '' === $enquadramento ) {
		$enquadramento = '—';
	}



	return array(

		'setor'         => $setor ?: '—',

		'regiao'        => $regiao ?: '—',

		'enquadramento' => $enquadramento,

		'plano'         => eyes_user_has_pro_plan( $user_id )

			? __( 'Plano Pro', 'eyes-child' )

			: __( 'Gratuito', 'eyes-child' ),

	);

}



/**

 * Applied concurso IDs for a user.

 *

 * @param int $user_id User ID.

 * @return int[]

 */

function eyes_get_applied_concurso_ids( $user_id = 0 ) {

	$user_id = $user_id ? (int) $user_id : get_current_user_id();



	if ( $user_id <= 0 ) {

		return array();

	}



	$ids = get_user_meta( $user_id, 'eyes_applied_concursos', true );



	if ( ! is_array( $ids ) ) {

		return array();

	}



	return array_values( array_unique( array_filter( array_map( 'intval', $ids ) ) ) );

}



/**

 * Count favourites with a deadline within the next N days.

 *

 * @param int $user_id User ID.

 * @param int $days    Window in days.

 */

function eyes_count_favourites_deadline_soon( $user_id = 0, $days = 30 ) {

	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	$ids     = eyes_get_favourite_concurso_ids( $user_id );



	if ( empty( $ids ) ) {

		return 0;

	}



	$cutoff = gmdate( 'Ymd', time() + ( $days * DAY_IN_SECONDS ) );

	$count  = 0;



	foreach ( $ids as $post_id ) {

		if ( ! function_exists( 'get_field' ) ) {

			continue;

		}



		$deadline = get_field( 'concurso_deadline', $post_id );



		if ( ! is_string( $deadline ) || '' === trim( $deadline ) ) {

			continue;

		}



		$deadline = trim( $deadline );



		if ( preg_match( '/^\\d{8}$/', $deadline ) && $deadline <= $cutoff ) {

			++$count;

			continue;

		}



		if ( preg_match( '/^\\d{4}-\\d{2}-\\d{2}$/', $deadline ) ) {

			$normalized = str_replace( '-', '', $deadline );

			if ( $normalized <= $cutoff ) {

				++$count;

			}

		}

	}



	return $count;

}



/**

 * Dashboard summary stats.

 *

 * @param int $user_id User ID.

 * @return array<string, int>

 */

function eyes_get_dashboard_stats( $user_id = 0 ) {

	$user_id = $user_id ? (int) $user_id : get_current_user_id();



	$recommended_query = new WP_Query(

		eyes_build_concurso_query_args(

			array_merge(

				eyes_get_concurso_filter_params(),

				array(

					'paged'         => 1,

					'dashboard_tab' => 'recomendadas',

				)

			)

		)

	);



	$favourites = eyes_get_favourite_concurso_ids( $user_id );



	return array(

		'opportunities'   => (int) $recommended_query->found_posts,

		'saved'           => count( $favourites ),

		'saved_deadline'  => eyes_count_favourites_deadline_soon( $user_id ),

	);

}



/**

 * Active dashboard tab slug from request.

 *

 * @param array<string, mixed>|null $source Request source.

 */

function eyes_get_dashboard_tab_slug( $source = null ) {

	if ( null === $source ) {

		$source = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	}



	$tab = isset( $source['dashboard_tab'] ) ? sanitize_key( (string) $source['dashboard_tab'] ) : 'todos';

	$tabs = eyes_get_dashboard_tabs();

	return array_key_exists( $tab, $tabs ) ? $tab : 'todos';

}



/**

 * Apply dashboard tab constraints to query args.

 *

 * @param array<string, mixed> $args Query args.

 * @param string               $tab  Tab slug.

 * @return array<string, mixed>

 */

function eyes_apply_dashboard_tab_to_query( $args, $tab ) {

	$user_id = get_current_user_id();



	switch ( $tab ) {

		case 'guardadas':

			$ids = eyes_get_favourite_concurso_ids( $user_id );

			if ( empty( $ids ) ) {

				$args['post__in'] = array( 0 );

			} else {

				$args['post__in'] = $ids;

				$args['orderby']  = 'post__in';

			}

			break;



		case 'candidaturas':

			$ids = eyes_get_applied_concurso_ids( $user_id );

			if ( empty( $ids ) ) {

				$args['post__in'] = array( 0 );

			} else {

				$args['post__in'] = $ids;

				$args['orderby']  = 'post__in';

			}

			break;



		case 'recomendadas':

			$ids = function_exists( 'eyes_get_recommended_concurso_ids' )

				? eyes_get_recommended_concurso_ids( $user_id )

				: array();



			if ( empty( $ids ) ) {

				$args['post__in'] = array( 0 );

			} else {

				$args['post__in'] = $ids;

				$args['orderby']  = 'post__in';

			}

			break;



		case 'todos':

		case 'territorio':
			if ( function_exists( 'eyes_apply_recommended_first_query_args' ) ) {
				$args = eyes_apply_recommended_first_query_args( $args, $user_id );
			}
			break;

		default:
			break;

	}



	return $args;

}



/**

 * Territorio map image URL.

 */

function eyes_territorio_map_uri() {

	$candidates = array(

		'images/oportunidades-por-territorio-map.png',

		'images/oportunidades-por-território-map.png',

		'images/oportunidades-por-territorio-map.webp',

		'images/oportunidades-por-território-map.webp',

	);



	foreach ( $candidates as $file ) {

		if ( file_exists( get_stylesheet_directory() . '/assets/' . $file ) ) {

			return eyes_asset_uri( $file );

		}

	}



	return eyes_asset_uri( 'images/oportunidades-por-territorio-map.png' );

}



/**

 * Map marker positions and regiao groups for the territorio tab.

 *

 * Counts show open concursos (status abertos) tagged with each regiao group.

 * National concursos without a regiao term are not included in regional bubbles.

 *

 * @param int                  $user_id       User ID (reserved for future personalization).

 * @param array<string, mixed> $filter_params Optional active archive filters.

 * @return array<int, array{count:int,left:string,top:string,label:string}>

 */

function eyes_get_territorio_map_markers( $user_id = 0, $filter_params = array() ) {

	unset( $user_id );



	if ( empty( $filter_params ) && function_exists( 'eyes_get_concurso_filter_params' ) ) {

		$filter_params = eyes_get_concurso_filter_params();

		$filter_params['dashboard_tab'] = 'territorio';

	}



	$markers = array(

		array(

			'label'   => __( 'Norte', 'eyes-child' ),

			'left'    => '28.5%',

			'top'     => '21.7%',

			'regions' => array( 'continental-norte' ),

		),

		array(

			'label'   => __( 'Centro', 'eyes-child' ),

			'left'    => '51.4%',

			'top'     => '32.2%',

			'regions' => array( 'continental-centro' ),

		),

		array(

			'label'   => __( 'Continental - Sul', 'eyes-child' ),

			'left'    => '42.2%',

			'top'     => '79.9%',

			'regions' => array( 'continental-sul' ),

		),

		array(

			'label'   => __( 'Regiões Autónomas', 'eyes-child' ),

			'left'    => '22.7%',

			'top'     => '83.7%',

			'regions' => array( 'insular-acores', 'insular-madeira' ),

		),

	);



	$results = array();



	foreach ( $markers as $marker ) {

		$count = function_exists( 'eyes_count_open_concursos_for_regioes' )

			? eyes_count_open_concursos_for_regioes( $marker['regions'], false, $filter_params )

			: 0;



		$results[] = array(

			'count' => $count,

			'left'  => $marker['left'],

			'top'   => $marker['top'],

			'label' => $marker['label'],

		);

	}



	return $results;

}



/**

 * Add dashboard body classes on the concurso archive when logged in.

 *

 * @param string[] $classes Body classes.

 * @return string[]

 */

function eyes_dashboard_body_classes( $classes ) {

	if ( is_user_logged_in() && is_post_type_archive( 'concurso' ) ) {

		$classes[] = 'dashboard-page';



		if ( eyes_user_has_pro_plan() ) {

			$classes[] = 'dashboard-page--pro';

		}

	}



	if ( is_singular( 'concurso' ) ) {

		$classes[] = 'concurso-detail-page';

	}



	return $classes;

}

add_filter( 'body_class', 'eyes_dashboard_body_classes' );



/**

 * AJAX handler for dashboard tab content.

 */

function eyes_ajax_fetch_dashboard_tab_content() {

	check_ajax_referer( 'dashboard_tab_nonce', 'nonce' );



	$tab_id = sanitize_key( $_POST['tab_id'] ?? 'todos' );



	// Set up WP_Query args based on the tab

	$query_args = eyes_build_concurso_query_args(

		eyes_get_concurso_filter_params( wp_unslash( $_POST['filters'] ?? array() ) ),

		array(

			'paged'         => absint( $_POST['paged'] ?? 1 ),

			'dashboard_tab' => $tab_id,

		)

	);



	$concursos_query = new WP_Query( $query_args );



	ob_start();



	if ( $concursos_query->have_posts() ) {

		set_query_var( 'eyes_concurso_card_variant', 'territorio' === $tab_id ? 'territorio' : 'default' );

		while ( $concursos_query->have_posts() ) {

			$concursos_query->the_post();

			get_template_part( 'template-parts/concurso', 'card' );

		}

		wp_reset_postdata();

	} else {

		eyes_render_empty_dashboard_tab_content(); // Custom function to render empty state

	}



	$content = ob_get_clean();



	wp_send_json_success( array(

		'content'       => $content,

		'total_posts'   => $concursos_query->found_posts,

		'max_num_pages' => $concursos_query->max_num_pages,

	) );

}

add_action( 'wp_ajax_fetch_dashboard_tab_content', 'eyes_ajax_fetch_dashboard_tab_content' );

add_action( 'wp_ajax_nopriv_fetch_dashboard_tab_content', 'eyes_ajax_fetch_dashboard_tab_content' );



/**

 * Render empty state content for dashboard tabs.

 */

function eyes_render_empty_dashboard_tab_content() {

	get_template_part( 'template-parts/dashboard', 'empty' );

}