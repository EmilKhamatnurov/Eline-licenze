<?php
/**
 * Sky Starter — дочерняя тема Kadence.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

require __DIR__ . '/inc/components.php';
require __DIR__ . '/inc/kadence.php';
require __DIR__ . '/inc/layout.php';
require __DIR__ . '/inc/lead-form.php';
require __DIR__ . '/inc/license.php';
require __DIR__ . '/inc/quiz.php';

add_action( 'wp_enqueue_scripts', 'sky_starter_enqueue', 20 );
add_action( 'wp_head', 'sky_starter_preload_fonts', 1 );
add_action( 'after_setup_theme', 'sky_starter_setup' );
add_action( 'admin_notices', 'sky_starter_baseline_notice' );

/**
 * Стили: версия по filemtime, чтобы правка CSS сбрасывала кэш браузера без ручного bump.
 * Порядок: шрифты → токены → база → кнопки и поля → каркас → компоненты.
 */
function sky_starter_enqueue(): void {
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();

	$styles = [
		'sky-fonts'      => [ 'fonts.css', [] ],
		// После kadence-global: токены перекрывают его палитру с тем же селектором :root.
		'sky-tokens'     => [ 'tokens.css', [ 'kadence-global', 'sky-fonts' ] ],
		'sky-base'       => [ 'base.css', [ 'sky-tokens' ] ],
		'sky-ui'         => [ 'ui.css', [ 'sky-base' ] ],
		'sky-layout'     => [ 'layout.css', [ 'sky-ui' ] ],
		'sky-components' => [ 'components.css', [ 'sky-layout' ] ],
	];
	// Главная, страница лицензии и каталог — свои файлы, остальным они не нужны.
	if ( is_singular( 'license' ) ) {
		$styles['sky-license'] = [ 'license.css', [ 'sky-components' ] ];
	}
	if ( is_post_type_archive( 'license' ) || is_tax( 'regulator' ) ) {
		$styles['sky-catalog'] = [ 'catalog.css', [ 'sky-components' ] ];
	}
	if ( ( is_page() && ! is_front_page() ) || is_home() || is_category() ) {
		$styles['sky-pages'] = [ 'pages.css', [ 'sky-components' ] ];
	}
	if ( is_front_page() ) {
		$styles['sky-home'] = [ 'home.css', [ 'sky-components' ] ];
		$styles['sky-quiz'] = [ 'quiz.css', [ 'sky-components' ] ]; // Квиз на главной — стили в <head>, без вспышки.
	}
	foreach ( $styles as $handle => [ $file, $deps ] ) {
		wp_enqueue_style( $handle, $uri . '/assets/css/' . $file, $deps, (string) filemtime( $dir . '/assets/css/' . $file ) );
	}

	wp_enqueue_script(
		'sky-header',
		$uri . '/assets/js/header.js',
		[],
		(string) filemtime( $dir . '/assets/js/header.js' ),
		[
			'in_footer' => true,
			'strategy'  => 'defer',
		]
	);

	// Вкладки и аккордеон: компоненты doc-tabs и faq ставят скрипт в очередь сами.
	wp_register_script(
		'sky-ui',
		$uri . '/assets/js/ui.js',
		[],
		(string) filemtime( $dir . '/assets/js/ui.js' ),
		[
			'in_footer' => true,
			'strategy'  => 'defer',
		]
	);

	// Каталог лицензий: поиск и фильтр без перезагрузки (template-parts/license/catalog.php).
	wp_register_script(
		'sky-catalog',
		$uri . '/assets/js/catalog.js',
		[],
		(string) filemtime( $dir . '/assets/js/catalog.js' ),
		[
			'in_footer' => true,
			'strategy'  => 'defer',
		]
	);

	// Квиз — модуль inc/quiz.php: компонент quiz ставит скрипт и стили в очередь сам.
	wp_register_style( 'sky-quiz', $uri . '/assets/css/quiz.css', [ 'sky-components' ], (string) filemtime( $dir . '/assets/css/quiz.css' ) );
	wp_register_script(
		'sky-quiz',
		$uri . '/assets/js/quiz.js',
		[],
		(string) filemtime( $dir . '/assets/js/quiz.js' ),
		[
			'in_footer' => true,
			'strategy'  => 'defer',
		]
	);

	// Форма заявки: компонент lead-form сам ставит скрипт в очередь, только там, где выведен.
	wp_register_script(
		'sky-lead-form',
		$uri . '/assets/js/lead-form.js',
		[],
		(string) filemtime( $dir . '/assets/js/lead-form.js' ),
		[
			'in_footer' => true,
			'strategy'  => 'defer',
		]
	);
	wp_add_inline_script( 'sky-lead-form', 'window.skyLeadForm=' . wp_json_encode( [ 'messages' => sky_starter_lead_messages() ] ) . ';', 'before' );
}

/**
 * Предзагрузка кириллицы обоих шрифтов — первый экран без подмены шрифта.
 */
function sky_starter_preload_fonts(): void {
	$uri = get_stylesheet_directory_uri() . '/assets/fonts/';
	foreach ( [ 'manrope-cyrillic-wght.woff2', 'cormorant-garamond-cyrillic-wght.woff2' ] as $file ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( $uri . $file ) );
	}
}

/**
 * Токены и шрифты в редакторе блоков — палитра Kadence там тоже ссылается на них.
 */
function sky_starter_setup(): void {
	add_theme_support( 'editor-styles' );
	add_editor_style( [ 'assets/css/fonts.css', 'assets/css/tokens.css', 'assets/css/base.css', 'assets/css/ui.css', 'assets/css/components.css' ] );
}

/**
 * Тема рассчитана на mu-plugin 7sky baseline.
 */
function sky_starter_baseline_notice(): void {
	if ( ! defined( 'SKY_BASELINE_VERSION' ) && current_user_can( 'manage_options' ) ) {
		echo '<div class="notice notice-error"><p>Sky Starter: не найден mu-plugin 7sky baseline — крошки, формы и SEO-разметка не работают. Запустите bin/link-local.sh.</p></div>';
	}
}
