<?php
/**
 * Компонент «Крошки» — обёртка над sky_breadcrumbs() из baseline.
 *
 * Аргументы: class — CSS-класс корня (crumbs).
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? [], [ 'class' => 'crumbs' ] );

if ( function_exists( 'sky_breadcrumbs' ) ) {
	sky_breadcrumbs( [ 'class' => $args['class'] ] );
}
