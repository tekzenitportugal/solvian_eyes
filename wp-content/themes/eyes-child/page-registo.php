<?php
/**
 * Registration page template (assign to a page with slug "registo").
 *
 * @package Eyes_Child
 */

 defined( 'ABSPATH' ) || exit;


 
 $redirect_to     = eyes_auth_redirect_target();
 $registo_error   = '';
 $registo_success = false;

if ( is_user_logged_in() ) {
	wp_safe_redirect( $redirect_to );
	exit;
}

if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) && isset( $_POST['eyes_registo'] ) ) {
	$result = eyes_process_registo_form();

	if ( true === $result ) {
		$registo_success = true;
	}


	if ( is_wp_error( $result ) ) {
		$registo_error = $result->get_error_message();
	}
}

$legal_base = home_url( '/legal/' );

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( get_bloginfo( 'name' ) . ' — ' . __( 'Criar conta', 'eyes-child' ) ); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'registo-page dark-page' ); ?>>
<?php wp_body_open(); ?>

<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
	<img
		class="dark-page__logo"
		src="<?php echo esc_url( eyes_asset_uri( 'images/footer-logo.png' ) ); ?>"
		width="158"
		height="50"
		alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
	/>
</a>

<main class="surface-card surface-card--form registo-page__card">
	<div class="registo-page__intro">
		<h1 class="registo-page__title"><?php esc_html_e( 'Criar conta gratuita', 'eyes-child' ); ?></h1>
		<p class="registo-page__subtitle"><?php esc_html_e( 'Encontre hoje as melhores oportunidades para a sua empresa!', 'eyes-child' ); ?></p>
	</div>

	<?php if ( $registo_error ) : ?>
		<p class="registo-page__error" role="alert"><?php echo esc_html( $registo_error ); ?></p>
	<?php endif; ?>
	<?php if ( $registo_success ) : ?>
	<div class="registo-page__success">
		<h3>Conta criada com sucesso!</h3>
		<p>Será redirecionado em 5 segundos...</p>
	</div>

	<script>
		setTimeout(function () {
			window.location.href = <?php echo wp_json_encode( $redirect_to ); ?>;
		}, 5000);
	</script>
<?php endif; ?>
<?php if ( ! $registo_success ) : ?>
	<form class="registo-page__form" method="post" action="<?php echo esc_url( eyes_registo_url() ); ?>" data-localidade-nuts>
		<?php wp_nonce_field( 'eyes_registo', 'eyes_registo_nonce' ); ?>
		<input type="hidden" name="eyes_registo" value="1" />
		<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />

		<section class="registo-page__section" aria-labelledby="registo-access-title">
			<h2 class="form-section-title" id="registo-access-title"><?php esc_html_e( 'Dados de acesso', 'eyes-child' ); ?></h2>

			<div class="form-field">
				<label class="form-field__label" for="nome"><?php esc_html_e( 'Nome completo*', 'eyes-child' ); ?></label>
				<input class="form-field__control" id="nome" name="nome" type="text" placeholder="<?php esc_attr_e( 'Seu nome completo', 'eyes-child' ); ?>" autocomplete="name" required />
			</div>

			<div class="form-row">
				<div class="form-field">
					<label class="form-field__label" for="email"><?php esc_html_e( 'Email*', 'eyes-child' ); ?></label>
					<input class="form-field__control" id="email" name="email" type="email" placeholder="user@email.com" autocomplete="email" required />
				</div>
				<div class="form-field">
					<label class="form-field__label" for="telemovel"><?php esc_html_e( 'Telemóvel*', 'eyes-child' ); ?></label>
					<input class="form-field__control" id="telemovel" name="telemovel" type="tel" placeholder="+351 912 345 678" autocomplete="tel" required />
				</div>
			</div>

			<div class="form-row">
				<div class="form-field">
					<label class="form-field__label" for="password"><?php esc_html_e( 'Password*', 'eyes-child' ); ?></label>
					<div class="form-field__control-wrap">
						<input class="form-field__control" id="password" name="password" type="password" placeholder="Password" autocomplete="new-password" required />
						<button type="button" class="password-toggle" aria-label="<?php esc_attr_e( 'Mostrar password', 'eyes-child' ); ?>"></button>
					</div>
				</div>
				<div class="form-field">
					<label class="form-field__label" for="password-repeat"><?php esc_html_e( 'Repetir password*', 'eyes-child' ); ?></label>
					<div class="form-field__control-wrap">
						<input class="form-field__control" id="password-repeat" name="password_repeat" type="password" placeholder="<?php esc_attr_e( 'Repetir Password', 'eyes-child' ); ?>" autocomplete="new-password" required />
						<button type="button" class="password-toggle" aria-label="<?php esc_attr_e( 'Mostrar password', 'eyes-child' ); ?>"></button>
					</div>
				</div>
			</div>
		</section>

		<section class="registo-page__section" aria-labelledby="registo-company-title">
			<h2 class="form-section-title" id="registo-company-title"><?php esc_html_e( 'Perfil de empresa', 'eyes-child' ); ?></h2>

			<div class="form-row">
				<div class="form-field">
					<label class="form-field__label" for="empresa"><?php esc_html_e( 'Nome da empresa*', 'eyes-child' ); ?></label>
					<input class="form-field__control" id="empresa" name="empresa" type="text" placeholder="<?php esc_attr_e( 'Nome de empresa', 'eyes-child' ); ?>" required />
				</div>
				<div class="form-field">
					<label class="form-field__label" for="nif"><?php esc_html_e( 'NIF*', 'eyes-child' ); ?></label>
					<input class="form-field__control" id="nif" name="nif" type="text" placeholder="<?php esc_attr_e( 'NIF da empresa', 'eyes-child' ); ?>" required />
				</div>
			</div>

			<div class="form-row">
				<div class="form-field">
					<label class="form-field__label" for="cae"><?php esc_html_e( 'CAE principal*', 'eyes-child' ); ?></label>
					<select class="form-field__control" id="cae" name="cae" required>
						<option value="" disabled selected><?php esc_html_e( 'Seleccione Atividade', 'eyes-child' ); ?></option>
						<?php foreach ( eyes_get_cae_codes() as $code => $label ) : ?>
							<option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( eyes_format_cae_label( $code, $label ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-field">
					<label class="form-field__label" for="enquadramento-pme"><?php esc_html_e( 'Enquadramento PME*', 'eyes-child' ); ?></label>
					<select class="form-field__control" id="enquadramento-pme" name="enquadramento_pme" required>
						<option value="" disabled selected><?php esc_html_e( 'Seleccione enquadramento', 'eyes-child' ); ?></option>
						<?php foreach ( eyes_get_pme_enquadramento_options() as $slug => $label ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<div class="form-row">
				<div class="form-field">
					<label class="form-field__label" for="ano"><?php esc_html_e( 'Ano de constituição*', 'eyes-child' ); ?></label>
					<input class="form-field__control" id="ano" name="ano" type="text" placeholder="<?php esc_attr_e( 'Ano de início', 'eyes-child' ); ?>" required />
				</div>
				<div class="form-field" aria-hidden="true"></div>
			</div>

			<div class="form-field">
				<label class="form-field__label" for="website"><?php esc_html_e( 'Website', 'eyes-child' ); ?></label>
				<input class="form-field__control" id="website" name="website" type="url" placeholder="Website" />
			</div>

			<div class="form-field">
				<label class="form-field__label" for="endereco"><?php esc_html_e( 'Endereço*', 'eyes-child' ); ?></label>
				<input class="form-field__control" id="endereco" name="endereco" type="text" placeholder="<?php esc_attr_e( 'Morada fiscal', 'eyes-child' ); ?>" required />
			</div>

			<div class="form-row">
				<div class="form-field">
					<label class="form-field__label" for="codigo-postal"><?php esc_html_e( 'Código-postal*', 'eyes-child' ); ?></label>
					<input class="form-field__control" id="codigo-postal" name="codigo_postal" type="text" placeholder="<?php esc_attr_e( 'Código-postal', 'eyes-child' ); ?>" required />
				</div>
				<div class="form-field">
					<label class="form-field__label" for="localidade"><?php esc_html_e( 'Localidade*', 'eyes-child' ); ?></label>
					<select class="form-field__control" id="localidade" name="localidade" required>
						<option value="" disabled selected><?php esc_html_e( 'Seleccione localidade', 'eyes-child' ); ?></option>
						<option value="porto">Porto</option>
						<option value="lisboa">Lisboa</option>
					</select>
				</div>
			</div>

			<div class="form-row">
				<div class="form-field">
					<label class="form-field__label" for="nuts2">NUTS II</label>
					<input class="form-field__control" id="nuts2" name="nuts2" type="text" placeholder="NUTS II" disabled />
				</div>
				<div class="form-field">
					<label class="form-field__label" for="nuts3">NUTS III</label>
					<input class="form-field__control" id="nuts3" name="nuts3" type="text" placeholder="NUTS III" disabled />
				</div>
			</div>

			<div class="registo-page__footer-fields">
				<p class="form-note"><?php esc_html_e( '*Campos obrigatórios', 'eyes-child' ); ?></p>
				<label class="form-check">
					<input type="checkbox" name="termos" value="1" required />
					<span class="form-check__box" aria-hidden="true"></span>
					<span>
						<?php esc_html_e( 'Li e aceito os', 'eyes-child' ); ?>
						<a href="<?php echo esc_url( $legal_base . '#termos' ); ?>"><?php esc_html_e( 'termos e condições', 'eyes-child' ); ?></a>
						<?php esc_html_e( 'e', 'eyes-child' ); ?>
						<a href="<?php echo esc_url( $legal_base . '#privacidade' ); ?>"><?php esc_html_e( 'política de privacidade', 'eyes-child' ); ?></a>.*
					</span>
				</label>
				<label class="form-check">
					<input type="checkbox" name="newsletter" value="1" />
					<span class="form-check__box" aria-hidden="true"></span>
					<span><?php esc_html_e( 'Subscrever a newsletter com novidades e promoções.', 'eyes-child' ); ?></span>
				</label>
			</div>
		</section>

		<button type="submit" class="btn btn--primary registo-page__submit"><?php esc_html_e( 'Começar a usar', 'eyes-child' ); ?></button>
	</form>
	<?php endif; ?>
	<p class="registo-page__login-link">
		<?php esc_html_e( 'Já tem conta?', 'eyes-child' ); ?>
		<a href="<?php echo esc_url( eyes_login_url( $redirect_to ) ); ?>"><?php esc_html_e( 'Entrar', 'eyes-child' ); ?></a>
	</p>
</main>

<p class="dark-page__footer">©<?php echo esc_html( gmdate( 'Y' ) ); ?> Solvian. <?php esc_html_e( 'All Rights Reserved.', 'eyes-child' ); ?></p>

<?php wp_footer(); ?>
</body>
</html>
