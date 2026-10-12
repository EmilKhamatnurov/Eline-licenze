<?php
/**
 * Компонент «Прайс-строки» — название, пояснение, цена и срок, кнопка (макет Documents «Что мы готовим»).
 * Цены не придумываются: пустая цена — «от [ЦЕНА] ₽», пустой срок — «[СРОК]» (DESIGN.md §8).
 *
 * Аргументы:
 *   items      — [ [ 'name' => …, 'note' => …, 'price' => число или '', 'term' => строка или '' ], … ];
 *   button     — текст кнопки (по умолчанию «Заказать»);
 *   button_url — якорь формы; название строки подставляется в поле «Что нужно» (data-lead-topic);
 *   class      — дополнительный класс.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'items'      => [],
		'button'     => 'Заказать',
		'button_url' => sky_starter_form_anchor(),
		'class'      => '',
	]
);

$sky_items = array_values( array_filter( (array) $args['items'], static fn( $i ) => is_array( $i ) && ! empty( $i['name'] ) ) );
if ( ! $sky_items ) {
	return;
}
?>
<ul class="price-list <?php echo esc_attr( $args['class'] ); ?>">
	<?php foreach ( $sky_items as $sky_item ) : ?>
		<?php $sky_term = trim( (string) ( $sky_item['term'] ?? '' ) ); ?>
		<li class="price-list__row">
			<div class="price-list__body">
				<p class="price-list__name"><?php echo esc_html( $sky_item['name'] ); ?></p>
				<?php if ( ! empty( $sky_item['note'] ) ) : ?>
					<p class="price-list__note"><?php echo esc_html( $sky_item['note'] ); ?></p>
				<?php endif; ?>
			</div>
			<p class="price-list__meta"><?php echo esc_html( sky_starter_price_label( (string) ( $sky_item['price'] ?? '' ) ) . ' · ' . ( '' !== $sky_term ? $sky_term : '[СРОК]' ) ); ?></p>
			<a class="btn btn--sm btn--secondary price-list__btn" href="<?php echo esc_url( $args['button_url'] ); ?>" data-lead-topic="<?php echo esc_attr( $sky_item['name'] ); ?>"><?php echo esc_html( $args['button'] ); ?><span class="screen-reader-text">: <?php echo esc_html( $sky_item['name'] ); ?></span></a>
		</li>
	<?php endforeach; ?>
</ul>
