<?php
/**
 * Компонент «Вопросы и ответы» — аккордеон (DESIGN.md §5 FAQ): кнопка на всю строку, «+» / «–»,
 * aria-expanded, первый вопрос открыт. Без JS все ответы видны.
 *
 * Аргументы:
 *   items  — [ [ 'question' => …, 'answer' => … ], … ] (ключи q/a тоже подходят);
 *   id     — префикс id, уникальный на странице;
 *   schema — добавить FAQPage. Если вопросы уже переданы в sky_schema_add_faq()
 *            до get_header() (разметка попадёт в <head>), передайте false;
 *   class  — дополнительный класс.
 *
 * Заголовок секции выводит шаблон.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'items'  => [],
		'id'     => 'faq',
		'schema' => true,
		'class'  => '',
	]
);

$sky_items = [];
foreach ( (array) $args['items'] as $sky_item ) {
	$sky_q = is_array( $sky_item ) ? (string) ( $sky_item['question'] ?? $sky_item['q'] ?? '' ) : '';
	$sky_a = is_array( $sky_item ) ? (string) ( $sky_item['answer'] ?? $sky_item['a'] ?? '' ) : '';
	if ( '' !== trim( $sky_q ) && '' !== trim( $sky_a ) ) {
		$sky_items[] = [
			'question' => $sky_q,
			'answer'   => $sky_a,
		];
	}
}
if ( ! $sky_items ) {
	return;
}

if ( $args['schema'] && function_exists( 'sky_schema_add_faq' ) ) {
	sky_schema_add_faq( $sky_items );
}

$sky_id = sanitize_html_class( $args['id'] );
wp_enqueue_script( 'sky-ui' );
?>
<div class="faq <?php echo esc_attr( $args['class'] ); ?>" data-accordion>
	<?php foreach ( $sky_items as $sky_i => $sky_item ) : ?>
		<div class="faq__item">
			<h3 class="faq__question">
				<button class="faq__toggle" type="button" id="<?php echo esc_attr( "{$sky_id}-q-{$sky_i}" ); ?>" aria-controls="<?php echo esc_attr( "{$sky_id}-a-{$sky_i}" ); ?>" aria-expanded="true">
					<span><?php echo esc_html( $sky_item['question'] ); ?></span>
					<span class="faq__sign" aria-hidden="true"></span>
				</button>
			</h3>
			<div class="faq__answer" id="<?php echo esc_attr( "{$sky_id}-a-{$sky_i}" ); ?>" role="region" aria-labelledby="<?php echo esc_attr( "{$sky_id}-q-{$sky_i}" ); ?>">
				<?php echo wp_kses_post( wpautop( esc_html( $sky_item['answer'] ) ) ); ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
