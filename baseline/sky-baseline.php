<?php
/**
 * Plugin Name: 7sky baseline
 * Description: SEO-ядро стартера: Title, meta, robots, canonical, крошки, Schema, редиректы, формы, CPT, аналитика.
 * Version:     2.0.0
 * Requires PHP: 8.1
 * Requires at least: 6.5
 *
 * @package Sky\Baseline
 */

defined( 'ABSPATH' ) || exit;

// Старый 7sky-loader.php объявляет те же функции — вместе они дают фатальную ошибку.
if ( defined( 'SKY_BASELINE_VERSION' ) || function_exists( 'sky_opt' ) ) {
	add_action(
		'admin_notices',
		static function () {
			if ( current_user_can( 'manage_options' ) ) {
				echo '<div class="notice notice-error"><p>7sky baseline: найдена вторая копия baseline (вероятно, старый mu-plugins/7sky-loader.php). Удалите её — новая версия не загружена.</p></div>';
			}
		}
	);
	return;
}

define( 'SKY_BASELINE_VERSION', '2.0.0' );
define( 'SKY_BASELINE_DIR', __DIR__ );

spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix = 'Sky\\Baseline\\';
		if ( ! str_starts_with( $class_name, $prefix ) ) {
			return;
		}
		$file = SKY_BASELINE_DIR . '/src/' . str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) ) . '.php';
		if ( is_readable( $file ) ) {
			require $file;
		}
	}
);

require SKY_BASELINE_DIR . '/inc/functions.php';

Sky\Baseline\Plugin::boot();
