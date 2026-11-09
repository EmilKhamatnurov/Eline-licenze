<?php
/**
 * Компонент «Таблица сравнения» (макет Outsourcing «Аутсорс или штатный юрист»).
 * На узком экране таблица прокручивается в своём контейнере, страница — нет (DESIGN.md §9).
 *
 * Аргументы:
 *   columns   — заголовки столбцов значений: [ 'Юрист на аутсорсе', 'Штатный юрист' ];
 *   rows      — [ [ 'Стоимость', 'значение 1', 'значение 2' ], … ];
 *   highlight — номер столбца значений, выделенного цветом (1 — первый; 0 — без выделения);
 *   caption   — подпись таблицы для экранного диктора;
 *   class     — дополнительный класс.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'columns'   => [],
		'rows'      => [],
		'highlight' => 1,
		'caption'   => '',
		'class'     => '',
	]
);

if ( ! $args['columns'] || ! $args['rows'] ) {
	return;
}
?>
<div class="compare <?php echo esc_attr( $args['class'] ); ?>" role="region" aria-label="<?php echo esc_attr( $args['caption'] ? $args['caption'] : 'Таблица сравнения' ); ?>" tabindex="0">
	<table class="compare__table">
		<?php if ( $args['caption'] ) : ?>
			<caption class="screen-reader-text"><?php echo esc_html( $args['caption'] ); ?></caption>
		<?php endif; ?>
		<thead>
			<tr>
				<td></td>
				<?php foreach ( (array) $args['columns'] as $sky_i => $sky_col ) : ?>
					<th scope="col"<?php echo (int) $args['highlight'] === $sky_i + 1 ? ' class="is-highlight"' : ''; ?>><?php echo esc_html( $sky_col ); ?></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( (array) $args['rows'] as $sky_row ) : ?>
				<tr>
					<th scope="row"><?php echo esc_html( (string) array_shift( $sky_row ) ); ?></th>
					<?php foreach ( array_values( $sky_row ) as $sky_i => $sky_cell ) : ?>
						<td<?php echo (int) $args['highlight'] === $sky_i + 1 ? '' : ' class="is-muted"'; ?>><?php echo esc_html( (string) $sky_cell ); ?></td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
