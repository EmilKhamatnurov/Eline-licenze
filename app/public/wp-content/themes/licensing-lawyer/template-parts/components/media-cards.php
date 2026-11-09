<?php
/**
 * Компонент «Карточки с местом под фото» — пунктирная рамка, плашка-заглушка медиа, подпись
 * (макеты Main «Нам доверяют», About «Команда»). Только заглушки: фото, имена и отзывы даёт клиент.
 *
 * Аргументы:
 *   items  — [ [ 'media' => '[Фото]', 'title' => …, 'text' => … ], … ];
 *   min    — минимальная ширина колонки, px (по умолчанию 260);
 *   height — высота плашки медиа, px (по умолчанию 120);
 *   class  — дополнительный класс.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'items'  => [],
		'min'    => 260,
		'height' => 120,
		'class'  => '',
	]
);

$sky_items = array_values( array_filter( (array) $args['items'], 'is_array' ) );
if ( ! $sky_items ) {
	return;
}
?>
<ul class="media-cards <?php echo esc_attr( $args['class'] ); ?>" style="--media-min: <?php echo (int) $args['min']; ?>px; --media-height: <?php echo (int) $args['height']; ?>px">
	<?php foreach ( $sky_items as $sky_item ) : ?>
		<li class="media-cards__item">
			<div class="media-cards__media"><?php echo esc_html( (string) ( $sky_item['media'] ?? '' ) ); ?></div>
			<p class="media-cards__title"><?php echo esc_html( (string) ( $sky_item['title'] ?? '' ) ); ?></p>
			<?php if ( ! empty( $sky_item['text'] ) ) : ?>
				<p class="media-cards__text"><?php echo esc_html( $sky_item['text'] ); ?></p>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
