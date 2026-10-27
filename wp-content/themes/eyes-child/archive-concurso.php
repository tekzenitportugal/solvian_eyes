<?php
/**
 * Archive template: public listing (guest) or dashboard (logged in).
 *
 * @package Eyes_Child
 */

get_header();

if ( is_user_logged_in() ) {
	get_template_part( 'template-parts/concurso', 'dashboard' );
	get_footer();
	return;
}
?>

<main>
    <div class="site-layout site-layout--top-24">
        <section class="concursos-hero" aria-labelledby="concursos-hero-title">
            <div class="concursos-hero__backdrop" aria-hidden="true">
                <span class="concursos-hero__bg-base"></span>
                <span class="concursos-hero__bg-layer concursos-hero__bg-layer--40"></span>
                <span class="concursos-hero__bg-layer concursos-hero__bg-layer--20"></span>
            </div>

            <div class="concursos-hero__inner">
                <h1 class="concursos-hero__title" id="concursos-hero-title">
                    <?php echo esc_html( get_field( 'concurso_hero_title', 'option' ) ?: 'Concursos e Oportunidades de Financiamento em Portugal' ); ?>
                </h1>
                <p class="concursos-hero__lead">
                    <?php echo esc_html( get_field( 'concurso_hero_lead', 'option' ) ?: 'Explore todos os concursos de financiamento, incentivos e fundos comunitários' ); ?>
                </p>
            </div>
        </section>

        <div class="concursos-main" data-concursos-archive>
            <div class="concursos-toolbar">
                <div class="concursos-toolbar__heading">
                    <h2 class="concursos-toolbar__title"><?php esc_html_e( 'Todos os concursos', 'eyes-child' ); ?></h2>
                    <p class="concursos-toolbar__count" data-concursos-count><?php echo absint( $wp_query->found_posts ); ?> <?php esc_html_e( 'encontradas', 'eyes-child' ); ?></p>
                </div>

                <div class="concursos-toolbar__actions">
                    <div class="view-toggle" role="group" aria-label="<?php esc_attr_e( 'Modo de visualização', 'eyes-child' ); ?>" data-view-toggle>
                        <button type="button" class="view-toggle__btn is-active" data-view="grid" aria-pressed="true" aria-label="<?php esc_attr_e( 'Vista em grelha', 'eyes-child' ); ?>">
                            <img src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/assets/images/icon-grid.svg" width="18" height="18" alt="" />
                        </button>
                        <button type="button" class="view-toggle__btn" data-view="list" aria-pressed="false" aria-label="<?php esc_attr_e( 'Vista em lista', 'eyes-child' ); ?>">
                            <img src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/assets/images/icon-list-cards.svg" width="18" height="18" alt="" />
                        </button>
                    </div>

                    <span class="concursos-toolbar__divider" aria-hidden="true"></span>

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
                    'extended_filters' => false,
                )
            );
            ?>

            <div class="concursos-results" data-results data-view-mode="grid">
                <?php if ( have_posts() ) : ?>
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php get_template_part( 'template-parts/concurso', 'card' ); ?>
                    <?php endwhile; ?>
                <?php else : ?>
                    <p class="concursos-results__empty"><?php esc_html_e( 'Nenhum concurso encontrado.', 'eyes-child' ); ?></p>
                <?php endif; ?>
            </div>

            <nav class="concursos-pagination" data-concursos-pagination aria-label="<?php esc_attr_e( 'Paginação', 'eyes-child' ); ?>"<?php echo $wp_query->max_num_pages <= 1 ? ' hidden' : ''; ?>>
                <?php
                eyes_render_concurso_pagination(
                    $wp_query,
                    eyes_get_concurso_filter_params()
                );
                ?>
            </nav>
        </div>
    </div>

    <section class="concursos-cta" aria-labelledby="concursos-cta-title">
        <div class="concursos-cta__inner">
            <h2 class="concursos-cta__title" id="concursos-cta-title">
                <?php echo esc_html( get_field( 'concurso_cta_title', 'option' ) ?: 'Registe-se na nossa plataforma e receba todas as ofertas no seu email' ); ?>
            </h2>
            <a class="concursos-cta__btn" href="<?php echo esc_url( get_field( 'concurso_cta_button_link', 'option' ) ?: '#' ); ?>">
                <?php echo esc_html( get_field( 'concurso_cta_button_text', 'option' ) ?: 'Criar Perfil Gratuito' ); ?>
                <img src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/assets/images/icon-arrow-right.svg" width="24" height="24" alt="" />
            </a>
        </div>
    </section>
</main>

<?php get_footer(); ?>
