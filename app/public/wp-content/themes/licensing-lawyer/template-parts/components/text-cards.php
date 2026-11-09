<?php
/**
 * Компонент «Карточки с меткой» — метка, заголовок, текст (макет About «Кейсы и отзывы»).
 *
 * Аргументы:
 *   items — [ [ 'tag' => 'Кейс', 'title' => …, 'text' => … ], … ];
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

$sky_items = array_values( array_filter( (array) $args['items'], static fn( $i ) => is_array( $i ) && ! empty( $i['title'] ) ) );
if ( ! $sky_items ) {
	return;
}
?>
<ul class="text-cards <?php echo esc_attr( $args['class'] ); ?>">
	<?php foreach ( $sky_items as $sky_item ) : ?>
		<li class="text-cards__item">
			<?php if ( ! empty( $sky_item['tag'] ) ) : ?>
				<span class="text-cards__tag"><?php echo esc_html( $sky_item['tag'] ); ?></span>
			<?php endif; ?>
			<h3 class="text-cards__title"><?php echo esc_html( $sky_item['title'] ); ?></h3>
			<?php if ( ! empty( $sky_item['text'] ) ) : ?>
				<p class="text-cards__text"><?php echo esc_html( $sky_item['text'] ); ?></p>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
