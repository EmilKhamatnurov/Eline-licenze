<?php
/**
 * Компонент «Вкладки-чипы» для разделов страницы (макеты Documents, LegalAI, About).
 * Вкладки — assets/js/ui.js ([data-tabs]): ← → Home End. Без JS видны все панели со своими заголовками.
 *
 * Аргументы:
 *   id      — префикс id, уникальный на странице;
 *   label   — подпись группы вкладок для экранного диктора;
 *   panels  — [ [ 'label' => 'Договоры', 'html' => '…' ], … ] — html готовится компонентами (sky_get_component);
 *   heading — уровень заголовка панели без JS: h3 (по умолчанию);
 *   class   — дополнительный класс.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'id'      => 'tabs',
		'label'   => 'Разделы',
		'panels'  => [],
		'heading' => 'h3',
		'class'   => '',
	]
);

$sky_panels = array_values( array_filter( (array) $args['panels'], static fn( $p ) => is_array( $p ) && '' !== trim( (string) ( $p['html'] ?? '' ) ) ) );
if ( ! $sky_panels ) {
	return;
}
$sky_id  = sanitize_html_class( $args['id'] );
$sky_tag = in_array( $args['heading'], [ 'h2', 'h3', 'h4' ], true ) ? $args['heading'] : 'h3';
wp_enqueue_script( 'sky-ui' );
?>
<div class="tabs <?php echo esc_attr( $args['class'] ); ?>" data-tabs>
	<div class="tabs__list" role="tablist" aria-label="<?php echo esc_attr( $args['label'] ); ?>" hidden>
		<?php foreach ( $sky_panels as $sky_i => $sky_panel ) : ?>
			<button class="tabs__tab" type="button" role="tab" id="<?php echo esc_attr( "{$sky_id}-tab-{$sky_i}" ); ?>" aria-controls="<?php echo esc_attr( "{$sky_id}-panel-{$sky_i}" ); ?>" aria-selected="<?php echo 0 === $sky_i ? 'true' : 'false'; ?>"<?php echo 0 === $sky_i ? '' : ' tabindex="-1"'; ?>><?php echo esc_html( (string) $sky_panel['label'] ); ?></button>
		<?php endforeach; ?>
	</div>
	<?php foreach ( $sky_panels as $sky_i => $sky_panel ) : ?>
		<div class="tabs__panel" id="<?php echo esc_attr( "{$sky_id}-panel-{$sky_i}" ); ?>" role="tabpanel" aria-labelledby="<?php echo esc_attr( "{$sky_id}-tab-{$sky_i}" ); ?>">
			<<?php echo esc_html( $sky_tag ); ?> class="tabs__heading"><?php echo esc_html( (string) $sky_panel['label'] ); ?></<?php echo esc_html( $sky_tag ); ?>>
			<?php echo $sky_panel['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML собран компонентами, они экранируют сами. ?>
		</div>
	<?php endforeach; ?>
</div>
