<?php
/**
 * Каталог лицензий (макет Licenses; DESIGN.md §5 «Каталог лицензий»):
 * поиск, чипы направлений, счётчик, карточки, пустое состояние с формой.
 * Общий для архива /licenzirovanie/ и раздела /licenzirovanie/<regulator>/ — отличается выбранным направлением.
 *
 * Выводятся все лицензии; не подходящие под направление и ?q= — с атрибутом hidden.
 * Без JS: чипы — ссылки на разделы, поиск — GET-форма (?q=). С JS (assets/js/catalog.js):
 * фильтр на месте, адрес, H1 и Title меняются через history API.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_current = ( $args['term'] ?? null ) instanceof WP_Term ? $args['term'] : null;
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- поисковый запрос из GET, только чтение.
$sky_q    = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );
$sky_qn   = sky_starter_search_norm( $sky_q );
$sky_view = sky_starter_catalog_view( $sky_current );
$sky_regs = sky_starter_regulators();

$sky_views = [ sky_starter_catalog_view( null ) ];
foreach ( $sky_regs as $sky_reg ) {
	$sky_views[] = sky_starter_catalog_view( $sky_reg );
}

$sky_posts = get_posts(
	[
		'post_type'      => 'license',
		'posts_per_page' => -1,
		'orderby'        => [
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		],
		'no_found_rows'  => true,
	]
);

// Карточки: направление, строка поиска, видимость при текущих фильтрах.
$sky_cards = [];
$sky_shown = 0;
foreach ( $sky_posts as $sky_post ) {
	$sky_reg    = sky_starter_license_regulator( $sky_post->ID );
	$sky_search = sky_starter_search_norm( get_the_title( $sky_post ) . ' ' . ( $sky_reg ? $sky_reg->name : '' ) );
	$sky_words  = '' !== $sky_qn ? explode( ' ', $sky_qn ) : [];
	$sky_match  = ( ! $sky_current || ( $sky_reg && $sky_reg->term_id === $sky_current->term_id ) )
		&& ! array_filter( $sky_words, static fn( $w ) => ! str_contains( $sky_search, $w ) );
	$sky_shown += $sky_match ? 1 : 0;

	$sky_cards[] = [
		'post'   => $sky_post,
		'reg'    => $sky_reg,
		'search' => $sky_search,
		'match'  => $sky_match,
		'price'  => sky_starter_price_label( sky_field( 'price_from', $sky_post->ID ) ),
		'term'   => sky_starter_term_label( sky_field( 'term_from', $sky_post->ID ) ),
	];
}

$sky_home_label = function_exists( 'sky_config' ) ? (string) sky_config( 'breadcrumbs.home_label', 'Главная' ) : 'Главная';
?>
<div id="main" class="catalog" data-catalog
	data-home-url="<?php echo esc_url( home_url( '/' ) ); ?>"
	data-home-label="<?php echo esc_attr( $sky_home_label ); ?>"
	data-archive-url="<?php echo esc_url( $sky_views[0]['url'] ); ?>"
	data-archive-label="<?php echo esc_attr( (string) get_post_type_object( 'license' )->labels->name ); ?>">

	<section class="page-hero catalog-page-hero">
		<div class="catalog-page-hero__image" aria-hidden="true"></div>
		<div class="sky-container catalog-page-hero__container">
			<div class="page-hero__crumbs">
				<?php sky_component( 'breadcrumbs' ); ?>
			</div>

			<div class="catalog-hero">
				<p class="catalog-eyebrow">Юридическое сопровождение бизнеса</p>
				<h1 class="page-hero__title" data-catalog-h1><?php echo esc_html( $sky_view['h1'] ); ?></h1>
				<p class="page-hero__lead" data-catalog-lead><?php echo esc_html( $sky_view['lead'] ); ?></p>

				<form class="catalog-search" role="search" method="get" action="<?php echo esc_url( $sky_view['url'] ); ?>">
					<label class="catalog-search__label" for="catalog-q">Поиск по каталогу</label>
					<div class="catalog-search__row">
						<input class="catalog-search__input" id="catalog-q" type="search" name="q" value="<?php echo esc_attr( $sky_q ); ?>" placeholder="Например, медицинская деятельность" autocomplete="off" enterkeyhint="search" aria-controls="catalog-grid">
						<button class="btn btn--light catalog-search__submit" type="submit">Найти</button>
					</div>
				</form>
			</div>
		</div>
	</section>

	<section class="catalog__body" aria-label="Каталог лицензий">
		<div class="sky-container">
			<div class="catalog__heading">
				<p class="catalog-eyebrow">Направления работы</p>
				<h2>Найдите свою лицензию</h2>
				<p>Выберите направление или воспользуйтесь поиском. Если нужна помощь, расскажите нам о вашей задаче.</p>
			</div>
			<?php if ( $sky_regs ) : ?>
				<nav class="filter-chips" aria-label="Направления">
					<ul class="filter-chips__list">
						<?php foreach ( $sky_views as $sky_v ) : ?>
							<li>
								<a class="filter-chips__link" href="<?php echo esc_url( $sky_v['url'] ); ?>"
									data-view="<?php echo esc_attr( $sky_v['slug'] ); ?>"
									data-h1="<?php echo esc_attr( $sky_v['h1'] ); ?>"
									data-lead="<?php echo esc_attr( $sky_v['lead'] ); ?>"
									data-title="<?php echo esc_attr( $sky_v['title'] ); ?>"
									<?php echo $sky_v['slug'] === $sky_view['slug'] ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $sky_v['label'] ); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>

			<p class="catalog__count" data-catalog-count>Найдено лицензий: <?php echo (int) $sky_shown; ?></p>
			<p class="screen-reader-text" aria-live="polite" data-catalog-live></p>

			<ul class="catalog__grid" id="catalog-grid">
				<?php foreach ( $sky_cards as $sky_card ) : ?>
					<li class="lic-card" data-regulator="<?php echo esc_attr( $sky_card['reg'] ? $sky_card['reg']->slug : '' ); ?>" data-search="<?php echo esc_attr( $sky_card['search'] ); ?>"<?php echo $sky_card['match'] ? '' : ' hidden'; ?>>
						<?php if ( $sky_card['reg'] ) : ?>
							<span class="lic-card__label"><?php echo esc_html( $sky_card['reg']->name ); ?></span>
						<?php endif; ?>
						<h2 class="lic-card__title">
							<a class="lic-card__link" href="<?php echo esc_url( get_permalink( $sky_card['post'] ) ); ?>"><?php echo esc_html( get_the_title( $sky_card['post'] ) ); ?></a>
						</h2>
						<p class="lic-card__meta"><?php echo esc_html( $sky_card['price'] . ' · ' . $sky_card['term'] ); ?></p>
						<span class="lic-card__more" aria-hidden="true">Подробнее →</span>
					</li>
				<?php endforeach; ?>
			</ul>

			<div class="catalog-empty" data-catalog-empty<?php echo $sky_shown ? ' hidden' : ''; ?>>
				<h2 class="catalog-empty__title"><?php echo $sky_posts ? 'Ничего не нашли?' : 'Поможем подобрать лицензию'; ?></h2>
				<p class="catalog-empty__text"><?php echo $sky_posts ? 'Опишите вид деятельности — подберём лицензию бесплатно.' : 'В каталоге пока нет опубликованных лицензий. Опишите вид деятельности, и мы поможем разобраться, какая лицензия нужна.'; ?></p>
				<?php
				sky_component(
					'lead-form',
					[
						'id'          => 'catalog-form',
						'topic_value' => $sky_q,
						'button'      => 'Задать вопрос юристу',
						'source'      => 'Каталог лицензий · ничего не нашли',
						'class'       => 'catalog-empty__form',
					]
				);
				?>
			</div>
		</div>
	</section>

	<section class="catalog-cta is-dark">
		<div class="sky-container catalog-cta__inner">
			<div class="catalog-cta__text">
				<h2 class="catalog-cta__title">Не знаете, какая лицензия нужна?</h2>
				<p class="catalog-cta__lead">Пройдите короткий подбор или расскажите нам о своей задаче.</p>
			</div>
			<div class="catalog-cta__actions">
				<a class="btn btn--light" href="<?php echo esc_url( home_url( '/#quiz' ) ); ?>">Пройти подбор</a>
				<a class="btn btn--outline-light" href="<?php echo esc_url( home_url( '/#form' ) ); ?>">Обсудить задачу</a>
			</div>
		</div>
	</section>
</div>
<?php
wp_enqueue_script( 'sky-catalog' );
