<?php
/**
 * Bootstrap.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline;

defined( 'ABSPATH' ) || exit;

/**
 * Включает модули из конфига.
 */
final class Plugin {

	/**
	 * Имя модуля в конфиге → класс.
	 */
	public const MODULES = [
		'settings'    => Modules\Settings::class,
		'meta'        => Modules\Meta::class,
		'robots'      => Modules\Robots::class,
		'canonical'   => Modules\Canonical::class,
		'breadcrumbs' => Modules\Breadcrumbs::class,
		'schema'      => Modules\Schema::class,
		'redirects'   => Modules\Redirects::class,
		'forms'       => Modules\Forms::class,
		'cpt'         => Modules\Cpt::class,
		'cleanup'     => Modules\Cleanup::class,
		'opengraph'   => Modules\OpenGraph::class,
		'sitemap'     => Modules\Sitemap::class,
		'robots_txt'  => Modules\RobotsTxt::class,
		'analytics'   => Modules\Analytics::class,
	];

	/**
	 * Включённые модули.
	 *
	 * @var array<string, Module>
	 */
	private static array $active = [];

	/**
	 * Точка входа из sky-baseline.php.
	 */
	public static function boot(): void {
		// SEO-плагины загружаются после mu-plugins, а тема может фильтровать sky_config —
		// поэтому модули стартуют на after_setup_theme, до init.
		add_action( 'after_setup_theme', [ self::class, 'register_modules' ], 1 );
	}

	/**
	 * Регистрирует включённые модули.
	 */
	public static function register_modules(): void {
		$flags    = (array) Config::get( 'modules', [] );
		$disabled = Conflicts::disabled_modules();

		foreach ( self::MODULES as $name => $class ) {
			if ( empty( $flags[ $name ] ) || in_array( $name, $disabled, true ) ) {
				continue;
			}
			$module = new $class();
			$module->register();
			self::$active[ $name ] = $module;
		}
	}

	/**
	 * Включён ли модуль.
	 *
	 * @param string $name Имя модуля.
	 */
	public static function active( string $name ): bool {
		return isset( self::$active[ $name ] );
	}
}
