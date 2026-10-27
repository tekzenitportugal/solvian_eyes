<?php
/**
 * Favourite (star) button for concurso cards and detail hero.
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();

if ( ! $post_id ) {
	return;
}

$button_class = isset( $args['button_class'] ) ? $args['button_class'] : 'concursos-card__btn-icon';
$label        = isset( $args['label'] ) ? $args['label'] : __( 'Guardar concurso', 'eyes-child' );
$is_favourite = eyes_is_favourite_concurso( $post_id );
$login_url    = eyes_login_url( get_permalink( $post_id ) );
$icon         = $is_favourite ? eyes_asset_uri( 'images/icon-star-filled.svg' ) : eyes_asset_uri( 'images/icon-star-outline.svg' );
?>
<button
	type="button"
	class="<?php echo esc_attr( trim( $button_class . ( $is_favourite ? ' is-favourite' : '' ) ) ); ?>"
	data-favourite-toggle
	data-concurso-id="<?php echo esc_attr( (string) $post_id ); ?>"
	data-login-url="<?php echo esc_url( $login_url ); ?>"
	aria-label="<?php echo esc_attr( $label ); ?>"
	aria-pressed="<?php echo $is_favourite ? 'true' : 'false'; ?>"
>
	<img src="<?php echo esc_url( $icon ); ?>" width="16" height="16" alt="" />
	<?php if ( ! empty( $args['show_text'] ) ) : ?>
		<span><?php echo esc_html( $label ); ?></span>
	<?php endif; ?>
</button>
