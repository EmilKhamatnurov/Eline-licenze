<?php
/**
 * Компонент «Списки с переключателем» — документы для юрлица и ИП (DESIGN.md §5, §10).
 *
 * Аргументы:
 *   id     — префикс id вкладок, уникальный на странице;
 *   groups — [ [ 'label' => 'Юридическое лицо', 'items' => [ … ] ], … ]; пустые группы пропускаются;
 *   label  — подпись группы вкладок для экранного диктора;
 *   class  — дополнительный класс.
 *
 * Без JS видны все списки с заголовками. assets/js/ui.js превращает их во вкладки:
 * role="tablist"/"tab"/"tabpanel", стрелки ← →, Home, End.
 * Если непустая группа одна — выводится просто список.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'id'     => 'docs',
		'groups' => [],
		'label'  => 'Тип заявителя',
		'class'  => '',
	]
);

$sky_groups = [];
foreach ( (array) $args['groups'] as $sky_group ) {
	$sky_items = sky_lines( $sky_group['items'] ?? [] );
	if ( $sky_items ) {
		$sky_groups[] = [
			'label' => (string) ( $sky_group['label'] ?? '' ),
			'items' => $sky_items,
		];
	}
}
if ( ! $sky_groups ) {
	return;
}

$sky_id   = sanitize_html_class( $args['id'] );
$sky_tabs = count( $sky_groups ) > 1;
if ( $sky_tabs ) {
	wp_enqueue_script( 'sky-ui' );
}
?>
<div class="doc-tabs <?php echo esc_attr( $args['class'] ); ?>"<?php echo $sky_tabs ? ' data-tabs' : ''; ?>>
	<?php if ( $sky_tabs ) : ?>
		<div class="doc-tabs__list" role="tablist" aria-label="<?php echo esc_attr( $args['label'] ); ?>" hidden>
			<?php foreach ( $sky_groups as $sky_i => $sky_group ) : ?>
				<button class="doc-tabs__tab" type="button" role="tab" id="<?php echo esc_attr( "{$sky_id}-tab-{$sky_i}" ); ?>" aria-controls="<?php echo esc_attr( "{$sky_id}-panel-{$sky_i}" ); ?>" aria-selected="<?php echo 0 === $sky_i ? 'true' : 'false'; ?>"<?php echo 0 === $sky_i ? '' : ' tabindex="-1"'; ?>><?php echo esc_html( $sky_group['label'] ); ?></button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php foreach ( $sky_groups as $sky_i => $sky_group ) : ?>
		<div class="doc-tabs__panel" id="<?php echo esc_attr( "{$sky_id}-panel-{$sky_i}" ); ?>"<?php echo $sky_tabs ? ' role="tabpanel" aria-labelledby="' . esc_attr( "{$sky_id}-tab-{$sky_i}" ) . '"' : ''; ?>>
			<?php if ( $sky_tabs ) : ?>
				<h3 class="doc-tabs__heading"><?php echo esc_html( $sky_group['label'] ); ?></h3>
			<?php endif; ?>
			<ol class="doc-tabs__items">
				<?php foreach ( $sky_group['items'] as $sky_item ) : ?>
					<li><?php echo esc_html( $sky_item ); ?></li>
				<?php endforeach; ?>
			</ol>
		</div>
	<?php endforeach; ?>
</div>
