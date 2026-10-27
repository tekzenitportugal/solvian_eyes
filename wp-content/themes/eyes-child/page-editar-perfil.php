<?php
/**
 * Edit profile page template (slug: editar-perfil).
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( eyes_login_url( eyes_edit_profile_url() ) );
	exit;
}

$profile_error   = '';
$profile_success = false;
$cancel_url      = get_post_type_archive_link( 'concurso' ) ?: home_url( '/' );
$planos_url      = home_url( '/planos/' );
$is_pro          = function_exists( 'eyes_user_has_pro_plan' ) && eyes_user_has_pro_plan();
$form            = eyes_get_user_profile_form_data();

if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
	if ( isset( $_POST['eyes_delete_account'] ) ) {
		$result = eyes_process_delete_account_form();

		if ( true === $result ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		if ( is_wp_error( $result ) ) {
			$profile_error = $result->get_error_message();
		}
	} elseif ( isset( $_POST['eyes_edit_profile'] ) ) {
		$result = eyes_process_edit_profile_form();

		if ( true === $result ) {
			$profile_success = true;
			$form            = eyes_get_user_profile_form_data();
		} elseif ( is_wp_error( $result ) ) {
			$profile_error = $result->get_error_message();
			$form          = array_merge( $form, eyes_sanitize_profile_post_fields() );
		}
	}
}

get_header();
?>

<main class="edit-profile-page">
	<div class="site-layout site-layout--top-24">
		<section class="edit-profile-panel" aria-labelledby="edit-profile-title">
			<h1 class="edit-profile-panel__title" id="edit-profile-title">
				<?php esc_html_e( 'Editar Perfil', 'eyes-child' ); ?>
			</h1>

			<?php if ( $profile_success ) : ?>
				<p class="edit-profile-panel__notice edit-profile-panel__notice--success" role="status">
					<?php esc_html_e( 'Perfil atualizado com sucesso.', 'eyes-child' ); ?>
				</p>
			<?php endif; ?>

			<?php if ( $profile_error ) : ?>
				<p class="edit-profile-panel__notice edit-profile-panel__notice--error" role="alert">
					<?php echo esc_html( $profile_error ); ?>
				</p>
			<?php endif; ?>

			<form
				class="edit-profile-panel__form"
				method="post"
				action="<?php echo esc_url( eyes_edit_profile_url() ); ?>"
				data-localidade-nuts
			>
				<?php wp_nonce_field( 'eyes_edit_profile', 'eyes_edit_profile_nonce' ); ?>
				<input type="hidden" name="eyes_edit_profile" value="1" />

				<section class="edit-profile-panel__section" aria-labelledby="edit-profile-access-title">
					<h2 class="form-section-title" id="edit-profile-access-title">
						<?php esc_html_e( 'Dados de acesso', 'eyes-child' ); ?>
					</h2>

					<div class="form-field">
						<label class="form-field__label" for="nome"><?php esc_html_e( 'Nome completo*', 'eyes-child' ); ?></label>
						<input class="form-field__control" id="nome" name="nome" type="text" value="<?php echo esc_attr( $form['nome'] ?? '' ); ?>" autocomplete="name" required />
					</div>

					<div class="form-row">
						<div class="form-field">
							<label class="form-field__label" for="email"><?php esc_html_e( 'Email*', 'eyes-child' ); ?></label>
							<input class="form-field__control" id="email" name="email" type="email" value="<?php echo esc_attr( $form['email'] ?? '' ); ?>" autocomplete="email" required />
						</div>
						<div class="form-field">
							<label class="form-field__label" for="telemovel"><?php esc_html_e( 'Telemóvel*', 'eyes-child' ); ?></label>
							<input class="form-field__control" id="telemovel" name="telemovel" type="tel" value="<?php echo esc_attr( $form['telemovel'] ?? '' ); ?>" autocomplete="tel" required />
						</div>
					</div>

					<div class="form-field">
						<label class="form-field__label" for="password"><?php esc_html_e( 'Password*', 'eyes-child' ); ?></label>
						<div class="form-field__control-wrap">
							<input
								class="form-field__control"
								id="password"
								name="password"
								type="password"
								placeholder="<?php esc_attr_e( 'Deixe em branco para manter a atual', 'eyes-child' ); ?>"
								autocomplete="new-password"
							/>
							<button type="button" class="password-toggle" aria-label="<?php esc_attr_e( 'Mostrar password', 'eyes-child' ); ?>"></button>
						</div>
					</div>
				</section>

				<section class="edit-profile-panel__section" aria-labelledby="edit-profile-company-title">
					<h2 class="form-section-title" id="edit-profile-company-title">
						<?php esc_html_e( 'Perfil de empresa', 'eyes-child' ); ?>
					</h2>

					<p class="edit-profile-panel__establishment"><?php esc_html_e( 'Sede/ Estabelecimento 1', 'eyes-child' ); ?></p>

					<div class="form-row">
						<div class="form-field">
							<label class="form-field__label" for="empresa"><?php esc_html_e( 'Nome da empresa*', 'eyes-child' ); ?></label>
							<input class="form-field__control" id="empresa" name="empresa" type="text" value="<?php echo esc_attr( $form['empresa'] ?? '' ); ?>" required />
						</div>
						<div class="form-field">
							<label class="form-field__label" for="nif"><?php esc_html_e( 'NIF*', 'eyes-child' ); ?></label>
							<input class="form-field__control" id="nif" name="nif" type="text" value="<?php echo esc_attr( $form['nif'] ?? '' ); ?>" required />
						</div>
					</div>

					<div class="form-row">
						<div class="form-field">
							<label class="form-field__label" for="cae"><?php esc_html_e( 'CAE principal*', 'eyes-child' ); ?></label>
							<select class="form-field__control" id="cae" name="cae" required>
								<option value="" disabled <?php selected( empty( $form['cae'] ) ); ?>><?php esc_html_e( 'Seleccione Atividade', 'eyes-child' ); ?></option>
								<?php foreach ( eyes_get_cae_codes() as $code => $label ) : ?>
									<option value="<?php echo esc_attr( $code ); ?>" <?php selected( ( $form['cae'] ?? '' ) === $code ); ?>>
										<?php echo esc_html( eyes_format_cae_label( $code, $label ) ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="form-field">
							<label class="form-field__label" for="enquadramento-pme"><?php esc_html_e( 'Enquadramento PME*', 'eyes-child' ); ?></label>
							<select class="form-field__control" id="enquadramento-pme" name="enquadramento_pme" required>
								<option value="" disabled <?php selected( empty( $form['enquadramento_pme'] ) ); ?>><?php esc_html_e( 'Seleccione enquadramento', 'eyes-child' ); ?></option>
								<?php foreach ( eyes_get_pme_enquadramento_options() as $slug => $label ) : ?>
									<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( ( $form['enquadramento_pme'] ?? '' ) === $slug ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="form-row">
						<div class="form-field">
							<label class="form-field__label" for="ano"><?php esc_html_e( 'Ano de constituição*', 'eyes-child' ); ?></label>
							<input class="form-field__control" id="ano" name="ano" type="text" value="<?php echo esc_attr( $form['ano'] ?? '' ); ?>" required />
						</div>
						<div class="form-field" aria-hidden="true"></div>
					</div>

					<div class="form-field form-field--icon-left">
						<label class="form-field__label" for="website"><?php esc_html_e( 'Website', 'eyes-child' ); ?></label>
						<div class="form-field__control-wrap">
							<img src="<?php echo esc_url( eyes_asset_uri( 'images/icon-link.svg' ) ); ?>" width="16" height="16" alt="" aria-hidden="true" />
							<input class="form-field__control" id="website" name="website" type="url" value="<?php echo esc_attr( $form['website'] ?? '' ); ?>" placeholder="www.exemplo.com" />
						</div>
					</div>

					<div class="form-field">
						<label class="form-field__label" for="endereco"><?php esc_html_e( 'Endereço*', 'eyes-child' ); ?></label>
						<input class="form-field__control" id="endereco" name="endereco" type="text" value="<?php echo esc_attr( $form['endereco'] ?? '' ); ?>" required />
					</div>

					<div class="form-row">
						<div class="form-field">
							<label class="form-field__label" for="codigo-postal"><?php esc_html_e( 'Código-postal*', 'eyes-child' ); ?></label>
							<input class="form-field__control" id="codigo-postal" name="codigo_postal" type="text" value="<?php echo esc_attr( $form['codigo_postal'] ?? '' ); ?>" required />
						</div>
						<div class="form-field">
							<label class="form-field__label" for="localidade"><?php esc_html_e( 'Localidade*', 'eyes-child' ); ?></label>
							<select class="form-field__control" id="localidade" name="localidade" required>
								<option value="" disabled <?php selected( empty( $form['localidade'] ) ); ?>><?php esc_html_e( 'Seleccione localidade', 'eyes-child' ); ?></option>
								<?php foreach ( eyes_get_localidade_options() as $slug => $label ) : ?>
									<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( ( $form['localidade'] ?? '' ) === $slug ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="form-row">
						<div class="form-field">
							<label class="form-field__label" for="nuts2"><?php esc_html_e( 'NUTS II', 'eyes-child' ); ?></label>
							<input class="form-field__control" id="nuts2" type="text" value="<?php echo esc_attr( $form['nuts2'] ?? '' ); ?>" disabled />
						</div>
						<div class="form-field">
							<label class="form-field__label" for="nuts3"><?php esc_html_e( 'NUTS III', 'eyes-child' ); ?></label>
							<input class="form-field__control" id="nuts3" type="text" value="<?php echo esc_attr( $form['nuts3'] ?? '' ); ?>" disabled />
						</div>
					</div>

					<fieldset class="edit-profile-panel__radio-group">
						<legend class="edit-profile-panel__radio-legend">
							<?php esc_html_e( 'Este é o único estabelecimento da empresa', 'eyes-child' ); ?>
						</legend>
						<div class="edit-profile-panel__radio-row">
							<label class="form-radio">
								<input type="radio" name="unico_estabelecimento" value="sim" <?php checked( ( $form['unico_estabelecimento'] ?? 'sim' ), 'sim' ); ?> />
								<span class="form-radio__mark" aria-hidden="true"></span>
								<span class="form-radio__label"><?php esc_html_e( 'Sim', 'eyes-child' ); ?></span>
							</label>
							<label class="form-radio">
								<input type="radio" name="unico_estabelecimento" value="nao" <?php checked( ( $form['unico_estabelecimento'] ?? '' ), 'nao' ); ?> />
								<span class="form-radio__mark" aria-hidden="true"></span>
								<span class="form-radio__label"><?php esc_html_e( 'Não', 'eyes-child' ); ?></span>
							</label>
						</div>
					</fieldset>

					<p class="form-note"><?php esc_html_e( '*Campos obrigatórios', 'eyes-child' ); ?></p>
				</section>

				<div class="edit-profile-panel__actions">
					<div class="edit-profile-panel__actions-main">
						<button type="submit" class="btn btn--primary edit-profile-panel__save">
							<?php esc_html_e( 'Guardar', 'eyes-child' ); ?>
						</button>
						<a class="btn btn--outline-primary edit-profile-panel__cancel" href="<?php echo esc_url( $cancel_url ); ?>">
							<?php esc_html_e( 'Cancelar', 'eyes-child' ); ?>
						</a>
					</div>

					<button
						type="submit"
						class="btn btn--outline-danger edit-profile-panel__delete"
						form="edit-profile-delete-form"
						onclick="return confirm('<?php echo esc_js( __( 'Tem a certeza de que pretende apagar a sua conta? Esta ação é irreversível.', 'eyes-child' ) ); ?>');"
					>
						<?php esc_html_e( 'Apagar conta', 'eyes-child' ); ?>
					</button>
				</div>
			</form>

			<form id="edit-profile-delete-form" method="post" action="<?php echo esc_url( eyes_edit_profile_url() ); ?>" hidden>
				<?php wp_nonce_field( 'eyes_delete_account', 'eyes_delete_account_nonce' ); ?>
				<input type="hidden" name="eyes_delete_account" value="1" />
			</form>
		</section>

		<?php if ( ! $is_pro ) : ?>
			<section class="dashboard-pro-cta" aria-labelledby="edit-profile-pro-cta-title">
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
						<h2 class="dashboard-pro-cta__title" id="edit-profile-pro-cta-title">
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
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
