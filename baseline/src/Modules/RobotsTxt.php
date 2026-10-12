<?php
/**
 * Модуль robots_txt: дополнения к виртуальному robots.txt.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline\Modules;

use Sky\Baseline\Config;
use Sky\Baseline\Module;

defined( 'ABSPATH' ) || exit;

/**
 * Работает, только пока в корне сайта нет физического robots.txt (он перекрывает виртуальный).
 * Строку Sitemap добавляет ядро WordPress. Директиву Host не выводим: Яндекс её не поддерживает.
 */
final class RobotsTxt implements Module {

	/**
	 * Хуки.
	 */
	public function register(): void {
		add_filter( 'robots_txt', [ $this, 'filter' ], 20, 2 );
	}

	/**
	 * Фильтр robots_txt.
	 *
	 * @param string     $output Текст robots.txt.
	 * @param bool|mixed $public Сайт открыт для индексации (blog_public).
	 */
	public function filter( $output, $public ): string {
		return $public ? self::build( (string) $output ) : (string) $output;
	}

	/**
	 * Добавляет Disallow в группу «User-agent: *» и строки Clean-param.
	 *
	 * @param string $output Текст robots.txt от ядра.
	 */
	public static function build( string $output ): string {
		$disallow = array_values( array_filter( array_map( 'strval', (array) Config::get( 'robots_txt.disallow', [] ) ) ) );
		$params   = array_values( array_filter( array_map( 'strval', (array) Config::get( 'robots_txt.clean_param', [] ) ) ) );

		if ( $disallow ) {
			$lines  = implode( "\n", array_map( static fn( string $path ): string => 'Disallow: ' . $path, $disallow ) ) . "\n";
			$output = (string) preg_replace_callback(
				'/^User-agent: \*\R/m',
				static fn( array $m ): string => $m[0] . $lines,
				$output,
				1
			);
		}

		if ( $params ) {
			// Clean-param — межсекционная директива Яндекса: её место в файле не важно.
			$output = rtrim( $output ) . "\n\n" . implode( "\n", array_map( static fn( string $p ): string => 'Clean-param: ' . $p, $params ) ) . "\n";
		}

		return $output;
	}
}
