<?php
/**
 * Front page template.
 *
 * All front-page static content is read from page-level ACF fields.
 * Repeated-looking sections use numbered ACF fields so ACF Free is enough.
 *
 * @package Eyes
 */

get_header();

$hero_primary_link = eyes_acf_link( 'hero_primary_link', '', '' );
$hero_secondary_link = eyes_acf_link( 'hero_secondary_link', '', '' );
$cta_mid_link = eyes_acf_link( 'cta_mid_link', '', '' );
$opportunities_post_type = eyes_acf_text( 'opportunities_post_type', 'post' );
$opportunities_category_slug = eyes_acf_text( 'opportunities_category_slug', '' );
$opportunities_posts_per_page = absint( eyes_acf_text( 'opportunities_posts_per_page', '4' ) );
$opportunities_query_args = [
	'post_type' => $opportunities_post_type,
	'posts_per_page' => $opportunities_posts_per_page ? $opportunities_posts_per_page : 4,
	'post_status' => 'publish',
	'ignore_sticky_posts' => true,
];

if ( 'post' === $opportunities_post_type && '' !== $opportunities_category_slug ) {
	$opportunities_query_args['category_name'] = $opportunities_category_slug;
}

$opportunities = new WP_Query( $opportunities_query_args );
?>
<main id="primary" class="site-main">
	<section class="hero" id="inicio">
		<div class="hero__inner site-layout">
			<div class="hero__content">
				<div class="hero__text-block">
					<p class="eyebrow"><?php echo esc_html( eyes_acf_text( 'hero_eyebrow', '' ) ); ?></p>
					<h1 class="hero__title">
						<span class="hero__title-line"><?php echo esc_html( eyes_acf_text( 'hero_title_line', '' ) ); ?></span>
						<span class="hero__title-accent"><?php echo esc_html( eyes_acf_text( 'hero_title_accent_1', '' ) ); ?></span>
						<span> </span>
						<span class="hero__title-accent"><?php echo esc_html( eyes_acf_text( 'hero_title_accent_2', '' ) ); ?></span>
						<span> </span>
						<span class="hero__title-accent"><?php echo esc_html( eyes_acf_text( 'hero_title_accent_3', '' ) ); ?></span>
						<span><?php echo esc_html( eyes_acf_text( 'hero_title_suffix', '' ) ); ?></span>
					</h1>
					<p class="hero__lead"><?php echo esc_html( eyes_acf_text( 'hero_lead', '' ) ); ?></p>
				</div>
				<div class="hero__ctas">
					<a class="btn btn--hero-info" <?php echo eyes_acf_link_attrs( $hero_secondary_link ); ?>><?php echo esc_html( $hero_secondary_link['title'] ); ?></a>
					<?php if ( $hero_primary_link && ! is_user_logged_in() ) : ?>
	<a class="btn btn--hero-primary" <?php echo eyes_acf_link_attrs( $hero_primary_link ); ?>>
		<?php echo esc_html( $hero_primary_link['title'] ); ?>

		<?php $hero_primary_icon = eyes_acf_image_url( 'hero_primary_link_icon' ); ?>
		<?php if ( $hero_primary_icon ) : ?>
			<img src="<?php echo esc_url( $hero_primary_icon ); ?>" width="20" height="20" alt="">
		<?php endif; ?>
	</a>
<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="hero__partners site-layout">
			<p class="hero__partners-label"><?php echo esc_html( eyes_acf_text( 'hero_partners_label', '' ) ); ?></p>
			<div class="hero__partners-marquee" role="region" aria-label="<?php echo esc_attr( eyes_acf_text( 'partner_logos_aria_label', '' ) ); ?>">
				<div class="hero__partners-marquee-viewport">
				<div class="hero__partners-marquee-track">

<?php for ( $set = 1; $set <= 4; $set++ ) : ?>
	<ul class="hero__partners-logos" <?php echo $set > 1 ? 'aria-hidden="true"' : ''; ?>>
		<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
			<?php $logo_image = eyes_acf_image_url( "partner_logo_{$i}_image" ); ?>
			<?php if ( $logo_image ) : ?>
				<li>
					<img
						class="hero__partners-logo"
						src="<?php echo esc_url( $logo_image ); ?>"
						alt=""
					>
				</li>
			<?php endif; ?>
		<?php endfor; ?>
	</ul>
<?php endfor; ?>

</div>
				</div>
			</div>
		</div>
	</section>

	<div class="section-wave section-wave--hero-end" aria-hidden="true"></div>

	<section class="features" id="funcionalidades" aria-labelledby="features-heading">
		<h2 id="features-heading" class="visually-hidden"><?php echo esc_html( eyes_acf_text( 'features_accessible_heading', '' ) ); ?></h2>
		<div class="site-layout">
			<div class="features__grid">
				<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
					<?php
					$feature_icon = eyes_acf_image_url( "feature_{$i}_icon" );
					$feature_title = eyes_acf_text( "feature_{$i}_title", '' );
					$feature_description = eyes_acf_text( "feature_{$i}_description", '' );
					?>
					<?php if ( $feature_icon || $feature_title || $feature_description ) : ?>
						<div class="features__cell">
							<div class="features__icon-wrap">
								<?php if ( $feature_icon ) : ?>
									<img class="features__icon" src="<?php echo esc_url( $feature_icon ); ?>" width="64" height="64" alt="">
								<?php endif; ?>
							</div>
							<div class="features__copy">
								<p class="feature-title"><?php echo esc_html( $feature_title ); ?></p>
								<p class="feature-text"><?php echo esc_html( $feature_description ); ?></p>
							</div>
						</div>
					<?php endif; ?>
				<?php endfor; ?>
			</div>
		</div>
	</section>

	<div class="section-wave section-wave--features-bottom" aria-hidden="true"></div>

	<section class="opps" id="oportunidades" aria-labelledby="opps-title">
		<div class="opps__decor opps__decor--a" aria-hidden="true"></div>
		<div class="opps__decor opps__decor--b" aria-hidden="true"></div>
		<div class="site-layout">
			<div class="opps__inner">
				<h2 class="opps__title" id="opps-title"><?php echo esc_html( eyes_acf_text( 'opportunities_title', '' ) ); ?></h2>
				<div class="opps__list-wrap">
					<div class="opps__list">
						<?php if ( $opportunities->have_posts() ) : ?>
							<?php while ( $opportunities->have_posts() ) : $opportunities->the_post(); ?>
								<?php
								// Use the Concurso ACF field names
								$opportunity_status   = function_exists( 'get_field' ) ? get_field( 'concurso_status', get_the_ID() ) : '';
								$opportunity_status_labels = function_exists( 'eyes_get_concurso_status_labels' ) ? eyes_get_concurso_status_labels() : array();
								$opportunity_status_label  = isset( $opportunity_status_labels[ $opportunity_status ] ) ? $opportunity_status_labels[ $opportunity_status ] : $opportunity_status;
								$opportunity_status_tag_cls = function_exists( 'eyes_get_concurso_status_tag_class' )
									? eyes_get_concurso_status_tag_class( $opportunity_status )
									: 'tag--aberto';
								$opportunity_deadline = function_exists( 'get_field' ) ? get_field( 'concurso_deadline', get_the_ID() ) : '';
								$opportunity_entity   = function_exists( 'get_field' ) ? get_field( 'concurso_entity', get_the_ID() ) : '';
								$opportunity_notice   = function_exists( 'get_field' ) ? get_field( 'concurso_notice_number', get_the_ID() ) : '';
								// Prefer ACF details content, then excerpt, then post content
								$acf_details = function_exists( 'get_field' ) ? get_field( 'concurso_details_content', get_the_ID() ) : '';
								$excerpt_source = '';
								if ( $acf_details ) {
									if ( is_array( $acf_details ) ) {
										$acf_text = '';
										if ( ! empty( $acf_details['value'] ) ) {
											$acf_text = $acf_details['value'];
										} elseif ( ! empty( $acf_details['text'] ) ) {
											$acf_text = $acf_details['text'];
										} else {
											$acf_text = wp_json_encode( $acf_details );
										}
									} else {
										$acf_text = $acf_details;
									}
									$opportunity_excerpt = wp_trim_words( wp_strip_all_tags( $acf_text ), 32 );
									$excerpt_source = 'acf';
								} elseif ( has_excerpt() ) {
									$opportunity_excerpt = get_the_excerpt();
									$excerpt_source = 'excerpt';
								} else {
									$opportunity_excerpt = wp_trim_words( get_the_content(), 32 );
									$excerpt_source = 'content';
								}
								// Debug helper
								echo '<!-- DEBUG excerpt_source=' . esc_attr( $excerpt_source ) . ' len=' . strlen( trim( strip_tags( $opportunity_excerpt ) ) ) . ' -->';
								$deadline_prefix      = eyes_acf_text( 'opportunities_deadline_prefix', '' );
								?>
								<article class="opportunity-card">
									<div class="opportunity-card__body">
										<div class="opportunity-card__meta">
											<?php if ( $opportunity_status_label ) : ?>
												<span class="tag <?php echo esc_attr( $opportunity_status_tag_cls ); ?>"><?php echo esc_html( $opportunity_status_label ); ?></span>
											<?php endif; ?>
											<?php
											$opportunity_deadline_raw = $opportunity_deadline;
											if ( $opportunity_deadline_raw ) {
												$formatted_deadline = $opportunity_deadline_raw;
												// Handle common ACF date formats: Ymd or Y-m-d
												if ( preg_match( '/^\d{8}$/', $opportunity_deadline_raw ) ) {
													$dt = DateTime::createFromFormat( 'Ymd', $opportunity_deadline_raw );
													if ( $dt ) {
														$formatted_deadline = $dt->format( 'd/m/Y' );
													}
												} elseif ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $opportunity_deadline_raw ) ) {
													$dt = DateTime::createFromFormat( 'Y-m-d', $opportunity_deadline_raw );
													if ( $dt ) {
														$formatted_deadline = $dt->format( 'd/m/Y' );
													}
												}
												$prefix = $deadline_prefix ? $deadline_prefix : 'Prazo';
												?>
												<span class="opportunity-card__deadline"><?php echo esc_html( $prefix . ': ' . $formatted_deadline ); ?></span>
											<?php } ?>
										</div>										
											
											<h3 class="opportunity-card__name"><?php echo esc_html( $opportunity_notice ); ?> - <?php the_title(); ?> </h3>
											
										
										<p class="opportunity-card__excerpt"><?php echo esc_html( $opportunity_excerpt ); ?></p>
									</div>
									<a class="btn btn--outline-muted" href="<?php the_permalink(); ?>"><?php echo esc_html( eyes_acf_text( 'opportunities_card_button_label', 'Saber Mais' ) ); ?></a>
								</article>
							<?php endwhile; ?>
							<?php wp_reset_postdata(); ?>
						<?php else : ?>
							<p class="opps__empty"><?php echo esc_html( eyes_acf_text( 'opportunities_empty_message', 'Nenhuma oportunidade encontrada.' ) ); ?></p>
						<?php endif; ?>
					</div>
					<div class="opps__fade" aria-hidden="true"></div>
				</div>
			</div>
		</div>

		<section class="cta-mid" aria-labelledby="cta-mid-title">
			<div class="cta-mid__inner">
				<h2 class="cta-mid__title" id="cta-mid-title"><?php echo esc_html( eyes_acf_text( 'cta_mid_title', '' ) ); ?></h2>
				<a class="btn btn--cta-mid" <?php echo eyes_acf_link_attrs( $cta_mid_link ); ?>><?php echo esc_html( $cta_mid_link['title'] ); ?></a>
			</div>
		</section>
	</section>

	<div class="section-wave section-wave--value-top" aria-hidden="true"></div>

	<section class="value" id="valor" aria-labelledby="value-title">
		<div class="site-layout">
			<h2 class="value__title" id="value-title"><?php echo esc_html( eyes_acf_text( 'value_title', '' ) ); ?></h2>
			<div class="value__track">
				<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
					<?php
					$value_icon = eyes_acf_image_url( "value_card_{$i}_icon" );
					$value_title = eyes_acf_text( "value_card_{$i}_title", '' );
					$value_description = eyes_acf_text( "value_card_{$i}_description", '' );
					?>
					<?php if ( $value_icon || $value_title || $value_description ) : ?>
						<article class="value-card">
							<div class="value-card__icon-box">
								<?php if ( $value_icon ) : ?>
									<img class="value-card__icon" src="<?php echo esc_url( $value_icon ); ?>" width="48" height="48" alt="">
								<?php endif; ?>
							</div>
							<h3 class="value-card__heading"><?php echo esc_html( $value_title ); ?></h3>
							<p class="value-card__text"><?php echo esc_html( $value_description ); ?></p>
						</article>
					<?php endif; ?>
				<?php endfor; ?>
			</div>
		</div>
	</section>

	<div class="section-wave section-wave--about-top" aria-hidden="true"></div>

	<section class="about" id="sobre" aria-labelledby="about-title">
		<div class="about__intro site-layout">
			<p class="about__eyebrow"><?php echo esc_html( eyes_acf_text( 'about_eyebrow', '' ) ); ?></p>
			<h2 class="about__headline" id="about-title"><?php echo esc_html( eyes_acf_text( 'about_headline', '' ) ); ?></h2>
			<p class="about__desc"><?php echo esc_html( eyes_acf_text( 'about_description', '' ) ); ?></p>
		</div>
		<div class="about__cards site-layout">
			<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
				<?php
				$segment_title = eyes_acf_text( "about_card_{$i}_title", '' );
				$segment_description = eyes_acf_text( "about_card_{$i}_description", '' );
				$segment_link = eyes_acf_link( "about_card_{$i}_link", '', '' );
				$segment_background = eyes_acf_image_url( "about_card_{$i}_background_image" );
				$segment_mark = eyes_acf_image_url( "about_card_{$i}_mark_image" );
				?>
				<?php if ( $segment_title || $segment_description || $segment_background || $segment_mark || ! empty( $segment_link['url'] ) ) : ?>
					<a class="segment-card" <?php echo eyes_acf_link_attrs( $segment_link ); ?> aria-label="<?php echo esc_attr( trim( $segment_title . '. ' . $segment_description ) ); ?>">
						<?php if ( $segment_background ) : ?>
							<img class="segment-card__bg" src="<?php echo esc_url( $segment_background ); ?>" alt="" width="400" height="480">
						<?php endif; ?>
						<div class="segment-card__shade" aria-hidden="true"></div>
						<?php if ( $segment_mark ) : ?>
							<img class="segment-card__mark" src="<?php echo esc_url( $segment_mark ); ?>" width="80" height="80" alt="">
						<?php endif; ?>
						<div class="segment-card__foot">
							<h3 class="segment-card__label" aria-hidden="true"><?php echo esc_html( $segment_title ); ?></h3>
							<p class="segment-card__desc" aria-hidden="true"><?php echo esc_html( $segment_description ); ?></p>
						</div>
					</a>
				<?php endif; ?>
			<?php endfor; ?>
		</div>
	</section>

	<section class="contact" id="contacto" aria-label="<?php echo esc_attr( eyes_acf_text( 'contact_section_aria_label', '' ) ); ?>">
		<div class="contact-split">
			<div class="contact-split__map">
				<?php $contact_map = eyes_acf_image_url( 'contact_map_image' ); ?>
				<?php if ( $contact_map ) : ?>
					<img src="<?php echo esc_url( $contact_map ); ?>" width="1600" height="900" alt="<?php echo esc_attr( eyes_acf_text( 'contact_map_alt', '' ) ); ?>">
				<?php endif; ?>
			</div>
			<div class="contact-split__form-wrap">
				<h2 class="contact-split__heading"><?php echo esc_html( eyes_acf_text( 'contact_heading', '' ) ); ?></h2>
				<p class="contact-split__sub"><?php echo esc_html( eyes_acf_text( 'contact_subheading', '' ) ); ?></p>
				<?php echo do_shortcode( eyes_acf_text( 'contact_form_shortcode', '' ) ); ?>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
