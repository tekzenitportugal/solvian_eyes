<?php
/**
 * Site footer template.
 *
 * @package Eyes_Child
 */
?>
	<footer class="site-footer">
		<div class="site-footer__top site-layout">
			<div>
				<img
					class="site-footer__logo"
					src="<?php echo esc_url( eyes_asset_uri( 'images/footer-logo.png' ) ); ?>"
					width="140"
					height="112"
					alt="Solvian Technology"
				/>
			</div>
			<div class="site-footer__contact-row">
				<div class="site-footer__contact-block site-footer__contact-block--contacts">
					<div class="site-footer__line">
						<img src="<?php echo esc_url( eyes_asset_uri( 'images/icon-phone.svg' ) ); ?>" width="16" height="16" alt="" />
						<span><a href="tel:+351226160175">226 160 175</a></span>
					</div>
					<div class="site-footer__line">
						<img src="<?php echo esc_url( eyes_asset_uri( 'images/icon-mail.svg' ) ); ?>" width="16" height="16" alt="" />
						<span><a href="mailto:geral@solvian.pt">geral@solvian.pt</a></span>
					</div>
				</div>
				<div class="site-footer__address-cluster">
					<img
						class="site-footer__address-pin"
						src="<?php echo esc_url( eyes_asset_uri( 'images/icon-map-pin.svg' ) ); ?>"
						width="16"
						height="16"
						alt=""
					/>
					<div class="site-footer__addresses">
						<div class="site-footer__address-unit">
							<p class="site-footer__label-sm">Sede</p>
							<p class="site-footer__address-text">
								Rua Júlio Dinis, 585 4.º Esq. Tras.<br />4050 - 325 Porto · Portugal
							</p>
						</div>
						<div class="site-footer__address-unit">
							<p class="site-footer__label-sm">Espaço de cowork</p>
							<p class="site-footer__address-text">
								Rua de Álvaro Gomes, 106A, 4150-063 Porto · Portugal
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<hr class="site-footer__rule site-layout" aria-hidden="true" />

		<div class="site-footer__mid site-layout">
			<div class="site-footer__badges">
				<div class="site-footer__funding-logos" aria-hidden="true"></div>
				<div class="site-footer__brand-logos">
					<img
						class="site-footer__selo"
						src="<?php echo esc_url( eyes_asset_uri( 'images/footer-selo.png' ) ); ?>"
						width="72"
						height="72"
						alt="Selo Top 5%"
					/>
					<img
						class="site-footer__logo-group"
						src="<?php echo esc_url( eyes_asset_uri( 'images/footer-logo-group.png' ) ); ?>"
						alt=""
					/>
				</div>
			</div>
			<div class="site-footer__mid-aside">
				<nav class="site-footer__links" aria-label="Solvian Eyes">
					<p class="site-footer__links-title">
    <?php esc_html_e( 'Solvian Eyes', 'eyes-child' ); ?>
</p>

<a href="<?php echo esc_url( get_post_type_archive_link( 'concurso' ) ?: home_url( '/concurso/' ) ); ?>">
    <?php esc_html_e( 'Consultar Concursos', 'eyes-child' ); ?>
</a>

<a href="<?php echo esc_url( eyes_report_problem_url() ); ?>">
    <?php esc_html_e( 'Reportar Problema', 'eyes-child' ); ?>
</a>

<a href="<?php echo esc_url( home_url( '/legal/#termos' ) ); ?>">
    <?php esc_html_e( 'Termos e Condições', 'eyes-child' ); ?>
</a>

<a href="<?php echo esc_url( home_url( '/legal/#privacidade' ) ); ?>">
    <?php esc_html_e( 'Política de Privacidade', 'eyes-child' ); ?>
</a>

<a href="<?php echo esc_url( home_url( '/legal/#dados' ) ); ?>">
    <?php esc_html_e( 'Proteção de Dados', 'eyes-child' ); ?>
</a>
				</nav>
			</div>
		</div>

		<div class="site-footer__bottom site-layout">
			<p>
    ©<?php echo esc_html( gmdate( 'Y' ) ); ?> Solvian.
    <?php esc_html_e( 'Todos os direitos reservados.', 'eyes-child' ); ?>
</p>
			<div class="site-footer__credit">
				<span><?php esc_html_e( 'Design e Desenvolvimento por', 'eyes-child' ); ?></span>
				 <a href="https://www.tekzenit.com/" target="_blank" rel="noopener noreferrer"><img
					class="site-footer__credit-logo"
					src="<?php echo esc_url( eyes_asset_uri( 'images/logo-tekzenit.png' ) ); ?>"
					width="59"
					height="11"
					alt="tekzenit"
				/></a>
			</div>
		</div>
	</footer>
</div>
<?php wp_footer(); ?>
<script>
	
document.addEventListener('DOMContentLoaded', function() {

	const burger = document.getElementById('menu-open');
const closeBtn = document.getElementById('menu-close');
const drawer = document.getElementById('mobile-drawer');

burger.addEventListener('click', function () {
	drawer.classList.add('is-open');
	drawer.removeAttribute('hidden');
	drawer.setAttribute('aria-hidden', 'false');
});

closeBtn.addEventListener('click', function () {
	drawer.classList.remove('is-open');
	drawer.setAttribute('hidden', '');
	drawer.setAttribute('aria-hidden', 'true');
});
drawer.addEventListener('click', function (e) {
	if (e.target === drawer) {
		drawer.classList.remove('is-open');
		drawer.setAttribute('hidden', '');
		drawer.setAttribute('aria-hidden', 'true');
	}
});

});
</script>
</body>
</html>
