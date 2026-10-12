<?php
/**
 * Демо-данные: 3 направления и 3 лицензии с заглушками в скобках (DESIGN.md §8).
 * Цены и сроки не заполняются — шаблоны выводят «[ЦЕНА]» и «[СРОК]».
 *
 *   wp eval-file bin/seed-licenses.php           — создать или обновить (повторный запуск безопасен)
 *   wp eval-file bin/seed-licenses.php delete    — удалить демо-лицензии (направления остаются)
 *
 * Демо-записи помечены мета-полем _sky_demo = 1 и ищутся по нему и по slug.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

if ( ! post_type_exists( 'license' ) || ! taxonomy_exists( 'regulator' ) ) {
	WP_CLI::error( 'Нет типа license или таксономии regulator: проверьте cpt в project/sky-config.php и что baseline подключён.' );
}

/**
 * Создать, обновить или удалить демо-данные.
 *
 * @param array $cli_args Аргументы wp eval-file: [ 'delete' ] — удалить.
 */
function sky_seed_licenses( array $cli_args ): void {
	$sky_demo_find = static function ( string $slug ): int {
		$found = get_posts(
			[
				'post_type'   => 'license',
				'name'        => $slug,
				'post_status' => 'any',
				'numberposts' => 1,
				'fields'      => 'ids',
			]
		);
		return $found ? (int) $found[0] : 0;
	};

	// Удаление.
	if ( in_array( 'delete', $cli_args, true ) ) {
		$ids = get_posts(
			[
				'post_type'   => 'license',
				'post_status' => 'any',
				'numberposts' => -1,
				'fields'      => 'ids',
				'meta_key'    => '_sky_demo', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- разовый запуск из CLI.
				'meta_value'  => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- разовый запуск из CLI.
			]
		);
		foreach ( $ids as $id ) {
			wp_delete_post( (int) $id, true );
		}
		WP_CLI::success( sprintf( 'Удалено демо-лицензий: %d', count( $ids ) ) );
		return;
	}

	// Направления. Названия и адреса — из макетов «Каталог лицензий» и «Структура сайта».
	$sky_regulators = [
		'alkogol'     => 'Алкоголь',
		'medicina'    => 'Медицина и фарма',
		'obrazovanie' => 'Образование',
	];
	$sky_term_ids   = [];
	foreach ( $sky_regulators as $slug => $name ) {
		$term = get_term_by( 'slug', $slug, 'regulator' );
		if ( ! $term ) {
			$created = wp_insert_term( $name, 'regulator', [ 'slug' => $slug ] );
			if ( is_wp_error( $created ) ) {
				WP_CLI::error( $created->get_error_message() );
			}
			$sky_term_ids[ $slug ] = (int) $created['term_id'];
		} else {
			$sky_term_ids[ $slug ] = (int) $term->term_id;
		}
	}

	// Общие заглушки: текст — в скобках, цены и сроки — не заполняются.
	$sky_list = static fn( string $label, int $count, string $tail = '' ): string => implode(
		"\n",
		array_map( static fn( int $i ): string => "[{$label} {$i}{$tail}]", range( 1, $count ) )
	);

	$sky_steps = implode(
		"\n\n",
		array_map(
			static fn( int $i ): string => "[Этап {$i}: название]\n[СРОК]\n[Этап {$i}: что делаем на этом шаге]",
			range( 1, 5 )
		)
	);

	$sky_tariffs = implode(
		"\n\n",
		array_map(
			static fn( int $i ): string => "[Тариф {$i}: название]\n[ЦЕНА]\n" . implode( "\n", array_map( static fn( int $j ): string => "[Что входит {$j}]", range( 1, 3 ) ) ),
			range( 1, 3 )
		)
	);

	$sky_faq = implode(
		"\n\n",
		array_map(
			static fn( int $i ): string => "[Вопрос {$i}]\n[Ответ {$i}. Юристу: проверить]",
			range( 1, 4 )
		)
	);

	// Лицензии: названия и H1 — по макетам, всё содержимое — заглушки.
	$sky_licenses = [
		'roznichnaya-prodazha-alkogolya' => [
			'title'     => 'Розничная продажа алкогольной продукции',
			'h1'        => 'Лицензия на розничную продажу алкогольной продукции под ключ',
			'regulator' => 'alkogol',
		],
		'medicinskaya-deyatelnost'       => [
			'title'     => 'Медицинская деятельность',
			'h1'        => 'Лицензия на медицинскую деятельность под ключ',
			'regulator' => 'medicina',
		],
		'obrazovatelnaya-deyatelnost'    => [
			'title'     => 'Образовательная деятельность',
			'h1'        => 'Лицензия на образовательную деятельность под ключ',
			'regulator' => 'obrazovanie',
		],
	];

	$sky_ids   = [];
	$sky_order = 0;
	foreach ( $sky_licenses as $slug => $def ) {
		$postarr = [
			'post_type'    => 'license',
			'post_status'  => 'publish',
			'post_title'   => $def['title'],
			'post_name'    => $slug,
			'post_excerpt' => '[Краткое описание для каталога и сниппета: 1–2 предложения]',
			'post_content' => '',
			'menu_order'   => ++$sky_order,
		];
		$id      = $sky_demo_find( $slug );
		if ( $id ) {
			$postarr['ID'] = $id;
		}
		$id = wp_insert_post( wp_slash( $postarr ), true );
		if ( is_wp_error( $id ) ) {
			WP_CLI::error( $id->get_error_message() );
		}
		wp_set_object_terms( $id, [ $sky_term_ids[ $def['regulator'] ] ], 'regulator' );

		$meta = [
			'_sky_demo'       => '1',
			'h1'              => $def['h1'],
			'activity'        => '[Вид деятельности — юристу: уточнить формулировку по закону]',
			'price_from'      => '', // Не придумываем: на сайте «[ЦЕНА]».
			'term_from'       => '', // Не придумываем: на сайте «[СРОК]».
			'intro'           => "[Когда лицензия обязательна: кому и для каких работ. Юристу: проверить]\n\n[Чем грозит работа без лицензии. Юристу: проверить]",
			'requirements'    => $sky_list( 'Требование', 4 ) . "\n[Юристу: проверить перечень требований]",
			'docs_legal'      => $sky_list( 'Документ', 6, ' для юрлица' ),
			'docs_ip'         => $sky_list( 'Документ', 6, ' для ИП' ),
			'steps'           => $sky_steps,
			'tariffs'         => $sky_tariffs,
			'tariff_featured' => '2',
			'faq'             => $sky_faq,
		];
		foreach ( $meta as $key => $value ) {
			update_post_meta( $id, $key, wp_slash( $value ) );
		}

		$sky_ids[ $slug ] = (int) $id;
		WP_CLI::log( sprintf( '%s — %s', $def['title'], get_permalink( $id ) ) );
	}

	// Смежные лицензии: каждая ссылается на две другие. Meta Box хранит множественное поле отдельными строками мета.
	foreach ( $sky_ids as $slug => $id ) {
		delete_post_meta( $id, 'related' );
		foreach ( array_diff( $sky_ids, [ $id ] ) as $other ) {
			add_post_meta( $id, 'related', (string) $other );
		}
	}

	WP_CLI::success( sprintf( 'Демо-лицензий: %d, направлений: %d', count( $sky_ids ), count( $sky_term_ids ) ) );
}

sky_seed_licenses( (array) ( $args ?? [] ) );
