<?php
/**
 * Модуль robots: директивы meta robots.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline\Modules;

use Sky\Baseline\Config;
use Sky\Baseline\Fields;
use Sky\Baseline\Module;
use Sky\Baseline\Seo\Context;

defined( 'ABSPATH' ) || exit;

/**
 * Служебные выдачи, поле seo_noindex и URL с параметрами из robots.noindex_query_args — noindex, follow.
 */
final class Robots implements Module {

	/**
	 * Хуки.
	 */
	public function register(): void {
		add_filter( 'wp_robots', [ $this, 'filter' ] );
	}

	/**
	 * Фильтр wp_robots (WP 5.7+).
	 *
	 * @param array $robots Директивы.
	 */
	public function filter( array $robots ): array {
		if ( $this->should_noindex() ) {
			return self::noindex( $robots );
		}
		$preview = (string) Config::get( 'robots.max_image_preview', 'large' );
		if ( '' !== $preview && get_option( 'blog_public' ) ) {
			$robots['max-image-preview'] = $preview;
		}
		return $robots;
	}

	/**
	 * Ставит noindex, follow.
	 *
	 * @param array $robots Директивы.
	 */
	public static function noindex( array $robots ): array {
		unset( $robots['index'], $robots['max-image-preview'] );
		$robots['noindex'] = true;
		if ( empty( $robots['nofollow'] ) ) {
			$robots['follow'] = true;
		}
		return $robots;
	}

	/**
	 * Нужно ли закрыть текущую страницу.
	 */
	private function should_noindex(): bool {
		$checks = [
			'search'     => 'is_search',
			'author'     => 'is_author',
			'date'       => 'is_date',
			'attachment' => 'is_attachment',
			'404'        => 'is_404',
		];
		foreach ( (array) Config::get( 'robots.noindex', [] ) as $type ) {
			if ( isset( $checks[ $type ] ) && call_user_func( $checks[ $type ] ) ) {
				return true;
			}
		}

		$id = Context::object_id();
		if ( $id && Fields::string( 'seo_noindex', $id ) ) {
			return true;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- только проверка наличия параметра.
		$args = array_intersect( (array) Config::get( 'robots.noindex_query_args', [] ), array_keys( $_GET ) );
		return [] !== $args;
	}
}
