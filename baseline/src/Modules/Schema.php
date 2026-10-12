<?php
/**
 * Модуль schema: JSON-LD.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline\Modules;

use Sky\Baseline\Config;
use Sky\Baseline\Module;
use Sky\Baseline\Organization;
use Sky\Baseline\Plugin;
use Sky\Baseline\Seo\Breadcrumbs as Chain;
use Sky\Baseline\Seo\Context;

defined( 'ABSPATH' ) || exit;

/**
 * Граф: организация, BreadcrumbList, FAQPage и узлы от темы.
 * Основной граф выводится в head. Узлы, добавленные шаблоном после wp_head
 * (например, компонент FAQ в теле страницы), выводятся вторым блоком в wp_footer.
 */
final class Schema implements Module {

	/**
	 * Узлы от sky_schema_add_node(), ещё не выведенные.
	 *
	 * @var array
	 */
	private static array $pending = [];

	/**
	 * Вопросы FAQ, ещё не выведенные.
	 *
	 * @var array
	 */
	private static array $faq = [];

	/**
	 * Уже добавленные вопросы за запрос. Компонент FAQ в контенте вызывается дважды:
	 * get_the_excerpt() для description прогоняет the_content ещё в <head>.
	 *
	 * @var array<string, true>
	 */
	private static array $faq_seen = [];

	/**
	 * Теги, допустимые в ответе FAQ.
	 */
	private const FAQ_TAGS = [
		'a'      => [ 'href' => true ],
		'p'      => [],
		'br'     => [],
		'ul'     => [],
		'ol'     => [],
		'li'     => [],
		'b'      => [],
		'strong' => [],
		'i'      => [],
		'em'     => [],
	];

	/**
	 * Хуки.
	 */
	public function register(): void {
		add_action( 'wp_head', [ $this, 'output_head' ], 5 );
		add_action( 'wp_footer', [ $this, 'output_late' ], 20 );
	}

	/**
	 * Добавить произвольный узел графа.
	 *
	 * @param array $node Узел schema.org.
	 */
	public static function add_node( array $node ): void {
		if ( $node ) {
			self::$pending[] = $node;
		}
	}

	/**
	 * Добавить вопросы в FAQPage. Принимает [ 'question'|'q' => …, 'answer'|'a' => … ].
	 * Несколько вызовов на странице дают один узел FAQPage.
	 *
	 * @param array $items Вопросы и ответы.
	 */
	public static function add_faq( array $items ): void {
		foreach ( $items as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$q   = Context::plain( (string) ( $item['question'] ?? $item['q'] ?? '' ) );
			$a   = trim( wp_kses( (string) ( $item['answer'] ?? $item['a'] ?? '' ), self::FAQ_TAGS ) );
			$key = md5( $q . "\0" . $a );
			if ( '' !== $q && '' !== $a && ! isset( self::$faq_seen[ $key ] ) ) {
				self::$faq_seen[ $key ] = true;
				self::$faq[]            = [
					'@type'          => 'Question',
					'name'           => $q,
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => $a,
					],
				];
			}
		}
	}

	/**
	 * Основной граф в head.
	 */
	public function output_head(): void {
		$graph = [ self::organization() ];

		if ( Plugin::active( 'breadcrumbs' ) && ! is_404() ) {
			$crumbs = self::breadcrumb_list();
			if ( $crumbs ) {
				$graph[] = $crumbs;
			}
		}

		$graph = array_merge( $graph, self::take_pending() );
		$graph = (array) apply_filters( 'sky_schema_graph', $graph );
		self::print( $graph );
	}

	/**
	 * Узлы, добавленные после wp_head.
	 */
	public function output_late(): void {
		self::print( self::take_pending() );
	}

	/**
	 * Узел организации из конфига и Customizer.
	 */
	public static function organization(): array {
		$org = [
			'@type'     => Config::get( 'schema.type', 'LocalBusiness' ),
			'@id'       => home_url( '/#organization' ),
			'name'      => Context::plain( (string) Organization::get( 'name' ) ),
			'legalName' => Organization::get( 'legal_name' ),
			'url'       => home_url( '/' ),
			'telephone' => Organization::get( 'phone' ),
			'email'     => Organization::get( 'email' ),
		];

		$address = array_filter(
			[
				'@type'           => 'PostalAddress',
				'streetAddress'   => Organization::get( 'street' ),
				'addressLocality' => Organization::get( 'city' ),
				'addressRegion'   => Organization::get( 'region' ),
				'postalCode'      => Organization::get( 'zip' ),
				'addressCountry'  => Organization::get( 'country' ),
			]
		);
		if ( isset( $address['streetAddress'] ) || isset( $address['addressLocality'] ) ) {
			$org['address'] = $address;
		}

		$lat = Organization::get( 'lat' );
		$lng = Organization::get( 'lng' );
		if ( is_numeric( $lat ) && is_numeric( $lng ) ) {
			$org['geo'] = [
				'@type'     => 'GeoCoordinates',
				'latitude'  => (float) $lat,
				'longitude' => (float) $lng,
			];
		}

		$hours = Organization::get( 'hours' );
		if ( is_string( $hours ) ) {
			$hours = preg_split( '/\s*[;\n]\s*/', $hours );
		}
		$org['openingHours'] = array_values( array_filter( (array) $hours ) );

		$logo = self::logo_url();
		if ( $logo ) {
			$org['logo']  = $logo;
			$org['image'] = $logo;
		}

		$org['priceRange'] = Config::get( 'schema.price_range', '' );
		$org['sameAs']     = array_values( array_filter( (array) Organization::get( 'same_as' ) ) );
		$org['areaServed'] = self::area_served();

		$inn = preg_replace( '/\D/', '', (string) Organization::get( 'inn' ) );
		if ( $inn ) {
			$org['taxID'] = $inn;
		}
		$org['identifier'] = self::identifiers( $inn );

		$org = array_filter( $org, static fn( $v ) => '' !== $v && null !== $v && [] !== $v );
		$org = array_replace( $org, (array) Config::get( 'schema.extra', [] ) );

		return (array) apply_filters( 'sky_schema_organization', $org );
	}

	/**
	 * BreadcrumbList с @id от URL текущего запроса — корректно для любого типа страницы.
	 */
	public static function breadcrumb_list(): array {
		$crumbs = Chain::items();
		if ( count( $crumbs ) < 2 ) {
			return [];
		}
		$list = [];
		foreach ( $crumbs as $i => $c ) {
			$el = [
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $c['name'],
			];
			if ( ! empty( $c['url'] ) ) {
				$el['item'] = $c['url'];
			}
			$list[] = $el;
		}
		return [
			'@type'           => 'BreadcrumbList',
			'@id'             => Context::current_url() . '#breadcrumb',
			'itemListElement' => $list,
		];
	}

	/**
	 * Забирает накопленные узлы и FAQ.
	 */
	private static function take_pending(): array {
		$nodes = self::$pending;
		if ( self::$faq ) {
			// Тот же @id у второго блока: парсеры JSON-LD сольют его с первым, а не создадут дубль.
			$nodes[] = [
				'@type'      => 'FAQPage',
				'@id'        => Context::current_url() . '#faq',
				'mainEntity' => self::$faq,
			];
		}
		self::$pending = [];
		self::$faq     = [];
		return $nodes;
	}

	/**
	 * Печать блока JSON-LD.
	 *
	 * @param array $graph Узлы.
	 */
	private static function print( array $graph ): void {
		$graph = array_values( array_filter( $graph ) );
		if ( ! $graph ) {
			return;
		}
		// JSON_HEX_TAG: строка с </script> в заголовке или ответе FAQ не закроет тег.
		$json = wp_json_encode(
			[
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			],
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
		);
		if ( $json ) {
			echo '<script type="application/ld+json">' . $json . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON с JSON_HEX_TAG.
		}
	}

	/**
	 * Логотип: organization.logo (ID или URL), иначе логотип темы.
	 */
	private static function logo_url(): string {
		$logo = Organization::get( 'logo' );
		if ( is_numeric( $logo ) && (int) $logo > 0 ) {
			return (string) wp_get_attachment_image_url( (int) $logo, 'full' );
		}
		if ( is_string( $logo ) && '' !== $logo ) {
			return $logo;
		}
		$mod = (int) get_theme_mod( 'custom_logo' );
		return $mod ? (string) wp_get_attachment_image_url( $mod, 'full' ) : '';
	}

	/**
	 * Строки становятся City, массивы передаются как есть.
	 */
	private static function area_served(): array {
		$out = [];
		foreach ( (array) Config::get( 'schema.area_served', [] ) as $area ) {
			if ( is_array( $area ) ) {
				$out[] = $area;
			} elseif ( '' !== (string) $area ) {
				$out[] = [
					'@type' => 'City',
					'name'  => (string) $area,
				];
			}
		}
		return $out;
	}

	/**
	 * ИНН, ОГРН/ОГРНИП, КПП как PropertyValue.
	 *
	 * @param string $inn ИНН (только цифры).
	 */
	private static function identifiers( string $inn ): array {
		$ogrn = (string) preg_replace( '/\D/', '', (string) Organization::get( 'ogrn' ) );
		$kpp  = (string) preg_replace( '/\D/', '', (string) Organization::get( 'kpp' ) );
		$ids  = [
			'ИНН'                                          => $inn,
			( 15 === strlen( $ogrn ) ? 'ОГРНИП' : 'ОГРН' ) => $ogrn,
			'КПП'                                          => $kpp,
		];

		$out = [];
		foreach ( array_filter( $ids ) as $name => $value ) {
			$out[] = [
				'@type'      => 'PropertyValue',
				'propertyID' => $name,
				'value'      => $value,
			];
		}
		return $out;
	}
}
