<?php
/**
 * Shared Pro plan CTA block (dashboard, concurso detail, etc.).
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

$title_id   = ! empty( $args['title_id'] ) ? $args['title_id'] : 'dashboard-pro-cta-title';
$planos_url = home_url( '/planos/' );
?>
<section class="dashboard-pro-cta" data-dashboard-pro-cta aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
	<div class="dashboard-pro-cta__inner">
		<div class="dashboard-pro-cta__tags">
			<span class="dashboard-pro-cta__tag">
				<img src="<?php echo esc_url( eyes_asset_uri( 'images/icon-need.svg' ) ); ?>" width="18" height="18" alt="" aria-hidden="true" />
				<?php esc_html_e( 'Apoio à candidatura', 'eyes-child' ); ?>
			</span>
			<span class="dashboard-pro-cta__tag">
				<img src="<?php echo esc_url( eyes_asset_uri( 'images/icon-notification-bell.svg' ) ); ?>" width="18" height="18" alt="" aria-hidden="true" />
				<?php esc_html_e( 'Alertas semanais de matches', 'eyes-child' ); ?>
			</span>
		</div>
		<div class="dashboard-pro-cta__copy">
			<h2 class="dashboard-pro-cta__title" id="<?php echo esc_attr( $title_id ); ?>">
				<?php esc_html_e( 'Aumente as suas hipóteses de aprovação', 'eyes-child' ); ?>
				<br />
				<?php esc_html_e( 'com a subscrição de um plano PRO', 'eyes-child' ); ?>
			</h2>
			<p class="dashboard-pro-cta__text">
				<?php esc_html_e( 'Contas pro têm acesso a um banco de horas mensais de consultoria especializada.', 'eyes-child' ); ?>
			</p>
		</div>
		<a class="btn btn--primary dashboard-pro-cta__btn" href="<?php echo esc_url( $planos_url ); ?>">
			<?php esc_html_e( 'Subscrever plano Pro', 'eyes-child' ); ?>
		</a>
	</div>
</section>
