<?php
/**
 * Single concurso detail page.
 *
 * Free layout: Figma 6001:29632
 * Pro layout:   Figma 6001:26862
 *
 * @package Eyes_Child
 */

get_header();

$can_view_pro = function_exists( 'eyes_user_can_view_concurso_pro_content' ) && eyes_user_can_view_concurso_pro_content();
$back_url     = get_post_type_archive_link( 'concurso' ) ?: home_url( '/concurso/' );
$status_map   = function_exists( 'eyes_get_concurso_status_labels' ) ? eyes_get_concurso_status_labels() : array();
$detail_post_id_for_state = 0;
$consult_submitted        = false;

if ( have_posts() ) {
	the_post();
	$detail_post_id_for_state = get_the_ID();
	rewind_posts();
	$consult_submitted = is_user_logged_in() && eyes_has_consult_request( $detail_post_id_for_state );
}
?>

<main class="concurso-detail-main">
	<div class="site-layout site-layout--top-50-bottom-100">
		<a class="concurso-detail-back" href="<?php echo esc_url( $back_url ); ?>">
			<span class="concurso-detail-back__icon" aria-hidden="true">
				<img src="<?php echo esc_url( eyes_asset_uri( 'images/icon-arrow-left.svg' ) ); ?>" width="16" height="16" alt="" />
			</span>
			<?php esc_html_e( 'Voltar', 'eyes-child' ); ?>
		</a>

		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();

				$detail_post_id = get_the_ID();
				$title          = get_field( 'concurso_title' ) ?: get_the_title();
				$entity         = get_field( 'concurso_entity' );
				$notice         = get_field( 'concurso_notice_number' );
				$status_raw     = get_field( 'concurso_status' );
				$status_label   = isset( $status_map[ $status_raw ] ) ? $status_map[ $status_raw ] : $status_raw;
				$value          = eyes_get_concurso_value_display( $detail_post_id );
				$deadline       = eyes_format_concurso_deadline( eyes_get_concurso_meta_value( $detail_post_id, 'concurso_deadline' ) );
				$region_label   = eyes_get_concurso_detail_region_label( $detail_post_id );
				$pme_label      = eyes_get_concurso_detail_pme_label( $detail_post_id );
				$financing      = eyes_get_concurso_detail_financing_label( $detail_post_id );
				$sector         = eyes_get_concurso_detail_sector_label( $detail_post_id );
				$audience_tags  = eyes_get_concurso_detail_audience_tags( $detail_post_id );
				$details        = get_field( 'concurso_details_content' );
				$apply_link     = get_field( 'concurso_apply_button_link' );
				$apply_href     = is_user_logged_in() && $apply_link ? $apply_link : eyes_login_url( get_permalink( $detail_post_id ) );
				$has_applied    = is_user_logged_in() && eyes_has_applied_concurso( $detail_post_id );
				$has_consult    = is_user_logged_in() && eyes_has_consult_request( $detail_post_id );
				$hero_stats_class = 'concurso-detail-hero__stats' . ( $can_view_pro ? ' concurso-detail-hero__stats--pro' : '' );
				?>
				<div class="concurso-detail-stack">
					<?php
					get_template_part(
						'template-parts/concurso',
						'consult-success',
						array(
							'hidden' => ! $has_consult,
						)
					);
					?>

					<article class="concurso-detail-hero" aria-labelledby="concurso-detail-title">
						<div class="concurso-detail-hero__head">
							<div class="concurso-detail-hero__title-block">
								<h1 class="concurso-detail-hero__title" id="concurso-detail-title">
									<?php echo esc_html( $title ); ?>
								</h1>
								<?php if ( $entity || $notice ) : ?>
									<p class="concurso-detail-hero__meta-line">
										<?php if ( $entity ) : ?>
											<span><?php echo esc_html( $entity ); ?></span>
										<?php endif; ?>
										<?php if ( $entity && $notice ) : ?>
											<span class="concurso-detail-hero__meta-divider" aria-hidden="true"></span>
										<?php endif; ?>
										<?php if ( $notice ) : ?>
											<span><?php echo esc_html( $notice ); ?></span>
										<?php endif; ?>
									</p>
								<?php endif; ?>
							</div>
							<?php if ( $status_label ) : ?>
								<span class="tag concurso-detail-hero__tag <?php echo esc_attr( eyes_get_concurso_status_tag_class( $status_raw ) ); ?>"><?php echo esc_html( $status_label ); ?></span>
							<?php endif; ?>
						</div>

						<dl class="<?php echo esc_attr( $hero_stats_class ); ?>">
							<div>
								<dt class="concurso-detail-hero__stat-label"><?php esc_html_e( 'Valor', 'eyes-child' ); ?></dt>
								<dd class="concurso-detail-hero__stat-value"><?php echo esc_html( eyes_concurso_detail_display_value( $value ) ); ?></dd>
							</div>
							<div>
								<dt class="concurso-detail-hero__stat-label"><?php esc_html_e( 'Prazo', 'eyes-child' ); ?></dt>
								<dd class="concurso-detail-hero__stat-value"><?php echo esc_html( eyes_concurso_detail_display_value( $deadline ) ); ?></dd>
							</div>
							<div>
								<dt class="concurso-detail-hero__stat-label"><?php esc_html_e( 'Região', 'eyes-child' ); ?></dt>
								<dd class="concurso-detail-hero__stat-value"><?php echo esc_html( eyes_concurso_detail_display_value( $region_label ) ); ?></dd>
							</div>
							<div>
								<dt class="concurso-detail-hero__stat-label"><?php esc_html_e( 'Tipos de PME', 'eyes-child' ); ?></dt>
								<dd class="concurso-detail-hero__stat-value"><?php echo esc_html( eyes_concurso_detail_display_value( $pme_label ) ); ?></dd>
							</div>
							<?php if ( $can_view_pro ) : ?>
								<div>
									<dt class="concurso-detail-hero__stat-label"><?php esc_html_e( 'Tipo de financiamento', 'eyes-child' ); ?></dt>
									<dd class="concurso-detail-hero__stat-value"><?php echo esc_html( eyes_concurso_detail_display_value( $financing ) ); ?></dd>
								</div>
								<div>
									<dt class="concurso-detail-hero__stat-label"><?php esc_html_e( 'Setor de atividade', 'eyes-child' ); ?></dt>
									<dd class="concurso-detail-hero__stat-value"><?php echo esc_html( eyes_concurso_detail_display_value( $sector ) ); ?></dd>
								</div>
							<?php endif; ?>
						</dl>

						<hr class="concurso-detail-hero__rule" aria-hidden="true" />

						<div class="concurso-detail-hero__actions">
							<a
								class="concurso-detail-hero__btn-primary"
								href="<?php echo esc_url( $apply_href ); ?>"
								data-mark-applied
								data-applied="<?php echo $has_applied ? 'true' : 'false'; ?>"
								data-concurso-id="<?php echo esc_attr( (string) $detail_post_id ); ?>"
								data-login-url="<?php echo esc_url( eyes_login_url( get_permalink( $detail_post_id ) ) ); ?>"
								<?php echo $apply_link && is_user_logged_in() ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
							>
								<?php echo $has_applied ? esc_html__( 'Candidatura registada', 'eyes-child' ) : esc_html__( 'Candidatar', 'eyes-child' ); ?>
							</a>
							<?php
							get_template_part(
								'template-parts/concurso',
								'favourite-btn',
								array(
									'post_id'      => $detail_post_id,
									'button_class' => 'concurso-detail-hero__btn-save',
									'label'        => __( 'Guardar', 'eyes-child' ),
									'show_text'    => true,
								)
							);
							?>
						</div>
					</article>

					<?php if ( $can_view_pro ) : ?>
						<?php
						get_template_part(
							'template-parts/concurso-detail-pro-sections',
							null,
							array(
								'post_id'       => $detail_post_id,
								'details'       => $details,
								'audience_tags' => $audience_tags,
							)
						);
						?>
					<?php else : ?>
						<div class="concurso-detail-grid concurso-detail-grid--free">
							<section class="concurso-detail-panel concurso-detail-panel--details" aria-labelledby="concurso-detail-details-title">
								<h2 class="concurso-detail-panel__title" id="concurso-detail-details-title">
									<?php esc_html_e( 'Detalhes', 'eyes-child' ); ?>
								</h2>
								<div class="concurso-detail-panel__text">
									<?php
									if ( is_array( $details ) && ! empty( $details['value'] ) ) {
										echo wp_kses_post( $details['value'] );
									} elseif ( is_string( $details ) && '' !== trim( $details ) ) {
										echo wp_kses_post( $details );
									}
									?>
								</div>
							</section>

							<div class="concurso-detail-panel concurso-detail-panel--aside">
								<div class="concurso-detail-info-row">
									<div class="concurso-detail-info-card">
										<p class="concurso-detail-info-card__label"><?php esc_html_e( 'Tipo de financiamento', 'eyes-child' ); ?></p>
										<p class="concurso-detail-info-card__value"><?php echo esc_html( eyes_concurso_detail_display_value( $financing ) ); ?></p>
									</div>
									<div class="concurso-detail-info-card">
										<p class="concurso-detail-info-card__label"><?php esc_html_e( 'Setor de atividade', 'eyes-child' ); ?></p>
										<p class="concurso-detail-info-card__value"><?php echo esc_html( eyes_concurso_detail_display_value( $sector ) ); ?></p>
									</div>
								</div>

								<section class="concurso-detail-panel concurso-detail-panel--audience" aria-labelledby="concurso-detail-audience-title">
									<h2 class="concurso-detail-panel__title" id="concurso-detail-audience-title">
										<?php esc_html_e( 'Público-Alvo', 'eyes-child' ); ?>
									</h2>
									<div class="concurso-detail-tags">
										<?php if ( ! empty( $audience_tags ) ) : ?>
											<?php foreach ( $audience_tags as $audience_tag ) : ?>
												<span class="tag tag--audience"><?php echo esc_html( $audience_tag ); ?></span>
											<?php endforeach; ?>
										<?php else : ?>
											<span class="concurso-detail-tags__empty"><?php echo esc_html( eyes_concurso_detail_display_value( '' ) ); ?></span>
										<?php endif; ?>
									</div>
								</section>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<?php
			endwhile;
		endif;
		?>
	</div>

	<section class="concurso-detail-consult" aria-labelledby="concurso-detail-consult-title">
		<div class="section-wave section-wave--dark-strip-top" aria-hidden="true"></div>
		<div class="concurso-detail-consult__band">
			<div class="concurso-detail-consult__inner">
				<div class="concurso-detail-consult__copy">
					<h2 class="concurso-detail-consult__title" id="concurso-detail-consult-title">
						<span class="concurso-detail-consult__title-highlight"><?php esc_html_e( 'Precisa de apoio', 'eyes-child' ); ?></span>
						<?php esc_html_e( ' na candidatura ao financiamento?', 'eyes-child' ); ?>
					</h2>
					<p class="concurso-detail-consult__text">
						<?php esc_html_e( 'Os nossos consultores especializados ajudam-no a maximizar as suas hipóteses de aprovação.', 'eyes-child' ); ?>
					</p>
				</div>
				<button
					type="button"
					class="concurso-detail-consult__btn"
					data-consult-request
					data-concurso-id="<?php echo esc_attr( (string) $detail_post_id_for_state ); ?>"
					data-login-url="<?php echo esc_url( eyes_login_url( get_permalink( $detail_post_id_for_state ) ) ); ?>"
					<?php echo $consult_submitted ? ' disabled aria-disabled="true"' : ''; ?>
				>
					<span class="concurso-detail-consult__btn-label">
						<?php echo $consult_submitted ? esc_html__( 'Pedido enviado', 'eyes-child' ) : esc_html__( 'Quero ser contactado', 'eyes-child' ); ?>
					</span>
					<img src="<?php echo esc_url( eyes_asset_uri( 'images/icon-phone.svg' ) ); ?>" width="20" height="20" alt="" aria-hidden="true" />
				</button>
			</div>
		</div>
		<div class="section-wave section-wave--dark-strip-bottom" aria-hidden="true"></div>
	</section>

	<?php if ( ! $can_view_pro ) : ?>
		<?php
		get_template_part(
			'template-parts/dashboard',
			'pro-cta',
			array(
				'title_id' => 'concurso-detail-pro-cta-title',
			)
		);
		?>
	<?php endif; ?>
</main>

<?php
get_footer();
