<?php
/**
 * Компонент «Список с галочками» — требования к соискателю и т. п.
 *
 * Аргументы:
 *   items — массив строк или текст с переводами строк;
 *   class — дополнительный класс.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'items' => [],
		'class' => '',
	]
);

$sky_items = sky_lines( $args['items'] );
if ( ! $sky_items ) {
	return;
}
?>
<ul class="checklist <?php echo esc_attr( $args['class'] ); ?>">
	<?php foreach ( $sky_items as $sky_item ) : ?>
		<li class="checklist__item">
			<svg class="checklist__icon" aria-hidden="true" focusable="false" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
			<span><?php echo esc_html( $sky_item ); ?></span>
		</li>
	<?php endforeach; ?>
</ul>
