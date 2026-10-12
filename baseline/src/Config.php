<?php
/**
 * Конфиг: дефолты ядра + sky-config.php проекта.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline;

defined( 'ABSPATH' ) || exit;

/**
 * Загрузка, слияние и чтение конфига по точечному пути.
 */
final class Config {

	/**
	 * Итоговый конфиг.
	 *
	 * @var array|null
	 */
	private static ?array $data = null;

	/**
	 * Путь к конфигу проекта.
	 */
	public static function path(): string {
		$path = defined( 'SKY_CONFIG_PATH' ) ? (string) SKY_CONFIG_PATH : WP_CONTENT_DIR . '/sky-config.php';
		return (string) apply_filters( 'sky_config_path', $path );
	}

	/**
	 * Весь конфиг.
	 */
	public static function all(): array {
		if ( null === self::$data ) {
			self::$data = self::load();
		}
		return self::$data;
	}

	/**
	 * Значение по пути вида 'schema.type'.
	 *
	 * @param string $key      Путь через точку.
	 * @param mixed  $fallback Значение, если ключа нет.
	 * @return mixed
	 */
	public static function get( string $key, $fallback = null ) {
		$value = self::all();
		foreach ( explode( '.', $key ) as $part ) {
			if ( ! is_array( $value ) || ! array_key_exists( $part, $value ) ) {
				return $fallback;
			}
			$value = $value[ $part ];
		}
		return $value;
	}

	/**
	 * Сброс кэша — для тестов.
	 */
	public static function reset(): void {
		self::$data = null;
	}

	/**
	 * Ассоциативные массивы сливаются рекурсивно, списки заменяются целиком:
	 * иначе нельзя было бы убрать значение из списка по умолчанию.
	 *
	 * @param array $base     Базовый массив.
	 * @param array $override Перекрывающий массив.
	 */
	public static function merge( array $base, array $override ): array {
		foreach ( $override as $key => $value ) {
			if ( is_array( $value ) && isset( $base[ $key ] ) && is_array( $base[ $key ] ) && ! array_is_list( $base[ $key ] ) ) {
				$base[ $key ] = self::merge( $base[ $key ], $value );
			} else {
				$base[ $key ] = $value;
			}
		}
		return $base;
	}

	/**
	 * Читает дефолты и конфиг проекта.
	 */
	private static function load(): array {
		$defaults = require SKY_BASELINE_DIR . '/config/defaults.php';
		$path     = self::path();
		$project  = [];

		if ( is_readable( $path ) ) {
			$loaded = include $path;
			if ( is_array( $loaded ) ) {
				$project = $loaded;
			} else {
				Notices::add( sprintf( 'Файл %s должен возвращать массив — используются значения по умолчанию.', $path ) );
			}
		} else {
			Notices::add( sprintf( 'Конфиг проекта не найден (%s) — используются значения по умолчанию.', $path ), 'warning' );
		}

		return (array) apply_filters( 'sky_config', self::merge( $defaults, $project ) );
	}
}
