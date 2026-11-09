<?php
/**
 * Главная: «Виды лицензий» — вкладки-направления и лицензии из тех же записей, что каталог
 * (DESIGN.md §5 «Каталог лицензий»). Вкладки — assets/js/ui.js; без JS видны все направления с заголовками.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_regs   = sky_starter_regulators();
$sky_panels = [];
foreach ( $sky_regs as $sky_reg ) {
	$sky_posts = get_posts(
		[
			'post_type'      => 'license',
			'posts_per_page' => -1,
			'orderby'        => [
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			],
			'no_found_rows'  => true,
			'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- по запросу на направление, их немного.
				[
					'taxonomy' => 'regulator',
					'terms'    => $sky_reg->term_id,
				],
			],
		]
	);
	if ( $sky_posts ) {
		$sky_panels[] = [
			'term'  => $sky_reg,
			'posts' => $sky_posts,
		];
	}
}
if ( ! $sky_panels ) {
	return;
}
wp_enqueue_script( 'sky-ui' );
?>
<section class="home-section home-catalog" id="catalog" aria-labelledby="catalog-title">
	<div class="sky-container">
		<h2 class="home-section__title" id="catalog-title">Виды лицензий</h2>
		<p class="home-section__lead">У каждой лицензии своя страница: требования, документы, сроки и цена.</p>

		<div class="home-catalog__layout" data-tabs>
			<div class="home-catalog__tabs" role="tablist" aria-label="Направления лицензирования" hidden>
				<?php foreach ( $sky_panels as $sky_i => $sky_panel ) : ?>
					<button class="home-catalog__tab" type="button" role="tab" id="hc-tab-<?php echo (int) $sky_i; ?>" aria-controls="hc-panel-<?php echo (int) $sky_i; ?>" aria-selected="<?php echo 0 === $sky_i ? 'true' : 'false'; ?>"<?php echo 0 === $sky_i ? '' : ' tabindex="-1"'; ?>><?php echo esc_html( $sky_panel['term']->name ); ?></button>
				<?php endforeach; ?>
			</div>

			<div class="home-catalog__panels">
				<?php foreach ( $sky_panels as $sky_i => $sky_panel ) : ?>
					<div class="home-catalog__panel" id="hc-panel-<?php echo (int) $sky_i; ?>" role="tabpanel" aria-labelledby="hc-tab-<?php echo (int) $sky_i; ?>">
						<h3 class="home-catalog__heading" data-tabs-heading><a href="<?php echo esc_url( get_term_link( $sky_panel['term'] ) ); ?>"><?php echo esc_html( $sky_panel['term']->name ); ?></a></h3>
						<ul class="home-catalog__list">
							<?php foreach ( $sky_panel['posts'] as $sky_post ) : ?>
								<li>
									<a class="catalog-row" href="<?php echo esc_url( get_permalink( $sky_post ) ); ?>">
										<span class="catalog-row__name"><?php echo esc_html( get_the_title( $sky_post ) ); ?></span>
										<span class="catalog-row__meta"><?php echo esc_html( sky_starter_price_label( sky_field( 'price_from', $sky_post->ID ) ) . ' · ' . sky_starter_term_label( sky_field( 'term_from', $sky_post->ID ) ) ); ?></span>
										<span class="catalog-row__arrow" aria-hidden="true">→</span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
				<p class="home-catalog__more">Не нашли свой вид деятельности? <a href="<?php echo esc_url( sky_starter_form_anchor() ); ?>">Опишите задачу</a> — подберём лицензию бесплатно. Или откройте <a href="<?php echo esc_url( get_post_type_archive_link( 'license' ) ); ?>">весь каталог</a>.</p>
			</div>
		</div>
	</div>
</section>
