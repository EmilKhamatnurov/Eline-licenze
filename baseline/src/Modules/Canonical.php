<?php
/**
 * Модуль canonical: канониклы для списков и 301 с /page/1/.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline\Modules;

use Sky\Baseline\Config;
use Sky\Baseline\Module;
use Sky\Baseline\Seo\Context;

defined( 'ABSPATH' ) || exit;

/**
 * Ядро выводит canonical только для записей — добавляем архивы, таксономии, блог.
 */
final class Canonical implements Module {

	/**
	 * Хуки.
	 */
	public function register(): void {
		if ( Config::get( 'canonical.archives', true ) ) {
			add_action( 'wp_head', [ $this, 'output' ], 3 );
		}
		if ( Config::get( 'canonical.redirect_page_one', true ) ) {
			add_action( 'template_redirect', [ $this, 'redirect_page_one' ] );
		}
	}

	/**
	 * Self-canonical на списках, включая страницы пагинации.
	 */
	public function output(): void {
		if ( is_singular() ) {
			return;
		}
		$url = Context::canonical();
		if ( '' !== $url ) {
			printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
		}
	}

	/**
	 * /razdel/page/1/ → /razdel/.
	 */
	public function redirect_page_one(): void {
		if ( is_singular() || is_404() || 1 !== (int) get_query_var( 'paged' ) ) {
			return;
		}
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
		if ( ! preg_match( '#/page/1/?$#', $path ) ) {
			return;
		}
		$target = (string) preg_replace( '#/page/1/?$#', '/', $path );
		$query  = (string) wp_parse_url( $uri, PHP_URL_QUERY );
		// Путь уже содержит подкаталог установки, поэтому редиректим относительным URL.
		wp_safe_redirect( $target . ( '' !== $query ? '?' . $query : '' ), 301 );
		exit;
	}
}
