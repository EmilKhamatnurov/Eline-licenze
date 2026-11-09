<?php
/**
 * Квиз «Подбор лицензии» (DESIGN.md §5): вид деятельности → форма бизнеса → результат и контакт.
 * Модуль: этот файл (вопросы и данные), template-parts/components/quiz.php (разметка),
 * assets/js/quiz.js (шаги), assets/css/quiz.css (стили). Выводится: sky_component( 'quiz' ).
 *
 * Варианты «вида деятельности» связаны с направлениями (таксономия regulator): в результате —
 * лицензии этого направления из записей, с ценой и сроком из их полей («[ЦЕНА]», «[СРОК]», если пусто).
 * Вариант показывается, только если в направлении есть лицензии; вариант без направления — всегда.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Вопросы квиза. Меняется фильтром sky_starter_quiz.
 *
 * activities — [ подпись, slug направления ]; slug '' — «Другое / не знаю», без лицензий в результате.
 */
function sky_starter_quiz_config(): array {
	return (array) apply_filters(
		'sky_starter_quiz',
		[
			'activity_title' => 'Чем занимается ваш бизнес?',
			'form_title'     => 'Какая у вас форма бизнеса?',
			'result_title'   => 'Предварительный результат',
			'activities'     => [
				[ 'Торговля или общепит с алкоголем', 'alkogol' ],
				[ 'Клиника, медцентр, аптека', 'medicina' ],
				[ 'Школа, детский центр, курсы', 'obrazovanie' ],
				[ 'Производство, отходы', 'ekologiya' ],
				[ 'Монтаж пожарной безопасности', 'mchs' ],
				[ 'Перевозки', 'transport' ],
				[ 'Охрана', 'bezopasnost' ],
				[ 'Другое / не знаю', '' ],
			],
			'forms'          => [ 'ИП', 'ООО', 'Компании ещё нет' ],
			'note'           => 'Точный расчёт — после консультации.',
			'fallback'       => 'Подберём лицензию после короткой консультации.',
		]
	);
}

/**
 * Данные для quiz.js: варианты с лицензиями из записей.
 *
 * @return array{activities: array, forms: string[], note: string, fallback: string}
 */
function sky_starter_quiz_data(): array {
	$config     = sky_starter_quiz_config();
	$activities = [];

	foreach ( (array) $config['activities'] as $i => [ $label, $slug ] ) {
		$licenses = [];
		if ( '' !== $slug ) {
			$term = get_term_by( 'slug', $slug, 'regulator' );
			if ( ! $term || ! $term->count ) {
				continue; // Направления нет или в нём пока нет лицензий — вариант не показываем.
			}
			$posts = get_posts(
				[
					'post_type'      => 'license',
					'posts_per_page' => -1,
					'orderby'        => [
						'menu_order' => 'ASC',
						'title'      => 'ASC',
					],
					'no_found_rows'  => true,
					'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- несколько направлений, разовый запрос на странице.
						[
							'taxonomy' => 'regulator',
							'terms'    => $term->term_id,
						],
					],
				]
			);
			foreach ( $posts as $post ) {
				$licenses[] = [
					'name' => get_the_title( $post ),
					'url'  => (string) get_permalink( $post ),
					'meta' => sky_starter_price_label( sky_field( 'price_from', $post->ID ) ) . ' · ' . sky_starter_term_label( sky_field( 'term_from', $post->ID ) ),
				];
			}
		}
		$activities[] = [
			'id'       => 'a' . $i,
			'label'    => (string) $label,
			'licenses' => $licenses,
		];
	}

	return [
		'activities' => $activities,
		'forms'      => array_values( (array) $config['forms'] ),
		'note'       => (string) $config['note'],
		'fallback'   => (string) $config['fallback'],
	];
}
