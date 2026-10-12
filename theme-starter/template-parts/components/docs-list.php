<?php
/**
 * Компонент «Список документов».
 *
 * Аргументы:
 *   title — заголовок секции;
 *   items — строки или [ [ 'name' => …, 'note' => … ], … ]; строка с переводами строк тоже подходит;
 *   class — доп. класс секции.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'title' => 'Какие документы нужны',
		'items' => [],
		'class' => 'sky-section--surface',
	]
);

$sky_items = sky_lines( $args['items'] );
if ( ! $sky_items ) {
	return;
}
?>
<section class="sky-section sky-docs <?php echo esc_attr( $args['class'] ); ?>">
	<div class="sky-container sky-container--narrow">
		<?php if ( $args['title'] ) : ?>
			<h2 class="sky-section__title"><?php echo esc_html( $args['title'] ); ?></h2>
		<?php endif; ?>
		<ul class="sky-docs__list">
			<?php foreach ( $sky_items as $sky_doc ) : ?>
				<li class="sky-docs__item">
					<?php if ( is_array( $sky_doc ) ) : ?>
						<?php echo esc_html( $sky_doc['name'] ?? '' ); ?>
						<?php if ( ! empty( $sky_doc['note'] ) ) : ?>
							<span class="sky-docs__note"><?php echo esc_html( $sky_doc['note'] ); ?></span>
						<?php endif; ?>
					<?php else : ?>
						<?php echo esc_html( (string) $sky_doc ); ?>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
