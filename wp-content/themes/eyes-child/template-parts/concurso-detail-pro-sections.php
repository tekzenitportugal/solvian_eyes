<?php
/**
 * Pro-only concurso detail sections (Figma 6001:26862).
 *
 * Only included when eyes_user_can_view_concurso_pro_content() is true.
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'eyes_user_can_view_concurso_pro_content' ) || ! eyes_user_can_view_concurso_pro_content() ) {
	return;
}

$post_id          = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
$details          = isset( $args['details'] ) ? $args['details'] : null;
$legal_intro      = eyes_get_concurso_pro_field( 'concurso_legal_intro', $post_id );
$legal_points     = eyes_get_concurso_pro_field( 'concurso_legal_points', $post_id );
$documents        = eyes_get_concurso_pro_field( 'concurso_documents', $post_id );
$forms            = eyes_get_concurso_pro_field( 'concurso_application_forms', $post_id );
$legislation      = eyes_get_concurso_pro_field( 'concurso_legislation', $post_id );
$audience_tags    = isset( $args['audience_tags'] ) && is_array( $args['audience_tags'] ) ? $args['audience_tags'] : array();
$has_legal        = ( is_string( $legal_intro ) && '' !== trim( $legal_intro ) ) || ( is_array( $legal_points ) && $legal_points );
$has_documents    = is_array( $documents ) && $documents;
$has_forms        = is_array( $forms ) && $forms;
$has_legislation  = is_array( $legislation ) && $legislation;
?>

<div class="concurso-detail-grid concurso-detail-grid--pro">
	<div class="concurso-detail-grid__col concurso-detail-grid__col--main">
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

		<?php if ( $has_legal ) : ?>
			<section class="concurso-detail-panel concurso-detail-panel--legal" aria-labelledby="concurso-detail-legal-title">
				<h2 class="concurso-detail-panel__title" id="concurso-detail-legal-title">
					<?php esc_html_e( 'Base legal deste concurso', 'eyes-child' ); ?>
				</h2>
				<?php if ( is_string( $legal_intro ) && '' !== trim( $legal_intro ) ) : ?>
					<p class="concurso-detail-panel__text concurso-detail-panel__text--intro">
						<?php echo esc_html( $legal_intro ); ?>
					</p>
				<?php endif; ?>
				<?php if ( is_array( $legal_points ) && $legal_points ) : ?>
					<ul class="concurso-detail-legal-list">
						<?php foreach ( $legal_points as $legal_point ) : ?>
							<?php
							$point_text = is_array( $legal_point ) && ! empty( $legal_point['point'] ) ? $legal_point['point'] : '';
							if ( '' === trim( (string) $point_text ) ) {
								continue;
							}
							?>
							<li class="concurso-detail-legal-list__item">
								<span class="concurso-detail-legal-list__bullet" aria-hidden="true"></span>
								<span><?php echo esc_html( $point_text ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<?php if ( $has_documents ) : ?>
			<section class="concurso-detail-panel concurso-detail-panel--documents" aria-labelledby="concurso-detail-documents-title">
				<h2 class="concurso-detail-panel__title" id="concurso-detail-documents-title">
					<?php esc_html_e( 'Documentos', 'eyes-child' ); ?>
				</h2>
				<ul class="concurso-detail-file-list concurso-detail-file-list--documents">
					<?php foreach ( $documents as $document ) : ?>
						<?php
						if ( ! is_array( $document ) ) {
							continue;
						}

						$doc_label     = ! empty( $document['label'] ) ? $document['label'] : eyes_get_acf_file_label( $document['file'] ?? '', '' );
						$doc_url       = eyes_get_acf_file_url( $document['file'] ?? '' );
						$is_complement = ! empty( $document['is_complement'] );
						$doc_item_class = 'concurso-detail-file-list__item' . ( $is_complement ? ' concurso-detail-file-list__item--complement' : '' );
						?>
						<li class="<?php echo esc_attr( $doc_item_class ); ?>">
							<div class="concurso-detail-file-list__info">
								<span class="concurso-detail-file-list__icon" aria-hidden="true">
									<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M11.667 2.5H5.833C5.17 2.5 4.625 3.045 4.625 3.708V16.292C4.625 16.955 5.17 17.5 5.833 17.5H14.167C14.83 17.5 15.375 16.955 15.375 16.292V6.208L11.667 2.5Z" stroke="currentColor" stroke-width="1.25" stroke-linejoin="round"/>
										<path d="M11.667 2.5V6.208H15.375" stroke="currentColor" stroke-width="1.25" stroke-linejoin="round"/>
									</svg>
								</span>
								<span class="concurso-detail-file-list__label"><?php echo esc_html( $doc_label ); ?></span>
							</div>
							<div class="concurso-detail-file-list__actions">
								<?php if ( $is_complement ) : ?>
									<span class="concurso-detail-file-list__badge"><?php esc_html_e( 'Complemento', 'eyes-child' ); ?></span>
								<?php endif; ?>
								<?php if ( $doc_url ) : ?>
									<a
										class="concurso-detail-file-list__download"
										href="<?php echo esc_url( $doc_url ); ?>"
										download
										target="_blank"
										rel="noopener noreferrer"
										aria-label="<?php echo esc_attr( sprintf( __( 'Download %s', 'eyes-child' ), $doc_label ) ); ?>"
									>
										<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
											<path d="M10 2.5V12.5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"/>
											<path d="M6.667 9.167L10 12.5L13.333 9.167" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M3.333 17.5H16.667" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"/>
										</svg>
									</a>
								<?php else : ?>
									<span class="concurso-detail-file-list__download concurso-detail-file-list__download--disabled" aria-hidden="true">
										<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M10 2.5V12.5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"/>
											<path d="M6.667 9.167L10 12.5L13.333 9.167" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M3.333 17.5H16.667" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"/>
										</svg>
									</span>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>
	</div>

	<div class="concurso-detail-grid__col concurso-detail-grid__col--aside">
		<section class="concurso-detail-panel concurso-detail-panel--audience" aria-labelledby="concurso-detail-audience-title-pro">
			<h2 class="concurso-detail-panel__title" id="concurso-detail-audience-title-pro">
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

		<?php if ( $has_forms ) : ?>
			<section class="concurso-detail-panel concurso-detail-panel--forms" aria-labelledby="concurso-detail-forms-title">
				<h2 class="concurso-detail-panel__title" id="concurso-detail-forms-title">
					<?php esc_html_e( 'Formulários de candidatura', 'eyes-child' ); ?>
				</h2>
				<ul class="concurso-detail-file-list concurso-detail-file-list--forms">
					<?php foreach ( $forms as $form ) : ?>
						<?php
						if ( ! is_array( $form ) ) {
							continue;
						}

						$form_label = ! empty( $form['label'] ) ? $form['label'] : eyes_get_acf_file_label( $form['file'] ?? '', '' );
						$form_url   = eyes_get_acf_file_url( $form['file'] ?? '' );

						if ( '' === trim( (string) $form_label ) ) {
							continue;
						}
						?>
						<li class="concurso-detail-file-list__item">
							<div class="concurso-detail-file-list__info">
								<span class="concurso-detail-file-list__icon concurso-detail-file-list__icon--form" aria-hidden="true">
									<img src="<?php echo esc_url( eyes_asset_uri( 'images/icon-form-word.svg' ) ); ?>" width="32" height="32" alt="" />
								</span>
								<span class="concurso-detail-file-list__label"><?php echo esc_html( $form_label ); ?></span>
							</div>
							<div class="concurso-detail-file-list__actions">
								<?php if ( $form_url ) : ?>
									<a
										class="concurso-detail-file-list__download"
										href="<?php echo esc_url( $form_url ); ?>"
										download
										target="_blank"
										rel="noopener noreferrer"
										aria-label="<?php echo esc_attr( sprintf( __( 'Download %s', 'eyes-child' ), $form_label ) ); ?>"
									>
										<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
											<path d="M10 2.5V12.5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"/>
											<path d="M6.667 9.167L10 12.5L13.333 9.167" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M3.333 17.5H16.667" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"/>
										</svg>
									</a>
								<?php else : ?>
									<span class="concurso-detail-file-list__download concurso-detail-file-list__download--disabled" aria-hidden="true">
										<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M10 2.5V12.5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"/>
											<path d="M6.667 9.167L10 12.5L13.333 9.167" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M3.333 17.5H16.667" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"/>
										</svg>
									</span>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

		<?php if ( $has_legislation ) : ?>
			<section class="concurso-detail-panel concurso-detail-panel--legislation" aria-labelledby="concurso-detail-legislation-title">
				<h2 class="concurso-detail-panel__title" id="concurso-detail-legislation-title">
					<?php esc_html_e( 'Legislação associada', 'eyes-child' ); ?>
				</h2>
				<div class="concurso-detail-legislation-list">
					<?php foreach ( $legislation as $law_item ) : ?>
						<?php
						if ( ! is_array( $law_item ) ) {
							continue;
						}

						$law_title   = ! empty( $law_item['title'] ) ? $law_item['title'] : '';
						$law_summary = ! empty( $law_item['summary'] ) ? $law_item['summary'] : '';

						if ( '' === trim( (string) $law_title ) && '' === trim( (string) $law_summary ) ) {
							continue;
						}
						?>
						<article class="concurso-detail-legislation-card">
							<?php if ( $law_title ) : ?>
								<h3 class="concurso-detail-legislation-card__title"><?php echo esc_html( $law_title ); ?></h3>
							<?php endif; ?>
							<?php if ( $law_summary ) : ?>
								<p class="concurso-detail-legislation-card__summary"><?php echo esc_html( $law_summary ); ?></p>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>
	</div>
</div>
