<?php
/**
 * Consult request success banner on concurso detail (Figma 6001:29716).
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

$hidden = ! empty( $args['hidden'] );
?>
<div
	class="concurso-detail-consult-success"
	data-consult-success-banner
	<?php echo $hidden ? ' hidden' : ''; ?>
	role="status"
	aria-live="polite"
>
	<span class="concurso-detail-consult-success__icon" aria-hidden="true">
		<img src="<?php echo esc_url( eyes_asset_uri( 'images/icon-checkmark.svg' ) ); ?>" width="56" height="56" alt="" />
	</span>
	<div class="concurso-detail-consult-success__copy">
		<h2 class="concurso-detail-consult-success__title">
			<?php esc_html_e( 'Pedido de consultoria enviado', 'eyes-child' ); ?>
		</h2>
		<p class="concurso-detail-consult-success__text">
			<?php esc_html_e( 'Em breve será contactado pela nossa equipa especializada em instrumentos de financiamento.', 'eyes-child' ); ?>
		</p>
	</div>
</div>
