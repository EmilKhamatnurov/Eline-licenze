<?php
/**
 * Тесты чистой логики baseline без WordPress: заглушки функций WP + проверки.
 *
 *   php tests/run.php
 *
 * Код выхода 0 — всё прошло. Это не замена проверке на живом сайте (см. чек-лист запуска),
 * а защита от поломок в редиректах, группах полей, токенах Title и robots.txt.
 */

define( 'ABSPATH', '/' );
define( 'WP_CONTENT_DIR', __DIR__ );
define( 'SKY_BASELINE_DIR', dirname( __DIR__ ) . '/baseline' );
define( 'SKY_CONFIG_PATH', __DIR__ . '/fixtures/sky-config.php' );

// phpcs:disable
function apply_filters( $tag, $value ) { return $value; }
function add_action( ...$args ) {}
function add_filter( ...$args ) {}
function is_404() { return true; }
function is_admin() { return false; }
function wp_doing_ajax() { return false; }
function is_preview() { return false; }
function wp_unslash( $v ) { return $v; }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }
function home_url( $p = '' ) { return 'https://site.test' . $p; }
function trailingslashit( $s ) { return rtrim( $s, '/' ) . '/'; }
function get_theme_mod( $k, $d = '' ) { return $d; }
function get_bloginfo( $k ) { return 'name' === $k ? 'Сайт' : ''; }
function wp_strip_all_tags( $s ) { return strip_tags( $s ); }

class Sky_Test_Redirect extends Exception {}
function wp_safe_redirect( $url, $code ) { throw new Sky_Test_Redirect( $url ); }
function wp_redirect( $url, $code ) { throw new Sky_Test_Redirect( $url ); }
// phpcs:enable

require SKY_BASELINE_DIR . '/src/Module.php';
spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix = 'Sky\\Baseline\\';
		if ( str_starts_with( $class_name, $prefix ) ) {
			$file = SKY_BASELINE_DIR . '/src/' . str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) ) . '.php';
			if ( is_readable( $file ) ) {
				require $file;
			}
		}
	}
);

use Sky\Baseline\FieldGroups;
use Sky\Baseline\Modules\Redirects;
use Sky\Baseline\Modules\RobotsTxt;
use Sky\Baseline\Seo\Context;

$failed = 0;
$total  = 0;

/**
 * Проверка равенства.
 *
 * @param string $name     Название проверки.
 * @param mixed  $expected Ожидаемое.
 * @param mixed  $actual   Фактическое.
 */
function check( string $name, $expected, $actual ): void {
	global $failed, $total;
	++$total;
	if ( $expected === $actual ) {
		echo "ok    $name\n";
		return;
	}
	++$failed;
	echo "FAIL  $name\n      ожидалось: " . json_encode( $expected, JSON_UNESCAPED_UNICODE ) . "\n      получено:  " . json_encode( $actual, JSON_UNESCAPED_UNICODE ) . "\n";
}

/**
 * Что сделает модуль redirects на данном REQUEST_URI.
 *
 * @param string $uri REQUEST_URI.
 */
function redirect_for( string $uri ): string {
	$_SERVER['REQUEST_URI'] = $uri;
	try {
		$module = new Redirects();
		$module->from_map();
		$module->normalize();
		return 'none';
	} catch ( Sky_Test_Redirect $e ) {
		return $e->getMessage();
	}
}

$enc = static fn( string $path ): string => implode( '/', array_map( 'rawurlencode', explode( '/', $path ) ) );

// Редиректы: нормализация.
check( 'clean: регистр', '/uslugi/', Redirects::clean( '/Uslugi/' ) );
check( 'clean: двойные слэши', '/uslugi/a/', Redirects::clean( '//uslugi//a/' ) );
check( 'clean: %XX не трогаем', '/%D0%BF%D1%80/abc/', Redirects::clean( '/%D0%BF%D1%80/Abc/' ) );

// Редиректы: карта.
check( 'map: ключ кириллицей', 'https://site.test/uslugi/remont/', redirect_for( $enc( '/услуги/ремонт/' ) ) );
check( 'map: ключ в %XX', 'https://site.test/ceny/', redirect_for( '/%D1%86%D0%B5%D0%BD%D1%8B/' ) );
check( 'map: .html без слэша', 'https://site.test/kontakty/', redirect_for( '/old-page.html' ) );
check( 'map: query сохраняется', 'https://site.test/kontakty/?utm_source=x', redirect_for( '/old-page.html?utm_source=x' ) );
check( 'map: префикс', 'https://site.test/uslugi/foo/', redirect_for( '/services/foo/' ) );
check( 'map: цель = источник не даёт петлю', 'none', redirect_for( '/loop/' ) );
check( 'normalize: /Uslugi/ → /uslugi/', 'https://site.test/uslugi/', redirect_for( '/Uslugi/' ) );

// Группы полей.
FieldGroups::add(
	[
		'id'     => 'box',
		'fields' => [
			[
				'id'     => 'faq',
				'type'   => 'group',
				'clone'  => true,
				'fields' => [ [ 'id' => 'question' ], [ 'id' => 'answer' ] ],
			],
		],
	]
);
$text  = "Сколько стоит?\nОт 5000 ₽.\nВторая строка.\n\nМожно ли частями?\nДа.";
$items = FieldGroups::decode( $text, 'faq' );
check( 'group: число пунктов', 2, count( $items ) );
check( 'group: многострочный ответ', "От 5000 ₽.\nВторая строка.", $items[0]['answer'] );
check( 'group: encode(decode(x)) = x', $text, FieldGroups::encode( $items, 'faq' ) );

// Токены Title.
check( 'render: падеж города', 'Банкротство в Уфе — цены | Сайт', Context::render( '%title% %city_in% — цены | %site%', [ '%title%' => 'Банкротство' ] ) );
check( 'render: пустой токен без висящих разделителей', 'Банкротство', Context::render( '%title% | %phone%', [ '%title%' => 'Банкротство' ] ) );

// robots.txt.
$core = "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n\nSitemap: https://site.test/wp-sitemap.xml\n";
$out  = RobotsTxt::build( $core );
check( 'robots.txt: Disallow внутри группы *', true, str_contains( $out, "User-agent: *\nDisallow: /*?s=\nDisallow: /wp-admin/" ) );
check( 'robots.txt: Clean-param добавлен', true, str_contains( $out, "\nClean-param: utm_source&utm_medium\n" ) );
check( 'robots.txt: Sitemap сохранён', true, str_contains( $out, 'Sitemap: https://site.test/wp-sitemap.xml' ) );

echo "\nПроверок: $total, провалено: $failed\n";
exit( $failed ? 1 : 0 );
