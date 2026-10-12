<?php
/**
 * Данные организации: Customizer поверх конфига.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline;

defined( 'ABSPATH' ) || exit;

/**
 * Источник контактов и реквизитов.
 */
final class Organization {

	/**
	 * Поля, редактируемые в Customizer: ключ organization.* → подпись.
	 */
	public const CUSTOMIZER_FIELDS = [
		'phone'  => 'Телефон',
		'email'  => 'E-mail',
		'street' => 'Улица, дом',
		'city'   => 'Город',
		'region' => 'Регион',
		'zip'    => 'Индекс',
		'hours'  => 'Часы работы (Mo-Fr 09:00-18:00)',
		'lat'    => 'Широта',
		'lng'    => 'Долгота',
	];

	/**
	 * Значение: непустой theme_mod sky_{key}, иначе organization.{key} из конфига.
	 * theme_mod привязан к теме, поэтому основа — конфиг: смена темы не теряет контакты.
	 *
	 * @param string $key Ключ без префикса: phone, city, inn…
	 * @return mixed
	 */
	public static function get( string $key ) {
		$mod = get_theme_mod( 'sky_' . $key, '' );
		if ( is_string( $mod ) && '' !== trim( $mod ) ) {
			return trim( $mod );
		}
		$value = Config::get( 'organization.' . $key, '' );
		if ( 'name' === $key && '' === $value ) {
			return get_bloginfo( 'name' );
		}
		return $value;
	}

	/**
	 * Город в именительном падеже.
	 */
	public static function city(): string {
		if ( ! Config::get( 'city.enabled', true ) ) {
			return '';
		}
		$city = (string) Config::get( 'city.nominative', '' );
		return '' !== $city ? $city : (string) self::get( 'city' );
	}

	/**
	 * «в Уфе» / «во Владимире»; '' если город выключен или падеж не задан.
	 */
	public static function city_in(): string {
		if ( ! Config::get( 'city.enabled', true ) ) {
			return '';
		}
		$prep = (string) Config::get( 'city.prepositional', '' );
		if ( '' === $prep ) {
			return '';
		}
		return trim( Config::get( 'city.preposition', 'в' ) . ' ' . $prep );
	}
}
