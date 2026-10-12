<?php
/**
 * Модуль opengraph: og:* для мессенджеров и соцсетей.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline\Modules;

use Sky\Baseline\Config;
use Sky\Baseline\Module;
use Sky\Baseline\Seo\Context;

defined( 'ABSPATH' ) || exit;

/**
 * Title и description берутся из того же Context, что и meta, — тексты не расходятся.
 */
final class OpenGraph implements Module {

	/**
	 * Хуки.
	 */
	public function register(): void {
		add_action( 'wp_head', [ $this, 'output' ], 4 );
	}

	/**
	 * Вывод.
	 */
	public function output(): void {
		if ( is_404() ) {
			return;
		}

		$tags = [
			'og:locale'      => (string) Config::get( 'opengraph.locale', 'ru_RU' ),
			'og:type'        => is_singular( 'post' ) ? 'article' : 'website',
			'og:site_name'   => Context::plain( get_bloginfo( 'name' ) ),
			'og:title'       => Context::title(),
			'og:description' => Context::description(),
			'og:url'         => Context::current_url(),
		];

		$image = Context::image();
		if ( $image ) {
			$tags['og:image'] = $image['url'];
			if ( $image['width'] && $image['height'] ) {
				$tags['og:image:width']  = (string) $image['width'];
				$tags['og:image:height'] = (string) $image['height'];
			}
		}

		$tags['twitter:card'] = $image ? 'summary_large_image' : 'summary';
		$tags                 = (array) apply_filters( 'sky_opengraph_tags', $tags );

		foreach ( $tags as $property => $content ) {
			if ( '' === (string) $content ) {
				continue;
			}
			$attr  = str_starts_with( (string) $property, 'twitter:' ) ? 'name' : 'property';
			$value = in_array( $property, [ 'og:url', 'og:image' ], true ) ? esc_url( (string) $content ) : esc_attr( (string) $content );
			printf( '<meta %s="%s" content="%s">' . "\n", esc_attr( $attr ), esc_attr( (string) $property ), $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- экранировано выше.
		}
	}
}
