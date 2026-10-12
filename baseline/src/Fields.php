<?php
/**
 * Адаптер произвольных полей: Meta Box или ACF.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline;

defined( 'ABSPATH' ) || exit;

/**
 * Чтение полей без привязки к конкретному плагину.
 */
final class Fields {

	/**
	 * Определённый провайдер: 'meta-box', 'acf' или ''.
	 *
	 * @var string|null
	 */
	private static ?string $provider = null;

	/**
	 * Активный провайдер по приоритету из fields.providers.
	 * Плагины грузятся после mu-plugins, поэтому определяем лениво.
	 */
	public static function provider(): string {
		if ( null !== self::$provider ) {
			return self::$provider;
		}
		self::$provider = '';
		foreach ( (array) Config::get( 'fields.providers', [] ) as $name ) {
			if ( 'meta-box' === $name && function_exists( 'rwmb_meta' ) ) {
				self::$provider = 'meta-box';
				break;
			}
			if ( 'acf' === $name && function_exists( 'get_field' ) ) {
				self::$provider = 'acf';
				break;
			}
		}
		return self::$provider;
	}

	/**
	 * Значение поля как есть (массивы для групп и клонов).
	 *
	 * @param string   $key Ключ поля.
	 * @param int|null $id  ID записи; по умолчанию текущая.
	 * @return mixed
	 */
	public static function raw( string $key, ?int $id = null ) {
		$id = $id ? $id : (int) get_the_ID();
		if ( ! $id ) {
			return null;
		}

		switch ( self::provider() ) {
			case 'meta-box':
				$value = rwmb_meta( $key, [], $id );
				break;
			case 'acf':
				$value = get_field( $key, $id );
				break;
			default:
				// Оба плагина хранят простые поля в post meta под тем же ключом.
				$value = get_post_meta( $id, $key, true );
		}

		// Группа без платного расширения хранится текстом (см. FieldGroups).
		if ( FieldGroups::group( $key ) && ! is_array( $value ) ) {
			return FieldGroups::decode( is_scalar( $value ) ? (string) $value : '', $key );
		}
		return $value;
	}

	/**
	 * Значение поля строкой; массивы и пустые значения — ''.
	 *
	 * @param string   $key Ключ поля.
	 * @param int|null $id  ID записи.
	 */
	public static function string( string $key, ?int $id = null ): string {
		$value = self::raw( $key, $id );
		return is_scalar( $value ) && false !== $value ? (string) $value : '';
	}
}
