<?php
/**
 * Компонент «Преимущества» (DESIGN.md §5): крупные приглушённые номера 01–06, две колонки.
 *
 * Аргументы:
 *   items — [ [ 'title' => …, 'text' => … ], … ];
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
<ul class="advantages <?php echo esc_attr( $args['class'] ); ?>">
	<?php foreach ( $sky_items as $sky_i => $sky_item ) : ?>
		<li class="advantages__item">
			<span class="advantages__num" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $sky_i + 1 ) ); ?></span>
			<div>
				<h3 class="advantages__title"><?php echo esc_html( $sky_item['title'] ); ?></h3>
				<?php if ( ! empty( $sky_item['text'] ) ) : ?>
					<p class="advantages__text"><?php echo esc_html( $sky_item['text'] ); ?></p>
				<?php endif; ?>
			</div>
		</li>
	<?php endforeach; ?>
</ul>
