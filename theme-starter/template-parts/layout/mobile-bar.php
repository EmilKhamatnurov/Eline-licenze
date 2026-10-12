<?php
/**
 * Закреплённая панель действий на телефоне (DESIGN.md §5): «Позвонить» и «Написать»,
 * на странице лицензии — «Заказать». Текст и ссылку второй кнопки меняет фильтр sky_starter_mobile_bar.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_anchor = sky_starter_form_anchor();
$sky_tel    = sky_starter_tel( sky_starter_org( 'phone' ) );
$sky_chat   = sky_starter_org( 'telegram' );
$sky_chat   = '' !== $sky_chat ? $sky_chat : sky_starter_org( 'whatsapp' );

$sky_bar = (array) apply_filters(
	'sky_starter_mobile_bar',
	[
		'action'     => is_singular( 'license' ) ? 'Заказать' : 'Написать',
		'action_url' => ( is_singular( 'license' ) || '' === $sky_chat ) ? $sky_anchor : $sky_chat,
	]
);
?>
<div class="mobile-bar">
	<a class="btn btn--sm btn--secondary mobile-bar__btn" href="<?php echo esc_url( $sky_tel ? $sky_tel : $sky_anchor ); ?>">Позвонить</a>
	<a class="btn btn--sm mobile-bar__btn" href="<?php echo esc_url( (string) $sky_bar['action_url'] ); ?>"><?php echo esc_html( (string) $sky_bar['action'] ); ?></a>
</div>
