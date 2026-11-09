<?php
/**
 * Компонент «Тарифы» — до трёх карточек, рекомендуемая на --brand-dark (DESIGN.md §5).
 *
 * Аргументы:
 *   items       — [ [ 'name' => …, 'who' => 'для кого', 'price_text' => '50 000 ₽', 'features' => [ … ] ], … ];
 *   featured    — номер рекомендуемого тарифа, 1–3 (0 — без выделения);
 *   segments    — переключатель «тип компании»: [ [ 'Малый бизнес / ИП', 'подсказка', номер тарифа ], … ].
 *                 Выбор подсвечивает тариф (DESIGN.md §5 «Тарифы», assets/js/ui.js). Без JS скрыт,
 *                 выделен тариф из featured;
 *   button      — текст кнопки;
 *   button_url  — ссылка кнопки (якорь формы);
 *   class       — дополнительный класс.
 *
 * Цена не придумывается: пустая — «[ЦЕНА] ₽» (sky_starter_money()).
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'items'      => [],
		'featured'   => 2,
		'segments'   => [],
		'button'     => 'Заказать',
		'button_url' => sky_starter_form_anchor(),
		'class'      => '',
	]
);

$sky_items = array_slice( array_values( array_filter( (array) $args['items'], 'is_array' ) ), 0, 3 );
if ( ! $sky_items ) {
	return;
}
$sky_segments = array_values( array_filter( (array) $args['segments'], 'is_array' ) );
if ( $sky_segments ) {
	wp_enqueue_script( 'sky-ui' );
}
?>
<div class="tariffs-block"<?php echo $sky_segments ? ' data-tariffs' : ''; ?>>
<?php if ( $sky_segments ) : ?>
	<div class="tariff-switch" data-tariff-switch hidden>
		<div class="tariff-switch__buttons" role="group" aria-label="Тип компании">
			<?php foreach ( $sky_segments as [ $sky_label, $sky_hint, $sky_pick ] ) : ?>
				<button class="tariff-switch__btn" type="button" aria-pressed="<?php echo (int) $sky_pick === (int) $args['featured'] ? 'true' : 'false'; ?>" data-featured="<?php echo (int) $sky_pick; ?>" data-hint="<?php echo esc_attr( $sky_hint ); ?>"><?php echo esc_html( $sky_label ); ?></button>
			<?php endforeach; ?>
		</div>
		<p class="tariff-switch__hint" aria-live="polite"></p>
	</div>
<?php endif; ?>
<ul class="tariffs <?php echo esc_attr( $args['class'] ); ?>">
	<?php
	foreach ( $sky_items as $sky_i => $sky_item ) :
		$sky_on = (int) $args['featured'] === $sky_i + 1;
		?>
		<li class="tariffs__item<?php echo $sky_on ? ' tariffs__item--featured' : ''; ?>">
			<p class="tariffs__name">
				<?php echo esc_html( (string) ( $sky_item['name'] ?? '' ) ); ?>
				<span class="screen-reader-text tariffs__flag"<?php echo $sky_on ? '' : ' hidden'; ?>>(рекомендуем)</span>
			</p>
			<?php if ( ! empty( $sky_item['who'] ) ) : ?>
				<p class="tariffs__who"><?php echo esc_html( $sky_item['who'] ); ?></p>
			<?php endif; ?>
			<p class="tariffs__price"><?php echo esc_html( (string) ( $sky_item['price_text'] ?? sky_starter_money( (string) ( $sky_item['price'] ?? '' ) ) ) ); ?></p>
			<?php $sky_features = sky_lines( $sky_item['features'] ?? [] ); ?>
			<?php if ( $sky_features ) : ?>
				<ul class="tariffs__features">
					<?php foreach ( $sky_features as $sky_feature ) : ?>
						<li><?php echo esc_html( $sky_feature ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<a class="btn btn--sm btn--block <?php echo $sky_on ? 'btn--light' : 'btn--secondary'; ?> tariffs__button" href="<?php echo esc_url( $args['button_url'] ); ?>" data-lead-topic="<?php echo esc_attr( 'Тариф: ' . (string) ( $sky_item['name'] ?? '' ) ); ?>"><?php echo esc_html( $args['button'] ); ?><span class="screen-reader-text">: <?php echo esc_html( (string) ( $sky_item['name'] ?? '' ) ); ?></span></a>
		</li>
	<?php endforeach; ?>
</ul>
</div>
