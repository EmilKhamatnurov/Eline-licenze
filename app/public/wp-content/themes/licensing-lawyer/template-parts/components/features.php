<?php
/**
 * Компонент «Пункты с линией сверху» — заголовок и текст (макеты Outsourcing «Что мы берём на себя»,
 * LegalAI «Безопасность», About «Как мы работаем»).
 *
 * Аргументы:
 *   items — [ [ 'title' => …, 'text' => … ], … ];
 *   min   — минимальная ширина колонки, px (по умолчанию 260);
 *   class — дополнительный класс.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'items' => [],
		'min'   => 260,
		'class' => '',
	]
);

$sky_items = array_values( array_filter( (array) $args['items'], static fn( $i ) => is_array( $i ) && ! empty( $i['title'] ) ) );
if ( ! $sky_items ) {
	return;
}
?>
<ul class="features <?php echo esc_attr( $args['class'] ); ?>" style="--features-min: <?php echo (int) $args['min']; ?>px">
	<?php foreach ( $sky_items as $sky_item ) : ?>
		<li class="features__item">
			<h3 class="features__title"><?php echo esc_html( $sky_item['title'] ); ?></h3>
			<?php if ( ! empty( $sky_item['text'] ) ) : ?>
				<p class="features__text"><?php echo esc_html( $sky_item['text'] ); ?></p>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
