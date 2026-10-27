<?php
/**
 * Concurso card used on archive and AJAX filter results.
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

$card_post_id    = get_the_ID();
$is_favourited   = eyes_is_favourite_concurso( $card_post_id );
$is_recommended  = function_exists( 'eyes_is_recommended_concurso' ) && eyes_is_recommended_concurso( $card_post_id );
$card_detail_url = eyes_concurso_detail_url( $card_post_id );
$card_variant    = get_query_var( 'eyes_concurso_card_variant', 'default' );

$status       = get_field( 'concurso_status' );
$deadline_raw = get_field( 'concurso_deadline' );
$deadline     = eyes_format_concurso_deadline( $deadline_raw );
$entity       = get_field( 'concurso_entity' );
$details      = get_field( 'concurso_details_content' );

if ( is_array( $details ) ) {
	$details_text = ! empty( $details['value'] ) ? $details['value'] : wp_json_encode( $details );
} else {
	$details_text = $details;
}

$region_label = function_exists( 'eyes_get_concurso_detail_region_label' )
	? eyes_get_concurso_detail_region_label( $card_post_id )
	: eyes_get_taxonomy_term_list( $card_post_id, 'regiao' );
$pme_label    = function_exists( 'eyes_get_concurso_detail_pme_label' )
	? eyes_get_concurso_detail_pme_label( $card_post_id )
	: eyes_get_taxonomy_term_list( $card_post_id, 'enquadramento_pme' );

$show_closing_tag = false;
if ( ! empty( $deadline_raw ) ) {
	try {
		$dt = false;
		if ( strlen( $deadline_raw ) === 8 ) {
			$dt = DateTime::createFromFormat( 'Ymd', $deadline_raw );
		} elseif ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $deadline_raw ) ) {
			$dt = DateTime::createFromFormat( 'Y-m-d', $deadline_raw );
		} elseif ( preg_match( '/^\d{1,2}\/\d{1,2}\/\d{4}$/', $deadline_raw ) ) {
			$dt = DateTime::createFromFormat( 'd/m/Y', $deadline_raw );
			if ( ! $dt ) {
				$dt = DateTime::createFromFormat( 'm/d/Y', $deadline_raw );
			}
		} else {
			$dt = new DateTime( $deadline_raw );
		}

		if ( $dt ) {
			$now  = new DateTime();
			$diff = $now->diff( $dt );
			if ( ! $diff->invert && $diff->days <= 30 ) {
				$show_closing_tag = true;
			}
		}
	} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
	}
}

$status_labels  = eyes_get_concurso_status_labels();
$status_label   = isset( $status_labels[ $status ] ) ? $status_labels[ $status ] : $status;
$status_tag_cls = function_exists( 'eyes_get_concurso_status_tag_class' )
	? eyes_get_concurso_status_tag_class( $status )
	: 'tag--aberto';

$card_btn_label = get_field( 'concurso_apply_button_text' ) ?: esc_html__( 'Ver Detalhes', 'eyes-child' );

$card_classes = array( 'concursos-card' );
if ( 'territorio' === $card_variant ) {
	$card_classes[] = 'concursos-card--territorio';
}
if ( $is_recommended ) {
	$card_classes[] = 'concursos-card--recommended';
}
?>
<article class="<?php echo esc_attr( implode( ' ', $card_classes ) ); ?>">
	<?php if ( 'territorio' === $card_variant ) : ?>
		<div class="concursos-card__title-row concursos-card__title-row--territorio">
			<h3 class="concursos-card__title"><?php echo esc_html( get_the_title() ); ?></h3>
			<div class="concursos-card__tags">
				<?php if ( $status_label ) : ?>
					<span class="tag tag--status <?php echo esc_attr( $status_tag_cls ); ?>"><?php echo esc_html( $status_label ); ?></span>
				<?php endif; ?>
				<?php if ( $show_closing_tag ) : ?>
					<span class="tag tag--info"><?php esc_html_e( 'fecha em breve', 'eyes-child' ); ?></span>
				<?php endif; ?>
			</div>
		</div>

		<div class="concursos-card__meta concursos-card__meta--territorio">
			<?php if ( $deadline ) : ?>
				<span class="concursos-card__meta-item">
					<img class="concursos-card__meta-icon" src="<?php echo esc_url( eyes_concursos_meta_icon_uri( 'deadline' ) ); ?>" width="18" height="18" alt="" />
					<span><?php echo esc_html( 'Prazo: ' . $deadline ); ?></span>
				</span>
			<?php endif; ?>

			<?php if ( $region_label ) : ?>
				<span class="concursos-card__meta-item">
					<img class="concursos-card__meta-icon" src="<?php echo esc_url( eyes_concursos_meta_icon_uri( 'location' ) ); ?>" width="18" height="18" alt="" />
					<span><?php echo esc_html( $region_label ); ?></span>
				</span>
			<?php endif; ?>

			<?php if ( $pme_label ) : ?>
				<span class="concursos-card__meta-item">
					<img class="concursos-card__meta-icon" src="<?php echo esc_url( eyes_concursos_meta_icon_uri( 'company' ) ); ?>" width="18" height="18" alt="" />
					<span><?php echo esc_html( $pme_label ); ?></span>
				</span>
			<?php endif; ?>
		</div>

		<div class="concursos-card__actions concursos-card__actions--territorio">
			<a class="concursos-card__btn-primary" href="<?php echo esc_url( $card_detail_url ); ?>">
				<?php echo esc_html( $card_btn_label ); ?>
			</a>
			<?php
			get_template_part(
				'template-parts/concurso',
				'favourite-btn',
				array(
					'post_id'      => $card_post_id,
					'button_class' => 'concursos-card__btn-icon',
				)
			);
			?>
		</div>
	<?php else : ?>
		<div class="concursos-card__head">
			<div class="concursos-card__title-row">
				<h3 class="concursos-card__title"><?php echo esc_html( get_the_title() ); ?></h3>
				<div class="concursos-card__tags">
					<?php if ( $status_label ) : ?>
						<span class="tag <?php echo esc_attr( $status_tag_cls ); ?>"><?php echo esc_html( $status_label ); ?></span>
					<?php endif; ?>
					<?php if ( $show_closing_tag ) : ?>
						<span class="tag tag--info"><?php esc_html_e( 'fecha em breve', 'eyes-child' ); ?></span>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( $entity ) : ?>
				<p class="concursos-card__issuer"><?php echo esc_html( $entity ); ?></p>
			<?php endif; ?>

			<?php if ( $details_text ) : ?>
				<p class="concursos-card__desc"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $details_text ), 40 ) ); ?></p>
			<?php endif; ?>

			<div class="concursos-card__meta">
				<?php if ( $deadline ) : ?>
					<span class="concursos-card__meta-item">
						<img class="concursos-card__meta-icon" src="<?php echo esc_url( eyes_concursos_meta_icon_uri( 'deadline' ) ); ?>" width="18" height="18" alt="" />
						<span><?php echo esc_html( 'Prazo: ' . $deadline ); ?></span>
					</span>
				<?php endif; ?>

				<?php if ( $region_label ) : ?>
					<span class="concursos-card__meta-item">
						<img class="concursos-card__meta-icon" src="<?php echo esc_url( eyes_concursos_meta_icon_uri( 'location' ) ); ?>" width="18" height="18" alt="" />
						<span><?php echo esc_html( $region_label ); ?></span>
					</span>
				<?php endif; ?>

				<?php if ( $pme_label ) : ?>
					<span class="concursos-card__meta-item">
						<img class="concursos-card__meta-icon" src="<?php echo esc_url( eyes_concursos_meta_icon_uri( 'company' ) ); ?>" width="18" height="18" alt="" />
						<span><?php echo esc_html( $pme_label ); ?></span>
					</span>
				<?php endif; ?>
			</div>
		</div>

		<hr class="concursos-card__rule" aria-hidden="true">

		<div class="concursos-card__actions">
			<a class="concursos-card__btn-primary" href="<?php echo esc_url( $card_detail_url ); ?>">
				<?php echo esc_html( $card_btn_label ); ?>
			</a>
			<?php
			get_template_part(
				'template-parts/concurso',
				'favourite-btn',
				array(
					'post_id'      => $card_post_id,
					'button_class' => 'concursos-card__btn-icon',
				)
			);
			?>
		</div>
	<?php endif; ?>
</article>
