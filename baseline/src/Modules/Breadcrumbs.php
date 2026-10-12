<?php
/**
 * Модуль breadcrumbs: HTML-крошки.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline\Modules;

use Sky\Baseline\Module;
use Sky\Baseline\Seo\Breadcrumbs as Chain;

defined( 'ABSPATH' ) || exit;

/**
 * Хуков нет: модуль включает sky_breadcrumbs() и BreadcrumbList в Schema.
 */
final class Breadcrumbs implements Module {

	/**
	 * Хуки.
	 */
	public function register(): void {}

	/**
	 * HTML крошек.
	 *
	 * @param array $args class — CSS-класс корня.
	 */
	public static function render( array $args = [] ): string {
		$items = Chain::items();
		if ( count( $items ) < 2 ) {
			return '';
		}
		$class = sanitize_html_class( $args['class'] ?? 'crumbs' );
		$last  = count( $items ) - 1;

		$html = sprintf( '<nav class="%1$s" aria-label="Хлебные крошки"><ol class="%1$s__list">', esc_attr( $class ) );
		foreach ( $items as $i => $item ) {
			$html .= sprintf( '<li class="%s__item">', esc_attr( $class ) );
			if ( $i !== $last && ! empty( $item['url'] ) ) {
				$html .= sprintf( '<a href="%s">%s</a>', esc_url( $item['url'] ), esc_html( $item['name'] ) );
			} else {
				$html .= sprintf( '<span%s>%s</span>', $i === $last ? ' aria-current="page"' : '', esc_html( $item['name'] ) );
			}
			$html .= '</li>';
		}
		return $html . '</ol></nav>';
	}
}
