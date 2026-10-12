<?php
/**
 * Модуль redirects: карта 301 и канонизация формы URL.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline\Modules;

use Sky\Baseline\Config;
use Sky\Baseline\Module;

defined( 'ABSPATH' ) || exit;

/**
 * Карта и префиксы срабатывают только на 404 — живые страницы не перехватываются.
 */
final class Redirects implements Module {

	/**
	 * Хуки.
	 */
	public function register(): void {
		add_action( 'template_redirect', [ $this, 'from_map' ], 1 );
		add_action( 'template_redirect', [ $this, 'normalize' ], 2 );
	}

	/**
	 * Карта точных совпадений (фильтр sky_redirect_map).
	 */
	public static function map(): array {
		$map = (array) apply_filters( 'sky_redirect_map', (array) Config::get( 'redirects.map', [] ) );
		$out = [];
		foreach ( $map as $from => $to ) {
			// Ключи приводим к виду, в котором путь приходит от браузера: кириллица → %XX.
			$out[ implode( '/', array_map( 'rawurlencode', array_map( 'rawurldecode', explode( '/', (string) $from ) ) ) ) ] = $to;
		}
		return $out;
	}

	/**
	 * Префиксы разделов, длинные проверяются первыми (фильтр sky_redirect_prefixes).
	 */
	public static function prefixes(): array {
		$prefixes = (array) apply_filters( 'sky_redirect_prefixes', (array) Config::get( 'redirects.prefixes', [] ) );
		uksort( $prefixes, static fn( $a, $b ) => strlen( (string) $b ) <=> strlen( (string) $a ) );
		return $prefixes;
	}

	/**
	 * Старые URL → новые.
	 */
	public function from_map(): void {
		if ( ! is_404() ) {
			return;
		}
		[ $raw, $query ] = self::request();
		if ( '' === $raw ) {
			return;
		}
		// Сопоставляем уже нормализованный путь: иначе /Old/Page/ дал бы два хопа
		// (сначала по карте, потом на нижний регистр).
		$clean = self::clean( $raw );
		$path  = trailingslashit( $clean );
		$map   = self::map();

		foreach ( array_unique( [ trailingslashit( $raw ), $raw, $path, $clean ] ) as $candidate ) {
			if ( isset( $map[ $candidate ] ) ) {
				// Цель = источник дала бы бесконечный 301.
				if ( trailingslashit( (string) $map[ $candidate ] ) === $path ) {
					continue;
				}
				self::go( (string) $map[ $candidate ], $query );
			}
		}

		foreach ( self::prefixes() as $from => $to ) {
			if ( str_starts_with( $path, (string) $from ) ) {
				self::go( $to . substr( $path, strlen( (string) $from ) ), $query );
			}
		}
	}

	/**
	 * Схлопывание // и нижний регистр. Работает и на 404: правила ЧПУ чувствительны
	 * к регистру, поэтому /Uslugi/ — это 404, а не страница раздела.
	 */
	public function normalize(): void {
		if ( is_admin() || wp_doing_ajax() || is_preview() ) {
			return;
		}
		[ $path, $query ] = self::request();
		if ( '' === $path ) {
			return;
		}

		$clean = self::clean( $path );

		if ( $clean !== $path ) {
			self::go( $clean, $query );
		}
	}

	/**
	 * Путь после склейки // и перевода в нижний регистр (по флагам конфига).
	 *
	 * @param string $path Путь.
	 */
	public static function clean( string $path ): string {
		if ( Config::get( 'redirects.collapse_slashes', true ) ) {
			$path = (string) preg_replace( '#/{2,}#', '/', $path );
		}
		if ( Config::get( 'redirects.lowercase', true ) ) {
			// Яндекс различает /Uslugi/ и /uslugi/. Последовательности %XX не трогаем:
			// браузер шлёт %D0%BF, и перевод в %d0%bf дал бы лишний 301 на каждом кириллическом URL.
			$path = (string) preg_replace_callback(
				'/(%[0-9A-Fa-f]{2})|([^%]+)/',
				static fn( array $m ) => '' !== $m[1] ? $m[1] : mb_strtolower( $m[2] ),
				$path
			);
		}
		return $path;
	}

	/**
	 * 301 на путь сайта или полный URL.
	 * Внешний хост допускается: цель задана в конфиге разработчиком, не пользователем.
	 *
	 * @param string $target Путь или URL.
	 * @param string $query  Строка запроса исходного URL.
	 */
	public static function go( string $target, string $query = '' ): void {
		$external = (bool) preg_match( '#^https?://#i', $target );
		$url      = $external ? $target : home_url( $target );
		if ( '' !== $query ) {
			$url .= ( str_contains( $url, '?' ) ? '&' : '?' ) . $query;
		}

		$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( $external && wp_parse_url( $url, PHP_URL_HOST ) !== $home_host ) {
			wp_redirect( $url, 301 ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- цель из конфига.
		} else {
			wp_safe_redirect( $url, 301 );
		}
		exit;
	}

	/**
	 * Путь (относительно корня сайта) и строка запроса.
	 *
	 * @return array{0: string, 1: string}
	 */
	private static function request(): array {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- разбирается ниже, в вывод не попадает.
		// Не wp_parse_url: путь вида //uslugi//remont/ он разберёт как хост «uslugi».
		[ $path, $query ] = array_pad( explode( '?', (string) $uri, 2 ), 2, '' );
		$home             = (string) wp_parse_url( home_url(), PHP_URL_PATH );
		// Установка в подкаталоге: ключи карты — относительно корня сайта.
		if ( '' !== $home && '/' !== $home && str_starts_with( $path, $home ) ) {
			$path = '/' . ltrim( substr( $path, strlen( rtrim( $home, '/' ) ) ), '/' );
		}
		return [ $path, $query ];
	}
}
