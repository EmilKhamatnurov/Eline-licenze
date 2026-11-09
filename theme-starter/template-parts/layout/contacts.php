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
<?php if ( $sky_tel || is_email( $sky_email ) || $sky_address || $sky_chats ) : ?>
<ul class="contacts-list">
	<?php if ( $sky_tel ) : ?>
	<li class="contacts-list__phone">
			<a href="<?php echo esc_url( $sky_tel ); ?>"><?php echo esc_html( $sky_phone ); ?></a>
	</li>
	<?php endif; ?>
	<?php if ( is_email( $sky_email ) ) : ?>
	<li>
			<a href="<?php echo esc_url( 'mailto:' . $sky_email ); ?>"><?php echo esc_html( $sky_email ); ?></a>
	</li>
	<?php endif; ?>
	<?php if ( $sky_address ) : ?><li><?php echo esc_html( $sky_address ); ?></li><?php endif; ?>
	<?php if ( $sky_chats ) : ?>
	<li>
			<?php
			$sky_links = [];
			foreach ( $sky_chats as $sky_label => $sky_url ) {
				$sky_links[] = sprintf( '<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url( $sky_url ), esc_html( $sky_label ) );
			}
			echo implode( ' · ', $sky_links ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- собрано из экранированных частей.
			?>
	</li>
	<?php endif; ?>
</ul>
<?php endif; ?>
