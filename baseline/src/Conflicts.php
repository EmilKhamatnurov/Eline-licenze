<?php
/**
 * Защита от дублей с SEO-плагинами.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline;

defined( 'ABSPATH' ) || exit;

/**
 * Если активен SEO-плагин, модули с пересекающейся разметкой выключаются.
 */
final class Conflicts {

	/**
	 * Название активного SEO-плагина или ''.
	 */
	public static function seo_plugin(): string {
		$plugins = [
			'Yoast SEO'         => defined( 'WPSEO_VERSION' ),
			'Rank Math'         => defined( 'RANK_MATH_VERSION' ),
			'All in One SEO'    => defined( 'AIOSEO_VERSION' ) || function_exists( 'aioseo' ),
			'SEOPress'          => defined( 'SEOPRESS_VERSION' ),
			'The SEO Framework' => defined( 'THE_SEO_FRAMEWORK_VERSION' ),
		];
		foreach ( $plugins as $name => $active ) {
			if ( $active ) {
				return $name;
			}
		}
		return '';
	}

	/**
	 * Модули, которые надо выключить. Уведомление показывается один раз за запрос.
	 *
	 * @return string[]
	 */
	public static function disabled_modules(): array {
		if ( ! Config::get( 'seo_plugins.auto_disable', true ) ) {
			return [];
		}
		$plugin = self::seo_plugin();
		if ( '' === $plugin ) {
			return [];
		}

		$modules = array_values( (array) Config::get( 'seo_plugins.modules', [] ) );
		if ( $modules ) {
			Notices::add(
				sprintf(
					'активен %s — модули %s отключены, чтобы не дублировать Title, meta, canonical и микроразметку.',
					$plugin,
					implode( ', ', $modules )
				),
				'warning'
			);
		}
		return $modules;
	}
}
