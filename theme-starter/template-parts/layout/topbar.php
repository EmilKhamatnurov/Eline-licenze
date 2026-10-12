<?php
/**
 * Верхняя полоса: режим работы, телефон, мессенджеры. На телефоне скрыта — там кнопка звонка в шапке.
 * Пустые контакты выводятся заглушками без ссылок (DESIGN.md §8).
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_text  = (string) get_theme_mod( 'sky_topbar_text', 'Работаем дистанционно по всей России · Пн–Пт 9:00–18:00' );
$sky_phone = sky_starter_org( 'phone' );
$sky_tel   = sky_starter_tel( $sky_phone );
$sky_links = [
	'Telegram' => sky_starter_org( 'telegram' ),
	'WhatsApp' => sky_starter_org( 'whatsapp' ),
];
?>
<div class="topbar">
	<div class="sky-container topbar__inner">
		<?php if ( '' !== $sky_text ) : ?>
			<p class="topbar__text"><?php echo esc_html( $sky_text ); ?></p>
		<?php endif; ?>
		<ul class="topbar__contacts">
			<li>
				<?php if ( $sky_tel ) : ?>
					<a class="topbar__phone" href="<?php echo esc_url( $sky_tel ); ?>"><?php echo esc_html( $sky_phone ); ?></a>
				<?php else : ?>
					<span class="topbar__phone">[ТЕЛЕФОН]</span>
				<?php endif; ?>
			</li>
			<?php foreach ( $sky_links as $sky_label => $sky_url ) : ?>
				<li>
					<?php if ( $sky_url ) : ?>
						<a href="<?php echo esc_url( $sky_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $sky_label ); ?></a>
					<?php else : ?>
						<span><?php echo esc_html( $sky_label ); ?></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
