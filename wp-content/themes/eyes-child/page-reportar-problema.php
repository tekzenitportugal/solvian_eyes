<?php
/**
 * Report a problem page (slug: reportar-problema).
 *
 * Page content is read from page-level ACF fields (same pattern as front-page.php).
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

$report_error   = '';
$report_success = false;

$report_defaults = function_exists( 'eyes_report_problem_default_fields' )
	? eyes_report_problem_default_fields()
	: array();

if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) && isset( $_POST['eyes_report_problem'] ) ) {
	$result = eyes_process_report_problem_form();

	if ( true === $result ) {
		$report_success = true;
	} elseif ( is_wp_error( $result ) ) {
		$report_error = $result->get_error_message();
	}
}

$report_page_title              = eyes_report_acf_text( 'report_page_title', $report_defaults['report_page_title'] ?? '' );
$report_page_lead               = eyes_report_acf_text( 'report_page_lead', $report_defaults['report_page_lead'] ?? '' );
$report_title_label             = eyes_report_acf_text( 'report_title_label', $report_defaults['report_title_label'] ?? '' );
$report_title_placeholder       = eyes_report_acf_text( 'report_title_placeholder', $report_defaults['report_title_placeholder'] ?? '' );
$report_description_label       = eyes_report_acf_text( 'report_description_label', $report_defaults['report_description_label'] ?? '' );
$report_description_placeholder = eyes_report_acf_text( 'report_description_placeholder', $report_defaults['report_description_placeholder'] ?? '' );
$report_submit_label            = eyes_report_acf_text( 'report_submit_label', $report_defaults['report_submit_label'] ?? '' );
$report_success_message         = eyes_report_acf_text( 'report_success_message', $report_defaults['report_success_message'] ?? '' );

get_header();
?>

<main class="report-page">
	<div class="site-layout site-layout--top-24">
		<section class="report-panel" aria-labelledby="report-title">
			<h1 class="report-panel__title" id="report-title">
				<?php echo esc_html( $report_page_title ); ?>
			</h1>
			<?php if ( $report_page_lead ) : ?>
				<p class="report-panel__lead"><?php echo esc_html( $report_page_lead ); ?></p>
			<?php endif; ?>

			<?php if ( $report_success ) : ?>
				<p class="report-panel__notice report-panel__notice--success" role="status">
					<?php echo esc_html( $report_success_message ); ?>
				</p>
			<?php else : ?>
				<?php if ( $report_error ) : ?>
					<p class="report-panel__notice report-panel__notice--error" role="alert"><?php echo esc_html( $report_error ); ?></p>
				<?php endif; ?>

				<form class="report-panel__form" method="post" action="<?php echo esc_url( eyes_report_problem_url() ); ?>">
					<?php wp_nonce_field( 'eyes_report_problem', 'eyes_report_nonce' ); ?>
					<input type="hidden" name="eyes_report_problem" value="1" />
					<input type="hidden" name="report_page_url" value="<?php echo esc_attr( eyes_report_problem_url() ); ?>" />

					<div class="report-panel__fields">
						<div class="form-field">
							<label class="form-field__label" for="report-title-input">
								<?php echo esc_html( $report_title_label ); ?>*
							</label>
							<input
								class="form-field__control"
								id="report-title-input"
								name="report_title"
								type="text"
								value="<?php echo isset( $_POST['report_title'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['report_title'] ) ) ) : ''; ?>"
								placeholder="<?php echo esc_attr( $report_title_placeholder ); ?>"
								required
							/>
						</div>

						<div class="form-field">
							<label class="form-field__label" for="report-desc">
								<?php echo esc_html( $report_description_label ); ?>*
							</label>
							<textarea
								class="form-field__control report-panel__textarea"
								id="report-desc"
								name="report_description"
								rows="8"
								placeholder="<?php echo esc_attr( $report_description_placeholder ); ?>"
								required
							><?php echo isset( $_POST['report_description'] ) ? esc_textarea( sanitize_textarea_field( wp_unslash( $_POST['report_description'] ) ) ) : ''; ?></textarea>
						</div>
					</div>

					<button type="submit" class="btn btn--primary report-panel__submit">
						<?php echo esc_html( $report_submit_label ); ?>
					</button>
				</form>
			<?php endif; ?>
		</section>
	</div>
</main>

<?php
get_footer();
