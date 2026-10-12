<?php
/**
 * API компонентов: sky_component( 'hero', [ … ] ).
 *
 * Компонент — файл template-parts/components/<имя>.php. Аргументы приходят в $args,
 * дефолты объявляет сам компонент через wp_parse_args(), экранирование — тоже в нём.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Вывести компонент.
 *
 * @param string $name Имя: hero, steps, price, docs-list, faq, cases-grid, reviews, cta, breadcrumbs.
 * @param array  $args Аргументы компонента.
 */
function sky_component( string $name, array $args = [] ): void {
	$name = sanitize_key( $name );
	$args = (array) apply_filters( 'sky_component_args', $args, $name );
	$args = (array) apply_filters( "sky_component_args_{$name}", $args );

	if ( ! locate_template( "template-parts/components/{$name}.php" ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			trigger_error( esc_html( "sky_component: нет компонента «{$name}»" ), E_USER_WARNING ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
		}
		return;
	}
	get_template_part( 'template-parts/components/' . $name, null, $args );
}

/**
 * Компонент строкой.
 *
 * @param string $name Имя.
 * @param array  $args Аргументы.
 */
function sky_get_component( string $name, array $args = [] ): string {
	ob_start();
	sky_component( $name, $args );
	return (string) ob_get_clean();
}

/**
 * Строки из textarea — по одной на строку, пустые отбрасываются.
 *
 * @param mixed $value Строка или массив.
 */
function sky_lines( $value ): array {
	if ( is_array( $value ) ) {
		return array_values( array_filter( $value ) );
	}
	return array_values( array_filter( array_map( 'trim', preg_split( '/\R/u', (string) $value ) ) ) );
}
