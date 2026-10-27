<?php

/**

 * Logged-in concurso dashboard (profile, stats, tabs, filters, results).

 *

 * @package Eyes_Child

 */



defined( 'ABSPATH' ) || exit;



$user_id        = get_current_user_id();

$profile        = eyes_get_dashboard_profile_fields( $user_id );

$stats          = eyes_get_dashboard_stats( $user_id );

$tabs           = eyes_get_dashboard_tabs();

$active_tab     = eyes_get_dashboard_tab_slug();

$active_tab_def = $tabs[ $active_tab ];

$is_pro         = eyes_user_has_pro_plan( $user_id );

$params         = eyes_get_concurso_filter_params();

$found_posts    = (int) $wp_query->found_posts;

$show_empty     = 0 === $found_posts;

$empty_state    = function_exists( 'eyes_get_dashboard_empty_state' )
	? eyes_get_dashboard_empty_state( $active_tab, $user_id )
	: array(
		'type'      => 'generic',
		'title'     => __( 'Comece hoje a aproveitar as oportunidades disponíveis', 'eyes-child' ),
		'text'      => __( 'Explore as nossas recomendações e encontre oportunidades únicas para o perfil da sua empresa.', 'eyes-child' ),
		'cta_label' => __( 'Explorar recomendadas', 'eyes-child' ),
		'cta_url'   => '#',
	);

$show_map   = 'territorio' === $active_tab;

$theme_uri  = get_stylesheet_directory_uri();

$planos_url = home_url( '/planos/' );

?>



<main class="dashboard-page" data-dashboard data-dashboard-account="<?php echo $is_pro ? 'pro' : 'free'; ?>">

	<div class="site-layout site-layout--top-24">

		<section class="dashboard-welcome" aria-labelledby="dashboard-welcome-title">

			<div class="dashboard-welcome__inner">

				<div class="dashboard-welcome__head">

					<h1 class="dashboard-welcome__title" id="dashboard-welcome-title">

						<?php echo esc_html( eyes_get_dashboard_display_name( $user_id ) ); ?>

					</h1>

					<div class="dashboard-welcome__actions">

						<a class="btn btn--welcome-outline" href="<?php echo esc_url( eyes_edit_profile_url() ); ?>">

							<?php esc_html_e( 'Editar Perfil', 'eyes-child' ); ?>

						</a>

						<?php if ( ! $is_pro ) : ?>

							<a class="btn btn--welcome-pro" href="<?php echo esc_url( $planos_url ); ?>" data-dashboard-upgrade>

								<img class="btn__icon" src="<?php echo esc_url( eyes_asset_uri( 'images/icon-arrow-right.svg' ) ); ?>" width="16" height="16" alt="" />

								<?php esc_html_e( 'Upgrade para Pro', 'eyes-child' ); ?>

							</a>

						<?php endif; ?>

					</div>

				</div>



				<dl class="dashboard-welcome__profile">

					<div class="dashboard-welcome__profile-item">

						<dt class="dashboard-welcome__profile-label"><?php esc_html_e( 'Setor', 'eyes-child' ); ?></dt>

						<dd class="dashboard-welcome__profile-value"><?php echo esc_html( $profile['setor'] ); ?></dd>

					</div>

					<div class="dashboard-welcome__profile-item">

						<dt class="dashboard-welcome__profile-label"><?php esc_html_e( 'Região', 'eyes-child' ); ?></dt>

						<dd class="dashboard-welcome__profile-value"><?php echo esc_html( $profile['regiao'] ); ?></dd>

					</div>

					<div class="dashboard-welcome__profile-item">

						<dt class="dashboard-welcome__profile-label"><?php esc_html_e( 'Enquadramento PME', 'eyes-child' ); ?></dt>

						<dd class="dashboard-welcome__profile-value"><?php echo esc_html( $profile['enquadramento'] ); ?></dd>

					</div>

					<div class="dashboard-welcome__profile-item">

						<dt class="dashboard-welcome__profile-label"><?php esc_html_e( 'Plano', 'eyes-child' ); ?></dt>

						<dd class="dashboard-welcome__profile-value" data-dashboard-plan-value><?php echo esc_html( $profile['plano'] ); ?></dd>

					</div>

				</dl>

			</div>

		</section>



		<div class="dashboard-stats" data-dashboard-stats>

			<div class="dashboard-stat">

				<p class="dashboard-stat__label"><?php esc_html_e( 'Oportunidades para si', 'eyes-child' ); ?></p>

				<p class="dashboard-stat__value" data-dashboard-stat-opportunities><?php echo absint( $stats['opportunities'] ); ?></p>

			</div>

			<div class="dashboard-stat">

				<p class="dashboard-stat__label"><?php esc_html_e( 'Oportunidades guardadas', 'eyes-child' ); ?></p>

				<p class="dashboard-stat__value" data-dashboard-stat-saved><?php echo absint( $stats['saved'] ); ?></p>

			</div>

			<div class="dashboard-stat">

				<p class="dashboard-stat__label"><?php esc_html_e( 'Oportunidades guardadas com prazo próximo', 'eyes-child' ); ?></p>

				<p class="dashboard-stat__value" data-dashboard-stat-deadline><?php echo absint( $stats['saved_deadline'] ); ?></p>

			</div>

		</div>



		<div

			class="dashboard-workspace"

			data-dashboard-workspace

			data-concursos-archive

			data-concursos-filters-extended

			data-layout="<?php echo esc_attr( $active_tab_def['layout'] ?? 'default' ); ?>"

		>

			<nav class="dashboard-tabs" aria-label="<?php esc_attr_e( 'Secções do painel', 'eyes-child' ); ?>" data-dashboard-tabs>

				<?php foreach ( $tabs as $tab_id => $tab_def ) : ?>

					<?php

					$is_active = $tab_id === $active_tab;

					$tab_count = 'recomendadas' === $tab_id ? $stats['opportunities'] : ( 'guardadas' === $tab_id ? $stats['saved'] : $found_posts );

					?>

					<button

						type="button"

						class="dashboard-tabs__btn<?php echo $is_active ? ' is-active' : ''; ?>"

						data-tab="<?php echo esc_attr( $tab_id ); ?>"

						data-panel-title="<?php echo esc_attr( $tab_def['panel_title'] ); ?>"

						data-panel-count="<?php echo esc_attr( sprintf( __( '%d encontradas', 'eyes-child' ), $tab_count ) ); ?>"

						data-layout="<?php echo esc_attr( $tab_def['layout'] ?? 'default' ); ?>"

						<?php if ( ! empty( $tab_def['panel_lead'] ) ) : ?>

							data-panel-lead="<?php echo esc_attr( $tab_def['panel_lead'] ); ?>"

						<?php endif; ?>

						<?php if ( ! empty( $tab_def['pagination'] ) ) : ?>

							data-pagination

						<?php endif; ?>

						<?php echo $is_active ? ' aria-current="page"' : ''; ?>

					>

						<span class="dashboard-tabs__icon" aria-hidden="true">

							<?php eyes_render_dashboard_tab_icon( $tab_id ); ?>

						</span>

						<span class="dashboard-tabs__text"><?php echo esc_html( $tab_def['label'] ); ?></span>

					</button>

				<?php endforeach; ?>

			</nav>



			<div class="dashboard-toolbar">

				<div class="dashboard-toolbar__heading">

					<h2 class="dashboard-toolbar__title" data-dashboard-panel-title><?php echo esc_html( $active_tab_def['panel_title'] ); ?></h2>

					<p class="dashboard-toolbar__count" data-dashboard-panel-count data-concursos-count><?php echo esc_html( sprintf( __( '%d encontradas', 'eyes-child' ), $found_posts ) ); ?></p>

					<?php if ( ! empty( $active_tab_def['panel_lead'] ) ) : ?>

						<p class="dashboard-toolbar__lead" data-dashboard-panel-lead><?php echo esc_html( $active_tab_def['panel_lead'] ); ?></p>

					<?php else : ?>

						<p class="dashboard-toolbar__lead" data-dashboard-panel-lead hidden></p>

					<?php endif; ?>

				</div>



				<div class="dashboard-toolbar__actions">

					<div class="view-toggle" role="group" aria-label="<?php esc_attr_e( 'Modo de visualização', 'eyes-child' ); ?>" data-view-toggle>

						<button type="button" class="view-toggle__btn is-active" data-view="grid" aria-pressed="true" aria-label="<?php esc_attr_e( 'Vista em grelha', 'eyes-child' ); ?>">

							<img src="<?php echo esc_url( $theme_uri ); ?>/assets/images/icon-grid.svg" width="18" height="18" alt="" />

						</button>

						<button type="button" class="view-toggle__btn" data-view="list" aria-pressed="false" aria-label="<?php esc_attr_e( 'Vista em lista', 'eyes-child' ); ?>">

							<img src="<?php echo esc_url( $theme_uri ); ?>/assets/images/icon-list-cards.svg" width="18" height="18" alt="" />

						</button>

					</div>



					<span class="dashboard-toolbar__divider" aria-hidden="true"></span>



					<div class="sort-toggle" role="group" aria-label="<?php esc_attr_e( 'Ordenar resultados', 'eyes-child' ); ?>" data-sort-toggle>

						<button type="button" class="sort-toggle__btn is-active" data-sort="recent" aria-pressed="true"><?php esc_html_e( 'Mais Recente', 'eyes-child' ); ?></button>

						<button type="button" class="sort-toggle__btn" data-sort="relevance" aria-pressed="false"><?php esc_html_e( 'Relevância', 'eyes-child' ); ?></button>

					</div>

				</div>

			</div>



			<?php

			get_template_part(

				'template-parts/concurso',

				'filters',

				array(

					'extended_filters' => true,

					'show_search'      => true,

					'wrapper_class'    => 'dashboard-filters',

				)

			);

			?>



			<div class="dashboard-results-panel" data-dashboard-results-panel data-layout="<?php echo esc_attr( $active_tab_def['layout'] ?? 'default' ); ?>">

				<div class="dashboard-territorio-list" data-dashboard-territorio-list>

					<div

						class="concursos-results dashboard-results<?php echo $show_map ? ' dashboard-results--territorio concursos-results--list' : ''; ?>"

						data-dashboard-results

						data-results

						data-view-mode="<?php echo $show_map ? 'list' : 'grid'; ?>"

						<?php echo $show_empty ? ' hidden' : ''; ?>

					>

						<?php if ( have_posts() && ! $show_empty ) : ?>

							<?php

							set_query_var( 'eyes_concurso_card_variant', $show_map ? 'territorio' : 'default' );

							while ( have_posts() ) :

								the_post();

								get_template_part( 'template-parts/concurso', 'card' );

							endwhile;

							?>

						<?php endif; ?>

					</div>



					<nav

						class="concursos-pagination dashboard-pagination"

						data-dashboard-pagination


						data-concursos-pagination

						aria-label="<?php esc_attr_e( 'Paginação', 'eyes-child' ); ?>"

						<?php echo ( $wp_query->max_num_pages <= 1 || $show_empty ) ? ' hidden' : ''; ?>

					>

						<?php eyes_render_concurso_pagination( $wp_query, $params ); ?>

					</nav>

				</div>



				<div class="dashboard-empty" data-dashboard-empty data-empty-type="<?php echo esc_attr( $empty_state['type'] ); ?>"<?php echo $show_empty ? '' : ' hidden'; ?>>

					<img

						class="dashboard-empty__icon"

						src="<?php echo esc_url( eyes_asset_uri( 'images/icon-rocket.svg' ) ); ?>"

						width="80"

						height="80"

						alt=""

						aria-hidden="true"

					/>

					<div class="dashboard-empty__copy">

						<h3 class="dashboard-empty__title" data-dashboard-empty-title>

							<?php echo esc_html( $empty_state['title'] ); ?>

						</h3>

						<p class="dashboard-empty__text" data-dashboard-empty-text>

							<?php echo esc_html( $empty_state['text'] ); ?>

						</p>

					</div>

					<a class="btn btn--info dashboard-empty__cta" href="<?php echo esc_url( $empty_state['cta_url'] ); ?>" data-dashboard-empty-cta>

						<?php echo esc_html( $empty_state['cta_label'] ); ?>

					</a>

				</div>



				<div

					class="dashboard-territorio-map"

					data-dashboard-map

					<?php echo $show_map ? '' : ' hidden'; ?>

					aria-hidden="<?php echo $show_map ? 'false' : 'true'; ?>"

					role="img"

					aria-label="<?php esc_attr_e( 'Mapa de oportunidades por território', 'eyes-child' ); ?>"

				>

					<div class="dashboard-territorio-map__viewport" aria-hidden="true"></div>

					<?php foreach ( eyes_get_territorio_map_markers( $user_id, $params ) as $marker ) : ?>

						<span

							class="dashboard-territorio-map__marker"

							style="<?php printf( 'left:%1$s;top:%2$s;', esc_attr( $marker['left'] ), esc_attr( $marker['top'] ) ); ?>"

							title="<?php echo esc_attr( $marker['label'] ); ?>"

						><?php echo absint( $marker['count'] ); ?></span>

					<?php endforeach; ?>

				</div>

			</div>

		</div>



		<?php if ( ! $is_pro ) : ?>

			<?php get_template_part( 'template-parts/dashboard', 'pro-cta' ); ?>

		<?php endif; ?>

	</div>

</main>

