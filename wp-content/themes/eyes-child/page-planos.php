<?php
/**
 * Plan selection page template (assign to a page with slug "planos").
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

$plans         = eyes_get_plan_definitions();
$pmpro_levels = array(
	'monthly' => 4, // <-- replace with real PMPro level ID
	'annual'  => 3, // <-- replace with real PMPro level ID
);
$current_plan  = eyes_get_user_current_plan();
$billing       = 'monthly';
$user_billing  = is_user_logged_in()
	? sanitize_key( (string) get_user_meta( get_current_user_id(), 'eyes_plan_billing', true ) )
	: '';

if ( in_array( $user_billing, array( 'monthly', 'annual' ), true ) ) {
	$billing = $user_billing;
}

if ( isset( $_GET['billing'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$query_billing = sanitize_key( wp_unslash( $_GET['billing'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( in_array( $query_billing, array( 'monthly', 'annual' ), true ) ) {
		$billing = $query_billing;
	}
}
$concurso_url = get_post_type_archive_link( 'concurso' );
if ( ! $concurso_url ) {
	$concurso_url = home_url( '/' );
}

$summary = is_user_logged_in() && 'advanced' === $current_plan
	? eyes_get_plan_success_summary()
	: array();

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( get_bloginfo( 'name' ) . ' — ' . __( 'Planos', 'eyes-child' ) ); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'planos-page-body dark-page' ); ?>>
<?php wp_body_open(); ?>

<main
	class="planos-page"
	data-planos-root
	data-planos-view="plans"
	data-planos-billing="<?php echo esc_attr( $billing ); ?>"
	data-planos-current="<?php echo esc_attr( $current_plan ); ?>"
>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
		<img
			class="dark-page__logo"
			src="<?php echo esc_url( eyes_asset_uri( 'images/footer-logo.png' ) ); ?>"
			width="158"
			height="50"
			alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
		/>
	</a>

	<div class="surface-card planos-page__card" data-planos-panel="plans">
		<div class="planos-page__intro">
			<h1 class="planos-page__title"><?php esc_html_e( 'Subscreva o plano Premium', 'eyes-child' ); ?></h1>
			<p class="planos-page__lead">
				<?php esc_html_e( 'Desbloqueie', 'eyes-child' ); ?>
				<strong><?php esc_html_e( 'análises avançadas', 'eyes-child' ); ?></strong>,
				<strong><?php esc_html_e( 'alertas de oportunidades', 'eyes-child' ); ?></strong>
				<?php esc_html_e( 'e', 'eyes-child' ); ?>
				<strong><?php esc_html_e( 'apoio técnico dedicado à sua candidatura', 'eyes-child' ); ?></strong>.
			</p>
		</div>

		<div class="planos-page__billing">
			<div class="planos-page__billing-inner" role="group" aria-label="<?php esc_attr_e( 'Período de faturação', 'eyes-child' ); ?>">
				<button
					type="button"
					class="planos-page__billing-btn<?php echo 'monthly' === $billing ? ' is-active' : ''; ?>"
					data-planos-billing-toggle="monthly"
				>
					<?php esc_html_e( 'Plano Mensal', 'eyes-child' ); ?>
				</button>
				<button
					type="button"
					class="planos-page__billing-btn<?php echo 'annual' === $billing ? ' is-active' : ''; ?>"
					data-planos-billing-toggle="annual"
				>
					<?php esc_html_e( 'Plano Anual', 'eyes-child' ); ?>
				</button>
			</div>
		</div>

		<div class="planos-page__grid">
			<?php foreach ( array( 'guest', 'free', 'advanced' ) as $plan_id ) : ?>
				<?php
				$plan       = $plans[ $plan_id ];
				$btn_state  = eyes_get_planos_card_button( $plan_id, $current_plan );
				$btn_class  = 'planos-card__btn';

				if ( $btn_state['current'] ) {
					$btn_class .= ' planos-card__btn--current';
				}
				?>
				<article class="planos-card" data-planos-card="<?php echo esc_attr( $plan_id ); ?>">
					<div class="planos-card__head">
						<h2 class="planos-card__name"><?php echo esc_html( $plan['name'] ); ?></h2>
						<button type="button" class="planos-card__info" aria-label="<?php esc_attr_e( 'Mais informação', 'eyes-child' ); ?>">i</button>
					</div>

					<?php if ( 'advanced' === $plan_id ) : ?>
						<p class="planos-card__price" data-planos-advanced-price>
							<span class="planos-card__price-value">30</span>
							<span class="planos-card__price-currency">€</span>
							<span class="planos-card__price-period">/mês</span>
						</p>
					<?php else : ?>
						<p class="planos-card__price">
							<span class="planos-card__price-value">0</span>
							<span class="planos-card__price-currency">€</span>
						</p>
					<?php endif; ?>

					<ul class="planos-card__features">
						<?php foreach ( $plan['features'] as $feature ) : ?>
							<li class="planos-card__feature"><?php echo esc_html( $feature ); ?></li>
						<?php endforeach; ?>
					</ul>

					<button
						type="button"
						class="<?php echo esc_attr( $btn_class ); ?>"
						data-planos-select="<?php echo esc_attr( $plan_id ); ?>"
						<?php disabled( $btn_state['disabled'] ); ?>
					>
						<?php echo esc_html( $btn_state['label'] ); ?>
					</button>
				</article>
			<?php endforeach; ?>
		</div>

		<p class="planos-page__footnote"><?php esc_html_e( 'Cancele quando quiser, sem custos adicionais.', 'eyes-child' ); ?></p>
	</div>

	<div class="surface-card planos-checkout" data-planos-panel="checkout">
		<button type="button" class="planos-checkout__back" data-planos-back>
			<?php esc_html_e( '← Voltar aos planos', 'eyes-child' ); ?>
		</button>

		<div class="planos-checkout__intro">
			<h1 class="planos-checkout__title"><?php esc_html_e( 'Subscrição ao Plano Pro', 'eyes-child' ); ?></h1>
			<p class="planos-checkout__lead">
				<?php esc_html_e( 'Desbloqueie', 'eyes-child' ); ?>
				<strong><?php esc_html_e( 'análises avançadas', 'eyes-child' ); ?></strong>,
				<strong><?php esc_html_e( 'alertas de oportunidades', 'eyes-child' ); ?></strong>
				<?php esc_html_e( 'e', 'eyes-child' ); ?>
				<strong><?php esc_html_e( 'apoio técnico dedicado à sua candidatura', 'eyes-child' ); ?></strong>.
			</p>
		</div>

		<div class="planos-checkout__period" role="radiogroup" aria-label="<?php esc_attr_e( 'Período de subscrição', 'eyes-child' ); ?>">
			<label class="planos-period-card<?php echo 'monthly' === $billing ? ' is-active' : ''; ?>">
				<?php $monthly_url = pmpro_url('checkout', '?level=' . $pmpro_levels['monthly']); ?>
			<input
	type="radio"
	name="planos_period"
	value="monthly"
	data-planos-period-input
	data-checkout-url="<?php echo esc_url( $monthly_url ); ?>"
	<?php checked( $billing, 'monthly' ); ?>
/>
				<span class="planos-period-card__radio" aria-hidden="true"></span>
				<span class="planos-period-card__body">
					<span class="planos-period-card__name"><?php esc_html_e( 'Mensal', 'eyes-child' ); ?></span>
					<span class="planos-period-card__price">
						<span class="planos-period-card__amount">30</span>
						<span class="planos-period-card__currency">€</span>
						<span class="planos-period-card__suffix">/mês</span>
					</span>
				</span>
			</label>

			<label class="planos-period-card<?php echo 'annual' === $billing ? ' is-active' : ''; ?>">
				<?php $annual_url = pmpro_url('checkout', '?level=' . $pmpro_levels['annual']); ?>
				<input
	type="radio"
	name="planos_period"
	value="annual"
	data-planos-period-input
checkout-url="<?php echo esc_url( $annual_url ); ?>"
	<?php checked( $billing, 'annual' ); ?>
/>
				<span class="planos-period-card__radio" aria-hidden="true"></span>
				<span class="planos-period-card__body">
					<span class="planos-period-card__name"><?php esc_html_e( 'Anual', 'eyes-child' ); ?></span>
					<span class="planos-period-card__price">
						<span class="planos-period-card__amount">300</span>
						<span class="planos-period-card__currency">€</span>
						<span class="planos-period-card__suffix">/ano</span>
						<span class="planos-period-card__badge"><?php esc_html_e( '2 meses grátis', 'eyes-child' ); ?></span>
					</span>
				</span>
			</label>
		</div>

		<div class="planos-checkout__submit">
		<button
	type="button"
	class="btn btn--primary planos-checkout__confirm"
	data-planos-confirm
	data-default-url="<?php echo esc_url( pmpro_url('checkout', '?level=' . $pmpro_levels['monthly']) ); ?>"
>
	<?php esc_html_e( 'Confirmar seleção', 'eyes-child' ); ?>
</button>
			<p class="planos-checkout__footnote"><?php esc_html_e( 'Cancele quando quiser, sem custos adicionais.', 'eyes-child' ); ?></p>
		</div>
	</div>

	<div class="surface-card planos-success" data-planos-panel="success">
		<div class="planos-success__icon" aria-hidden="true"></div>

		<div class="planos-success__intro">
			<h1 class="planos-success__title"><?php esc_html_e( 'Pagamento confirmado', 'eyes-child' ); ?></h1>
			<p class="planos-success__lead">
				<?php esc_html_e( 'Enviámos o recibo e os detalhes da subscrição para o seu e-mail.', 'eyes-child' ); ?>
				<br />
				<?php esc_html_e( 'Pode começar a explorar as análises e ferramentas avançadas de imediato.', 'eyes-child' ); ?>
			</p>
		</div>

		<hr class="planos-success__divider" />

		<div class="planos-success__details">
			<h2 class="planos-success__details-title"><?php esc_html_e( 'Detalhes de Subscrição', 'eyes-child' ); ?></h2>

			<div class="planos-success__grid">
				<div class="planos-success__field">
					<span class="planos-success__label"><?php esc_html_e( 'Tipo de subscrição', 'eyes-child' ); ?></span>
					<span class="planos-success__value" data-planos-success-billing><?php echo esc_html( $summary['billing_label'] ?? '' ); ?></span>
				</div>
				<div class="planos-success__field">
					<span class="planos-success__label"><?php esc_html_e( 'Preço', 'eyes-child' ); ?></span>
					<span class="planos-success__value" data-planos-success-price><?php echo esc_html( $summary['price_label'] ?? '' ); ?></span>
				</div>
				<div class="planos-success__field">
					<span class="planos-success__label"><?php esc_html_e( 'Nome da empresa', 'eyes-child' ); ?></span>
					<span class="planos-success__value" data-planos-success-company><?php echo esc_html( $summary['company'] ?? '' ); ?></span>
				</div>
				<div class="planos-success__field">
					<span class="planos-success__label"><?php esc_html_e( 'NIF', 'eyes-child' ); ?></span>
					<span class="planos-success__value" data-planos-success-nif><?php echo esc_html( $summary['nif'] ?? '' ); ?></span>
				</div>
				<div class="planos-success__field planos-success__field--wide">
					<span class="planos-success__label"><?php esc_html_e( 'Morada', 'eyes-child' ); ?></span>
					<span class="planos-success__value" data-planos-success-address><?php echo esc_html( $summary['address'] ?? '' ); ?></span>
				</div>
			</div>
		</div>

		<a class="btn btn--primary planos-success__cta" href="<?php echo esc_url( $concurso_url ); ?>">
			<?php esc_html_e( 'Começar a Usar', 'eyes-child' ); ?>
		</a>
	</div>

	<p class="dark-page__footer">©<?php echo esc_html( gmdate( 'Y' ) ); ?> Solvian. <?php esc_html_e( 'All Rights Reserved.', 'eyes-child' ); ?></p>
</main>

<?php wp_footer(); ?>
</body>
</html>
