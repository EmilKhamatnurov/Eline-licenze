<?php
/**
 * Страницы сайта по структуре DESIGN.md §7 с назначенными шаблонами и рубрики статей.
 *
 *   wp eval-file bin/seed-pages.php
 *
 * Повторный запуск безопасен: существующая страница (по slug) не перезаписывается — только шаблон.
 * Главная — страница «Главная» (шаблон front-page.php), записи — на странице «Статьи».
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Создать или обновить страницы и настройки чтения.
 */
function sky_seed_pages(): void {
	$pages = [
		'glavnaya'           => [ 'Главная', '' ],
		'yurist-na-autsorse' => [ 'Юрист на аутсорсе', 'page-templates/outsourcing.php' ],
		'dokumenty'          => [ 'Документы', 'page-templates/documents.php' ],
		'yuridicheskiy-ii'   => [ 'Юридический ИИ', 'page-templates/legal-ai.php' ],
		'o-kompanii'         => [ 'О компании', 'page-templates/about.php' ],
		'kontakty'           => [ 'Контакты', 'page-templates/contacts.php' ],
		'stati'              => [ 'Статьи', '' ],
	];

	$ids = [];
	foreach ( $pages as $slug => [ $title, $template ] ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$id = (int) $page->ID;
		} else {
			$id = wp_insert_post(
				[
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_title'  => $title,
					'post_name'   => $slug,
				],
				true
			);
			if ( is_wp_error( $id ) ) {
				WP_CLI::error( $id->get_error_message() );
			}
		}
		update_post_meta( $id, '_wp_page_template', '' !== $template ? $template : 'default' );
		$ids[ $slug ] = (int) $id;
		WP_CLI::log( sprintf( '%s — %s', $title, get_permalink( $id ) ) );
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ids['glavnaya'] );
	update_option( 'page_for_posts', $ids['stati'] );

	// Рубрики статей — темы из макета Articles. Slug латиницей: без транслитератора WordPress сделал бы %D0%…
	$topics = [
		'litsenzirovanie'     => 'Лицензирование',
		'yurist-dlya-biznesa' => 'Юрист для бизнеса',
		'dokumenty'           => 'Документы',
		'yuridicheskiy-ii'    => 'Юридический ИИ',
	];
	foreach ( $topics as $slug => $name ) {
		if ( ! get_term_by( 'slug', $slug, 'category' ) ) {
			wp_insert_term( $name, 'category', [ 'slug' => $slug ] );
		}
	}

	WP_CLI::success( 'Страницы созданы, главная и страница записей назначены.' );
}

sky_seed_pages();
