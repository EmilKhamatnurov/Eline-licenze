<?php
/**
 * Контакты списком (нижний блок главной): телефон, e-mail, адрес, мессенджеры.
 * Пустые значения — заглушки без ссылок (DESIGN.md §8).
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_phone   = sky_starter_org( 'phone' );
$sky_tel     = sky_starter_tel( $sky_phone );
$sky_email   = sky_starter_org( 'email' );
$sky_address = trim( implode( ', ', array_filter( [ sky_starter_org( 'city' ), sky_starter_org( 'street' ) ] ) ) );
$sky_chats   = array_filter(
	[
		'Telegram' => sky_starter_org( 'telegram' ),
		'WhatsApp' => sky_starter_org( 'whatsapp' ),
	]
);
?>
<ul class="contacts-list">
	<li class="contacts-list__phone">
		<?php if ( $sky_tel ) : ?>
			<a href="<?php echo esc_url( $sky_tel ); ?>"><?php echo esc_html( $sky_phone ); ?></a>
		<?php else : ?>
			[ТЕЛЕФОН]
		<?php endif; ?>
	</li>
	<li>
		<?php if ( is_email( $sky_email ) ) : ?>
			<a href="<?php echo esc_url( 'mailto:' . $sky_email ); ?>"><?php echo esc_html( $sky_email ); ?></a>
		<?php else : ?>
			[EMAIL]
		<?php endif; ?>
	</li>
	<li><?php echo esc_html( '' !== $sky_address ? $sky_address : '[АДРЕС ОФИСА]' ); ?></li>
	<li>
		<?php if ( $sky_chats ) : ?>
			<?php
			$sky_links = [];
			foreach ( $sky_chats as $sky_label => $sky_url ) {
				$sky_links[] = sprintf( '<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url( $sky_url ), esc_html( $sky_label ) );
			}
			echo implode( ' · ', $sky_links ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- собрано из экранированных частей.
			?>
		<?php else : ?>
			Telegram · WhatsApp: [КОНТАКТ]
		<?php endif; ?>
	</li>
</ul>
