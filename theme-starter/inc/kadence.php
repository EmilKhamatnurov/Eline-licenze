<?php
/**
 * Совместимость с Kadence: крошки и микроразметка отдаются baseline.
 * Хуки проверены по коду Kadence 1.5.2 (inc/components/microdata, breadcrumbs, entry_title, archive_title).
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

// itemscope/itemtype (WebPage, Blog, WPHeader…) дублировали бы JSON-LD baseline.
add_filter( 'kadence_microdata', '__return_false' );

add_filter( 'kadence_title_elements_template_path', 'sky_starter_kadence_breadcrumb_template', 10, 2 );
add_filter( 'kadence_breadcrumb_html', 'sky_starter_kadence_breadcrumb_html' );

/**
 * Элемент «Крошки» в заголовке записи или архива рендерит наш компонент.
 * Включение и позиция по-прежнему задаются в Customizer Kadence.
 *
 * @param string $template Путь шаблона.
 * @param string $item     Элемент заголовка.
 */
function sky_starter_kadence_breadcrumb_template( $template, $item ) {
	return 'breadcrumb' === $item ? 'template-parts/components/breadcrumbs' : $template;
}

/**
 * Остальные места, где Kadence строит свои крошки, получают наши.
 */
function sky_starter_kadence_breadcrumb_html(): string {
	return sky_get_component( 'breadcrumbs' );
}
