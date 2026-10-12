<?php
/**
 * Компонент «Этапы» — вертикальная лента: номер в круге, линия, заголовок, текст, срок.
 *
 * Аргументы:
 *   items   — [ [ 'title' => …, 'text' => …, 'term' => … ], … ]; term необязателен;
 *   variant — list (лента, по умолчанию) | row (от 768 px — горизонтальный ряд: точка, линия,
 *             крупный номер 01–05; на телефоне — та же лента с кружками) | cards (сетка: линия сверху,
 *             крупный номер 01–05 на любой ширине — макеты Outsourcing, Documents, LegalAI);
 *   class   — дополнительный класс.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'items'   => [],
		'variant' => 'list',
		'class'   => '',
	]
);

$sky_items = array_values(
	array_filter(
		(array) $args['items'],
		static fn( $item ): bool => is_array( $item ) && '' !== trim( (string) ( $item['title'] ?? '' ) )
	)
);
if ( ! $sky_items ) {
	return;
}
?>
<ol class="steps<?php echo in_array( $args['variant'], [ 'row', 'cards' ], true ) ? ' steps--' . esc_attr( $args['variant'] ) : ''; ?> <?php echo esc_attr( $args['class'] ); ?>">
	<?php foreach ( $sky_items as $sky_i => $sky_item ) : ?>
		<li class="steps__item">
			<span class="steps__num" aria-hidden="true" data-pad="<?php echo esc_attr( sprintf( '%02d', $sky_i + 1 ) ); ?>"><?php echo (int) $sky_i + 1; ?></span>
			<div class="steps__body">
				<h3 class="steps__title"><?php echo esc_html( $sky_item['title'] ); ?></h3>
				<?php if ( '' !== trim( (string) ( $sky_item['text'] ?? '' ) ) ) : ?>
					<p class="steps__text"><?php echo esc_html( $sky_item['text'] ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== trim( (string) ( $sky_item['term'] ?? '' ) ) ) : ?>
					<p class="steps__term"><span class="screen-reader-text">Срок: </span><?php echo esc_html( $sky_item['term'] ); ?></p>
				<?php endif; ?>
			</div>
		</li>
	<?php endforeach; ?>
</ol>
