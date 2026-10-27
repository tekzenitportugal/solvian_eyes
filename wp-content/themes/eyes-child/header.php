<?php
/**
 * Site header template.
 *
 * ACF fields load from the page slug: header-settings (WPML-translatable).
 *
 * @package Eyes_Child
 */

$h = eyes_header_get_context();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">

	<header class="site-header<?php echo ( is_user_logged_in() && is_post_type_archive( 'concurso' ) ) ? ' site-header--dashboard' : ''; ?>">
		<div class="site-header__inner site-layout">
			<div class="site-header__brand">
				<a <?php echo eyes_acf_link_attrs( $h['logo_link'] ); ?> aria-label="<?php echo esc_attr( $h['logo_aria'] ); ?>">
					<img class="site-header__logo" src="<?php echo esc_url( $h['logo'] ); ?>" width="139" height="42" alt="<?php echo esc_attr( $h['logo_alt'] ); ?>">
				</a>
				<?php if ( ! is_user_logged_in() ) : ?>
				<span class="site-header__divider site-header__nav-desktop" aria-hidden="true"></span>
				<nav class="site-header__nav-desktop" aria-label="<?php echo esc_attr( eyes_header_text( 'header_nav_aria_label' ) ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'header_menu_1',
							'container'      => false,
							'menu_class'     => 'site-header__menu',
							'fallback_cb'    => false,
						)
					);
					?>
				</nav>
				<?php endif; ?>
			</div>

		

			<div class="site-header__actions">
				<?php if ( $h['languages'] ) : ?>
					<div class="lang-toggle" role="group" aria-label="<?php echo esc_attr( eyes_header_text( 'header_language_aria_label' ) ); ?>">
						<?php foreach ( $h['languages'] as $language_key => $language ) : ?>
							<?php
							$is_active = ! empty( $language['active'] );
							$code      = '';

							if ( ! empty( $language['language_code'] ) ) {
								$code = $language['language_code'];
							} elseif ( ! empty( $language['code'] ) ) {
								$code = $language['code'];
							} elseif ( is_string( $language_key ) ) {
								$code = $language_key;
							}

							$code = str_replace( '-PT', '', strtoupper( $code ) );
							$url  = ! empty( $language['url'] ) ? $language['url'] : '';
							?>
							<a class="<?php echo $is_active ? 'is-active' : 'is-inactive'; ?>" href="<?php echo esc_url( $url ); ?>"<?php echo $is_active ? ' aria-current="true"' : ''; ?>>
								<?php echo esc_html( $code ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( is_user_logged_in() ) : ?>
					<?php $current_user = wp_get_current_user(); ?>
					<a class="user-info" href="<?php echo esc_url( get_post_type_archive_link( 'concurso' ) ?: home_url( '/concurso/' ) ); ?>">
						<span class="user-info__name"><?php echo esc_html( eyes_current_user_full_name( $current_user ) ); ?></span>
						<img src="<?php echo esc_url( $h['avatar_uri'] ); ?>" alt="<?php esc_attr_e( 'User avatar', 'eyes-child' ); ?>" width="24" height="24" />
					</a>
				<?php else : ?>
					<a class="btn btn--primary site-header__nav-desktop test" <?php echo eyes_acf_link_attrs( $h['login'] ); ?>><?php echo esc_html( $h['login']['title'] ); ?></a>
					<a class="btn btn--outline-primary site-header__nav-desktop" <?php echo eyes_acf_link_attrs( $h['register'] ); ?>><?php echo esc_html( $h['register']['title'] ); ?></a>
				<?php endif; ?>
			</div>
			<button
				type="button"
				class="site-header__burger"
				aria-expanded="false"
				aria-controls="mobile-drawer"
				id="menu-open"
				data-open-label="<?php echo esc_attr( eyes_header_text( 'header_menu_open_label' ) ); ?>"
				data-close-label="<?php echo esc_attr( eyes_header_text( 'header_menu_close_label' ) ); ?>"
			>
				<span></span><span></span><span></span>
				<span class="visually-hidden"><?php echo esc_html( eyes_header_text( 'header_menu_open_label' ) ); ?></span>
			</button>
		</div>
	</header>

	<div class="site-header__drawer" id="mobile-drawer" hidden aria-hidden="true">
		<div class="site-header__drawer-panel">
		<button
	type="button"
	class="site-header__drawer-close"
	aria-label="Close menu"
	id="menu-close"
>
	&times;
</button>
			<?php if ( $h['languages'] ) : ?>
	<div class="lang-toggle lang-toggle--mobile" role="group" aria-label="<?php echo esc_attr( eyes_header_text( 'header_language_aria_label' ) ); ?>">
		<?php foreach ( $h['languages'] as $language_key => $language ) : ?>
			<?php
			$is_active = ! empty( $language['active'] );
			$code      = '';

			if ( ! empty( $language['language_code'] ) ) {
				$code = $language['language_code'];
			} elseif ( ! empty( $language['code'] ) ) {
				$code = $language['code'];
			} elseif ( is_string( $language_key ) ) {
				$code = $language_key;
			}

			$code = str_replace( '-PT', '', strtoupper( $code ) );
			$url  = ! empty( $language['url'] ) ? $language['url'] : '';
			?>
			<a class="<?php echo $is_active ? 'is-active' : 'is-inactive'; ?>" href="<?php echo esc_url( $url ); ?>"<?php echo $is_active ? ' aria-current="true"' : ''; ?>>
				<?php echo esc_html( $code ); ?>
			</a>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
			<nav aria-label="<?php echo esc_attr( eyes_header_text( 'header_mobile_nav_aria_label' ) ); ?>">
				<?php if ( ! is_user_logged_in() ) : ?>
					<a class="btn btn--nav-text" <?php echo eyes_acf_link_attrs( $h['nav_1'] ); ?>><?php echo esc_html( $h['nav_1']['title'] ); ?></a>
					<a class="btn btn--nav-text" <?php echo eyes_acf_link_attrs( $h['nav_2'] ); ?>><?php echo esc_html( $h['nav_2']['title'] ); ?></a>
					<a class="btn btn--nav-text" <?php echo eyes_acf_link_attrs( $h['nav_3'] ); ?>><?php echo esc_html( $h['nav_3']['title'] ); ?></a>
					<a class="btn btn--primary" <?php echo eyes_acf_link_attrs( $h['login'] ); ?>><?php echo esc_html( $h['login']['title'] ); ?></a>
					<a class="btn btn--outline-primary" <?php echo eyes_acf_link_attrs( $h['register'] ); ?>><?php echo esc_html( $h['register']['title'] ); ?></a>
				<?php else : ?>
					<?php $current_user = wp_get_current_user(); ?>
					<a class="user-info" href="<?php echo esc_url( get_post_type_archive_link( 'concurso' ) ?: home_url( '/concurso/' ) ); ?>">
						<span class="user-info__name"><?php echo esc_html( eyes_current_user_full_name( $current_user ) ); ?></span>
						<img src="<?php echo esc_url( $h['avatar_uri'] ); ?>" alt="<?php esc_attr_e( 'User avatar', 'eyes-child' ); ?>" width="24" height="24" />
					</a>
				<?php endif; ?>
			</nav>
		</div>
	</div>
