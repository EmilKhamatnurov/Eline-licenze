<?php
/**
 * Подвал: о компании, лицензии, услуги, реквизиты. Пустые реквизиты — заглушки (DESIGN.md §8).
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_name    = sky_starter_org( 'name' );
$sky_legal   = sky_starter_org( 'legal_name' );
$sky_inn     = sky_starter_org( 'inn' );
$sky_ogrn    = preg_replace( '/\D+/', '', sky_starter_org( 'ogrn' ) );
$sky_privacy = get_privacy_policy_url();
$sky_menus   = [
	'footer_licenses' => 'Лицензии',
	'footer_services' => 'Услуги',
];
?>
<footer class="site-footer">
	<div class="sky-container site-footer__grid">
		<div class="site-footer__col">
			<p class="site-footer__title"><?php echo esc_html( '' !== $sky_name ? $sky_name : '[НАЗВАНИЕ]' ); ?></p>
			<p class="site-footer__about">Лицензирование и юридическое сопровождение бизнеса.</p>
		</div>

		<?php foreach ( $sky_menus as $sky_location => $sky_title ) : ?>
			<nav class="site-footer__col" aria-label="<?php echo esc_attr( $sky_title ); ?>">
				<p class="site-footer__title"><?php echo esc_html( $sky_title ); ?></p>
				<?php
				wp_nav_menu(
					[
						'theme_location' => $sky_location,
						'container'      => false,
						'menu_class'     => 'site-footer__list',
						'depth'          => 1,
						'fallback_cb'    => 'sky_starter_menu_fallback',
					]
				);
				?>
			</nav>
		<?php endforeach; ?>

		<div class="site-footer__col">
			<p class="site-footer__title">Реквизиты</p>
			<ul class="site-footer__list">
				<li><?php echo esc_html( '' !== $sky_legal ? $sky_legal : '[ООО «НАЗВАНИЕ»]' ); ?></li>
				<li>ИНН <?php echo esc_html( '' !== $sky_inn ? $sky_inn : '[XXXXXXXXXX]' ); ?></li>
				<?php if ( $sky_ogrn ) : ?>
					<li><?php echo esc_html( ( 15 === strlen( $sky_ogrn ) ? 'ОГРНИП ' : 'ОГРН ' ) . $sky_ogrn ); ?></li>
				<?php endif; ?>
				<li>
					<?php if ( $sky_privacy ) : ?>
						<a class="site-footer__underline" href="<?php echo esc_url( $sky_privacy ); ?>">Политика конфиденциальности</a>
					<?php else : ?>
						<span>[Политика конфиденциальности]</span>
					<?php endif; ?>
				</li>
			</ul>
		</div>
	</div>
</footer>
