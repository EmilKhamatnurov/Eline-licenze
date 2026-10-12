<?php
/**
 * Модуль sitemap: чистка wp-sitemap.xml.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline\Modules;

use Sky\Baseline\Config;
use Sky\Baseline\Module;

defined( 'ABSPATH' ) || exit;

/**
 * Убирает из карты пользователей, заявки, служебные таксономии и записи с noindex.
 */
final class Sitemap implements Module {

	/**
	 * Хуки.
	 */
	public function register(): void {
		add_filter( 'wp_sitemaps_add_provider', [ $this, 'provider' ], 10, 2 );
		add_filter( 'wp_sitemaps_post_types', [ $this, 'post_types' ] );
		add_filter( 'wp_sitemaps_taxonomies', [ $this, 'taxonomies' ] );
		if ( Config::get( 'sitemap.exclude_noindex', true ) ) {
			add_filter( 'wp_sitemaps_posts_query_args', [ $this, 'skip_noindex' ] );
		}
	}

	/**
	 * Провайдер пользователей.
	 *
	 * @param \WP_Sitemaps_Provider|false $provider Провайдер.
	 * @param string                      $name     Имя.
	 * @return \WP_Sitemaps_Provider|false
	 */
	public function provider( $provider, string $name ) {
		return 'users' === $name && ! Config::get( 'sitemap.users', false ) ? false : $provider;
	}

	/**
	 * Типы записей.
	 *
	 * @param array $post_types Типы.
	 */
	public function post_types( array $post_types ): array {
		$exclude = array_merge( [ Forms::POST_TYPE ], (array) Config::get( 'sitemap.exclude_post_types', [] ) );
		return array_diff_key( $post_types, array_flip( $exclude ) );
	}

	/**
	 * Таксономии.
	 *
	 * @param array $taxonomies Таксономии.
	 */
	public function taxonomies( array $taxonomies ): array {
		return array_diff_key( $taxonomies, array_flip( (array) Config::get( 'sitemap.exclude_taxonomies', [] ) ) );
	}

	/**
	 * Записи с seo_noindex не попадают в карту.
	 *
	 * @param array $args Аргументы WP_Query.
	 */
	public function skip_noindex( array $args ): array {
		$args['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- карта сайта кэшируется ботами, запрос редкий.
			'relation' => 'OR',
			[
				'key'     => 'seo_noindex',
				'compare' => 'NOT EXISTS',
			],
			[
				'key'     => 'seo_noindex',
				'value'   => '1',
				'compare' => '!=',
			],
		];
		return $args;
	}
}
