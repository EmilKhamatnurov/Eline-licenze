<?php
/**
 * Лицензии (CPT license, таксономия regulator — project/sky-config.php, раздел cpt).
 * Адрес /licenzirovanie/<regulator>/<slug>/ и данные лицензии одним массивом для шаблонов.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'post_type_link', 'sky_starter_license_link', 10, 2 );
add_action( 'template_redirect', 'sky_starter_license_canonical_redirect', 1 );
add_action( 'pre_get_posts', 'sky_starter_catalog_query' );
add_action( 'template_redirect', 'sky_starter_catalog_redirect_paged', 1 );
add_filter( 'wp_robots', 'sky_starter_catalog_robots', 30 );

/**
 * %regulator% в адресе — первое направление записи, как в крошках baseline (get_the_terms + reset).
 * Без направления — «drugie» (default_term таксономии назначает его при сохранении).
 *
 * @param string  $link Адрес.
 * @param WP_Post $post Запись.
 */
function sky_starter_license_link( $link, $post ) {
	if ( 'license' !== $post->post_type || ! str_contains( (string) $link, '%regulator%' ) ) {
		return $link;
	}
	$term = sky_starter_license_regulator( $post->ID );
	return str_replace( '%regulator%', $term ? $term->slug : 'drugie', (string) $link );
}

/**
 * WordPress находит лицензию по slug и с чужим направлением в адресе
 * (/licenzirovanie/alkogol/medicinskaya-deyatelnost/) — это дубль. 301 на настоящий адрес.
 */
function sky_starter_license_canonical_redirect(): void {
	if ( ! is_singular( 'license' ) || is_preview() ) {
		return;
	}
	$requested = (string) get_query_var( 'regulator' );
	$term      = sky_starter_license_regulator( (int) get_queried_object_id() );
	$expected  = $term ? $term->slug : 'drugie';
	if ( '' !== $requested && $requested !== $expected ) {
		wp_safe_redirect( (string) get_permalink( get_queried_object_id() ), 301 );
		exit;
	}
}

/**
 * Основное направление лицензии.
 *
 * @param int $id ID лицензии.
 */
function sky_starter_license_regulator( int $id ): ?WP_Term {
	$terms = get_the_terms( $id, 'regulator' );
	return $terms && ! is_wp_error( $terms ) ? reset( $terms ) : null;
}

/**
 * Число с неразрывными пробелами по разрядам: 50 000.
 *
 * @param string $value Строка; не число — вернётся как есть.
 */
function sky_starter_number( string $value ): string {
	$digits = preg_replace( '/[\s\x{00A0}]+/u', '', $value );
	return is_numeric( $digits ) ? number_format( (float) $digits, 0, ',', "\u{00A0}" ) : $value;
}

/**
 * «день», «дня», «дней».
 *
 * @param int $n Число.
 */
function sky_starter_days_word( int $n ): string {
	$n10  = $n % 10;
	$n100 = $n % 100;
	if ( 1 === $n10 && 11 !== $n100 ) {
		return 'день';
	}
	if ( $n10 >= 2 && $n10 <= 4 && ( $n100 < 12 || $n100 > 14 ) ) {
		return 'дня';
	}
	return 'дней';
}

/**
 * «от 50 000 ₽» или «от [ЦЕНА] ₽» — цена не придумывается (DESIGN.md §8).
 *
 * @param string $price Значение поля.
 */
function sky_starter_price_label( string $price ): string {
	$price = trim( $price );
	return 'от ' . ( '' !== $price ? sky_starter_number( $price ) : '[ЦЕНА]' ) . "\u{00A0}₽";
}

/**
 * «50 000 ₽» или «[ЦЕНА] ₽» — цена тарифа без «от».
 *
 * @param string $price Значение поля.
 */
function sky_starter_money( string $price ): string {
	$price = trim( $price );
	return ( '' !== $price ? sky_starter_number( $price ) : '[ЦЕНА]' ) . "\u{00A0}₽";
}

/**
 * «от 30 дней» или «от [СРОК] дней».
 *
 * @param string $days Значение поля.
 */
function sky_starter_term_label( string $days ): string {
	$days = trim( $days );
	if ( ! is_numeric( $days ) ) {
		return 'от ' . ( '' !== $days ? $days : '[СРОК]' ) . "\u{00A0}дней";
	}
	$n = (int) $days;
	return 'от ' . $n . "\u{00A0}" . sky_starter_days_word( $n );
}

/**
 * Все данные лицензии для шаблонов: списки — массивами строк, группы — массивами пунктов.
 *
 * @param int|null $id ID лицензии (по умолчанию текущая).
 * @return array{id:int, title:string, h1:string, url:string, regulator:?WP_Term, lead:string, activity:string,
 *               price_from:string, term_from:string, price_label:string, term_label:string,
 *               intro:string, requirements:string[], docs_legal:string[], docs_ip:string[],
 *               steps:array, tariffs:array, tariff_featured:int, faq:array, related:int[]}
 */
function sky_starter_license( ?int $id = null ): array {
	$id    = $id ? $id : (int) get_the_ID();
	$field = static fn( string $key ): string => function_exists( 'sky_field' ) ? sky_field( $key, $id ) : (string) get_post_meta( $id, $key, true );
	$group = static fn( string $key ): array => function_exists( 'sky_field_raw' ) ? array_values( array_filter( (array) sky_field_raw( $key, $id ) ) ) : [];
	$lines = static fn( string $key ): array => sky_lines( $field( $key ) );

	$title   = get_the_title( $id );
	$h1      = trim( $field( 'h1' ) );
	$related = function_exists( 'sky_field_raw' ) ? sky_field_raw( 'related', $id ) : get_post_meta( $id, 'related', false );
	$related = array_values( array_filter( array_map( 'absint', (array) $related ) ) );

	$tariffs = array_slice( $group( 'tariffs' ), 0, 3 );
	foreach ( $tariffs as &$tariff ) {
		$tariff['features']    = sky_lines( $tariff['features'] ?? '' );
		$tariff['price_label'] = sky_starter_price_label( (string) ( $tariff['price'] ?? '' ) );
		$tariff['price_text']  = sky_starter_money( (string) ( $tariff['price'] ?? '' ) );
	}
	unset( $tariff );

	$featured = (int) $field( 'tariff_featured' );

	return [
		'id'              => $id,
		'title'           => $title,
		'h1'              => '' !== $h1 ? $h1 : $title,
		'url'             => (string) get_permalink( $id ),
		'regulator'       => sky_starter_license_regulator( $id ),
		'lead'            => has_excerpt( $id ) ? (string) get_post_field( 'post_excerpt', $id ) : '',
		'activity'        => $field( 'activity' ),
		'price_from'      => $field( 'price_from' ),
		'term_from'       => $field( 'term_from' ),
		'price_label'     => sky_starter_price_label( $field( 'price_from' ) ),
		'term_label'      => sky_starter_term_label( $field( 'term_from' ) ),
		'intro'           => $field( 'intro' ),
		'requirements'    => $lines( 'requirements' ),
		'docs_legal'      => $lines( 'docs_legal' ),
		'docs_ip'         => $lines( 'docs_ip' ),
		'steps'           => $group( 'steps' ),
		'tariffs'         => $tariffs,
		'tariff_featured' => $featured >= 1 && $featured <= 3 ? $featured : 2,
		'faq'             => $group( 'faq' ),
		'related'         => array_values( array_diff( $related, [ $id ] ) ),
	];
}

/**
 * Каталог и разделы — одной страницей: фильтр работает по всем карточкам на месте,
 * а /licenzirovanie/page/2/ не появляется (иначе это дубль каталога).
 *
 * @param WP_Query $q Запрос.
 */
function sky_starter_catalog_query( WP_Query $q ): void {
	if ( ! is_admin() && $q->is_main_query() && ( $q->is_post_type_archive( 'license' ) || $q->is_tax( 'regulator' ) ) ) {
		$q->set( 'posts_per_page', -1 );
		$q->set( 'no_found_rows', true );
	}
}

/**
 * Каталог — одна страница: /licenzirovanie/page/2/ и /licenzirovanie/<regulator>/page/2/ — 301 на первую.
 */
function sky_starter_catalog_redirect_paged(): void {
	if ( ( is_post_type_archive( 'license' ) || is_tax( 'regulator' ) ) && (int) get_query_var( 'paged' ) > 1 ) {
		$url = is_tax() ? get_term_link( get_queried_object() ) : get_post_type_archive_link( 'license' );
		if ( is_string( $url ) ) {
			wp_safe_redirect( $url, 301 );
			exit;
		}
	}
}

/**
 * Раздел без лицензий — пустая страница: noindex, follow, пока в нём ничего нет.
 *
 * @param array $robots Директивы.
 */
function sky_starter_catalog_robots( array $robots ): array {
	$term = is_tax( 'regulator' ) ? get_queried_object() : null;
	if ( $term instanceof WP_Term && 0 === (int) $term->count ) {
		unset( $robots['index'] );
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}

/**
 * Направления с лицензиями: по алфавиту, «Другие лицензии» — в конце.
 *
 * @return WP_Term[]
 */
function sky_starter_regulators(): array {
	$terms = get_terms(
		[
			'taxonomy'   => 'regulator',
			'hide_empty' => true,
			'orderby'    => 'name',
		]
	);
	if ( ! $terms || is_wp_error( $terms ) ) {
		return [];
	}
	usort( $terms, static fn( $a, $b ) => (int) ( 'drugie' === $a->slug ) <=> (int) ( 'drugie' === $b->slug ) );
	return $terms;
}

/**
 * Строка для поиска по каталогу: нижний регистр, «ё» → «е», одиночные пробелы.
 * Та же нормализация — в assets/js/catalog.js.
 *
 * @param string $text Текст.
 */
function sky_starter_search_norm( string $text ): string {
	$text = mb_strtolower( $text );
	$text = str_replace( 'ё', 'е', $text );
	return trim( (string) preg_replace( '/\s+/u', ' ', $text ) );
}

/**
 * Вид каталога: «Все» (term = null) или направление. H1, подзаголовок, адрес и Title —
 * сервер выводит текущий, JS переключает остальные без перезагрузки.
 *
 * @param WP_Term|null $term Направление.
 * @return array{slug:string, label:string, h1:string, lead:string, url:string, title:string}
 */
function sky_starter_catalog_view( ?WP_Term $term ): array {
	$templates = function_exists( 'sky_title_templates' ) ? (array) sky_title_templates() : [];
	$render    = static fn( string $tpl, array $tokens ): string => function_exists( 'sky_render' ) ? sky_render( $tpl, $tokens ) : strtr( $tpl, $tokens + [ '%site%' => get_bloginfo( 'name' ) ] );
	$archive   = get_post_type_object( 'license' );
	$label     = $archive ? (string) $archive->labels->name : 'Лицензирование';

	if ( ! $term ) {
		return [
			'slug'  => '',
			'label' => 'Все',
			'h1'    => 'Лицензирование любых видов деятельности',
			'lead'  => 'Найдите свою лицензию в каталоге — у каждой отдельная страница с требованиями, документами, сроками и ценой.',
			'url'   => (string) get_post_type_archive_link( 'license' ),
			'title' => $render( (string) ( $templates['archive_license'] ?? '%archive% | %site%' ), [ '%archive%' => $label ] ),
		];
	}

	$description = trim( wp_strip_all_tags( term_description( $term ) ) );
	return [
		'slug'  => $term->slug,
		'label' => $term->name,
		'h1'    => 'Лицензии: ' . $term->name,
		'lead'  => '' !== $description ? $description : sprintf( 'Лицензии направления «%s»: у каждой отдельная страница с требованиями, документами, сроками и ценой.', $term->name ),
		'url'   => (string) get_term_link( $term ),
		'title' => $render( (string) ( $templates['tax_regulator'] ?? $templates['tax'] ?? '%term% | %site%' ), [ '%term%' => $term->name ] ),
	];
}
