<?php
/**
 * Login page template (assign to a page with slug "login").
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

$redirect_to = eyes_auth_redirect_target();
$login_error = '';
if ( isset( $_GET['action'] ) ) {

	switch ( sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) {

		case 'invalid_username':
			$login_error = __( 'Invalid username or password.', 'eyes-child' );
			break;

		case 'incorrect_password':
			$login_error = __( 'Invalid username or password.', 'eyes-child' );
			break;

		case 'empty_username':
			$login_error = __( 'Please enter your username.', 'eyes-child' );
			break;

		case 'empty_password':
			$login_error = __( 'Please enter your password.', 'eyes-child' );
			break;
	}
}
if ( empty( $redirect_to ) ) {
	$redirect_to = get_post_type_archive_link( 'concurso' );

	if ( ! $redirect_to ) {
		$redirect_to = home_url( '/' );
	}
}

if ( is_user_logged_in() ) {
	wp_safe_redirect( $redirect_to );
	exit;
}

if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) && isset( $_POST['eyes_login'] ) ) {
	$result = eyes_process_login_form();
	

	if ( true === $result ) {
		wp_safe_redirect( $redirect_to );
		exit;
	}

	if ( is_wp_error( $result ) ) {
		echo '<pre>';
		print_r( $result );
		echo '</pre>';
		$login_error = $result->get_error_message();
	}
	if ( false === $result ) {
    $login_error = 'Invalid username or password.';
}
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( get_bloginfo( 'name' ) . ' — ' . __( 'Entrar', 'eyes-child' ) ); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'auth-page' ); ?>>
<?php wp_body_open(); ?>

<div class="auth-card">
	<div class="auth-card__visual" aria-hidden="true"></div>
	<div class="auth-card__body">
		<a class="auth-card__close" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Fechar', 'eyes-child' ); ?>">×</a>

	

		<form class="auth-card__form" method="post" action="<?php echo esc_url( eyes_login_url( $redirect_to ) ); ?>">
			<?php wp_nonce_field( 'eyes_login', 'eyes_login_nonce' ); ?>
			<input type="hidden" name="eyes_login" value="1" />
			<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />

			<div class="auth-card__head">
				<h1 class="auth-card__title"><?php esc_html_e( 'Faça login', 'eyes-child' ); ?></h1>
				<p class="auth-card__lead">
					<?php esc_html_e( 'Entre na sua conta para consultar e acompanhar as suas oportunidades de financiamento.', 'eyes-child' ); ?>
				</p>
			</div>
			<?php if ( $login_error ) : ?>
    <p class="auth-card__error" role="alert">
        <?php echo esc_html( $login_error ); ?>
    </p>
<?php endif; ?>
			<div class="form-field">
				<label class="form-field__label" for="login-user"><?php esc_html_e( 'Username ou Email', 'eyes-child' ); ?></label>
				<input
					class="form-field__control"
					id="login-user"
					name="user"
					type="text"
					value="<?php echo isset( $_POST['user'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['user'] ) ) ) : ''; ?>"
					placeholder="<?php esc_attr_e( 'Insira o seu username ou email', 'eyes-child' ); ?>"
					autocomplete="username"
					required
				/>
			</div>

			<div class="form-field">
				<label class="form-field__label" for="login-password"><?php esc_html_e( 'Password', 'eyes-child' ); ?></label>
				<div class="form-field__control-wrap">
					<input
						class="form-field__control"
						id="login-password"
						name="password"
						type="password"
						placeholder="<?php esc_attr_e( 'Insira a sua password', 'eyes-child' ); ?>"
						autocomplete="current-password"
						required
					/>
					<button type="button" class="password-toggle" aria-label="<?php esc_attr_e( 'Mostrar password', 'eyes-child' ); ?>"></button>
				</div>
			</div>

			<div class="auth-card__row">
				<label class="form-check">
					<input type="checkbox" name="remember" value="1" <?php checked( ! empty( $_POST['remember'] ) ); ?> />
					<span class="form-check__box" aria-hidden="true"></span>
					<span><?php esc_html_e( 'Manter sessão iniciada', 'eyes-child' ); ?></span>
				</label>
				<a class="auth-card__recover" href="<?php echo esc_url( wp_lostpassword_url( eyes_login_url( $redirect_to ) ) ); ?>">
					<?php esc_html_e( 'Recuperar palavra-passe', 'eyes-child' ); ?>
				</a>
			</div>

			<button type="submit" class="btn btn--primary" style="width: 100%"><?php esc_html_e( 'Entrar', 'eyes-child' ); ?></button>
		</form>

		<hr class="form-divider" />

		<div class="auth-card__signup">
			<h2 class="auth-card__signup-title"><?php esc_html_e( 'Crie uma conta gratuita', 'eyes-child' ); ?></h2>
			<p class="auth-card__signup-text">
				<?php esc_html_e( 'Crie um perfil da sua empresa e descubra as oportunidades de financiamento que se enquadram no seu perfil.', 'eyes-child' ); ?>
			</p>
			<a class="btn btn--info" href="<?php echo esc_url( eyes_registo_url() ); ?>">
				<?php esc_html_e( 'Criar conta', 'eyes-child' ); ?>
				<img src="<?php echo esc_url( eyes_asset_uri( 'images/icon-arrow-right-dark.svg' ) ); ?>" width="20" height="20" alt="" />
			</a>
		</div>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
