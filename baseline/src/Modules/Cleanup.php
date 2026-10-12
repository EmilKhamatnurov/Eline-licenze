<?php
/**
 * Модуль cleanup: лишнее в <head> и xmlrpc.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline\Modules;

use Sky\Baseline\Config;
use Sky\Baseline\Module;

defined( 'ABSPATH' ) || exit;

/**
 * Каждое отключение — отдельный флаг cleanup.*.
 */
final class Cleanup implements Module {

	/**
	 * Хуки.
	 */
	public function register(): void {
		$on = static fn( string $flag ): bool => (bool) Config::get( 'cleanup.' . $flag, false );

		if ( $on( 'emoji' ) ) {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
			remove_action( 'admin_enqueue_scripts', 'wp_enqueue_emoji_styles' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'embed_head', 'print_emoji_detection_script' );
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
			add_filter( 'emoji_svg_url', '__return_false' );
		}

		if ( $on( 'oembed_discovery' ) ) {
			// В ядре висит на двух приоритетах.
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 4 );
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		}

		if ( $on( 'rsd' ) ) {
			remove_action( 'wp_head', 'rsd_link' );
		}

		if ( $on( 'wlwmanifest' ) ) {
			// С WP 6.3 ядро не выводит wlwmanifest; снимаем на случай, если вернёт плагин.
			remove_action( 'wp_head', 'wlwmanifest_link' );
		}

		if ( $on( 'rest_link' ) ) {
			remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
			remove_action( 'template_redirect', 'rest_output_link_header', 11 );
		}

		if ( $on( 'shortlink' ) ) {
			remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
			remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
		}

		if ( $on( 'generator' ) ) {
			remove_action( 'wp_head', 'wp_generator' );
			add_filter( 'the_generator', '__return_empty_string' );
		}

		if ( $on( 'xmlrpc' ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
			// xmlrpc_enabled закрывает только методы с авторизацией — пингбэки убираем отдельно.
			add_filter( 'xmlrpc_methods', '__return_empty_array' );
			add_filter( 'wp_headers', [ $this, 'remove_pingback_header' ] );
		}
	}

	/**
	 * Заголовок X-Pingback.
	 *
	 * @param array $headers Заголовки.
	 */
	public function remove_pingback_header( array $headers ): array {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
}
