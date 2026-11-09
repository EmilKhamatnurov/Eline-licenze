<?php
/**
 * Шапка: печать-логотип, название, меню, «Получить консультацию».
 * Десктоп (от 1024): логотип и кнопка в первом ряду, меню — вторым рядом.
 * Телефон и планшет: кнопка звонка и бургер, меню раскрывается панелью (assets/js/header.js).
 * Без JS меню показано списком под шапкой, бургер скрыт (атрибут hidden).
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_name   = sky_starter_org( 'name' );
$sky_tel    = sky_starter_tel( sky_starter_org( 'phone' ) );
$sky_anchor = sky_starter_form_anchor();
?>
<header class="site-header" id="site-header">
	<div class="sky-container site-header__bar">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<span class="brand__seal" aria-hidden="true">§</span>
			<span class="brand__text">
				<span class="brand__name"><?php echo esc_html( '' !== $sky_name ? $sky_name : '[НАЗВАНИЕ]' ); ?></span>
				<span class="brand__tagline">Лицензии · юридическое сопровождение</span>
			</span>
		</a>

		<div class="site-header__actions">
			<a class="btn btn--sm site-header__cta" href="<?php echo esc_url( $sky_anchor ); ?>">Получить консультацию</a>
			<a class="icon-btn icon-btn--tint site-header__call" href="<?php echo esc_url( $sky_tel ? $sky_tel : $sky_anchor ); ?>" aria-label="Позвонить">
				<?php echo sky_starter_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- статичный SVG. ?>
			</a>
			<button class="icon-btn icon-btn--brand site-header__toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Открыть меню" hidden>
				<?php echo sky_starter_icon( 'menu' ) . sky_starter_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- статичный SVG. ?>
			</button>
		</div>
	</div>

	<nav class="site-nav" id="site-nav" aria-label="Основное меню">
		<div class="sky-container site-nav__inner">
			<?php
			wp_nav_menu(
				[
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'site-nav__list',
					'depth'          => 1,
					'fallback_cb'    => 'sky_starter_menu_fallback',
				]
			);
			?>
			<a class="btn btn--block site-nav__cta" href="<?php echo esc_url( $sky_anchor ); ?>">Получить консультацию</a>
		</div>
	</nav>
</header>
