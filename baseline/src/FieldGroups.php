<?php
/**
 * Регистрация групп полей в формате Meta Box — в Meta Box или ACF.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline;

defined( 'ABSPATH' ) || exit;

/**
 * Группы описываются как мета-боксы Meta Box: id, title, post_types, fields[ id, name, type ].
 * Для ACF они конвертируются в local field groups.
 *
 * Поле типа group есть только в платных расширениях (Meta Box Group / AIO, repeater — ACF PRO).
 * Без них группа становится textarea: элементы разделяются пустой строкой, строки элемента —
 * подполя по порядку, последнее подполе может занимать несколько строк. sky_field_raw()
 * в обоих случаях возвращает массив, так что шаблонам разница не видна.
 */
final class FieldGroups {

	/**
	 * Группы.
	 *
	 * @var array
	 */
	private static array $boxes = [];

	/**
	 * Поля-группы верхнего уровня: id => [ 'fields' => [ id подполей ], 'clone' => bool ].
	 *
	 * @var array<string, array{fields: string[], clone: bool}>
	 */
	private static array $groups = [];

	/**
	 * Хуки повешены.
	 *
	 * @var bool
	 */
	private static bool $hooked = false;

	/**
	 * ACF-типы для типов Meta Box. Остальные становятся text.
	 */
	private const ACF_TYPES = [
		'text'         => 'text',
		'textarea'     => 'textarea',
		'wysiwyg'      => 'wysiwyg',
		'number'       => 'number',
		'email'        => 'email',
		'url'          => 'url',
		'tel'          => 'text',
		'select'       => 'select',
		'radio'        => 'radio',
		'checkbox'     => 'true_false',
		'image'        => 'image',
		'single_image' => 'image',
		'file'         => 'file',
		'date'         => 'date_picker',
		'post'         => 'post_object',
		'group'        => 'group',
	];

	/**
	 * Добавить группу.
	 *
	 * @param array $box Мета-бокс в формате Meta Box.
	 */
	public static function add( array $box ): void {
		if ( empty( $box['id'] ) || empty( $box['fields'] ) ) {
			return;
		}
		self::$boxes[] = $box;
		foreach ( (array) $box['fields'] as $field ) {
			if ( 'group' === ( $field['type'] ?? '' ) && ! empty( $field['id'] ) ) {
				self::$groups[ (string) $field['id'] ] = [
					'fields' => array_values( array_filter( array_column( (array) ( $field['fields'] ?? [] ), 'id' ) ) ),
					'clone'  => ! empty( $field['clone'] ),
				];
			}
		}
		if ( ! self::$hooked ) {
			self::$hooked = true;
			add_filter( 'rwmb_meta_boxes', [ self::class, 'meta_box' ] );
			add_filter( 'rwmb_field_meta', [ self::class, 'meta_box_value' ], 10, 2 );
			add_action( 'acf/init', [ self::class, 'acf' ] );
		}
	}

	/**
	 * Описание поля-группы по ключу или null, если это не группа.
	 *
	 * @param string $key Ключ поля.
	 * @return array{fields: string[], clone: bool}|null
	 */
	public static function group( string $key ): ?array {
		return self::$groups[ $key ] ?? null;
	}

	/**
	 * Фильтр rwmb_meta_boxes. Срабатывает, только если Meta Box — выбранный провайдер.
	 *
	 * @param array $boxes Мета-боксы.
	 */
	public static function meta_box( array $boxes ): array {
		if ( 'meta-box' !== Fields::provider() ) {
			return $boxes;
		}
		$own = self::resolved();
		if ( ! class_exists( 'RWMB_Group_Field' ) ) {
			foreach ( $own as &$box ) {
				$box['fields'] = array_map( [ self::class, 'text_fallback' ], (array) $box['fields'] );
			}
			unset( $box );
		}
		return array_merge( $boxes, $own );
	}

	/**
	 * Фильтр rwmb_field_meta: массив, сохранённый настоящей группой, показываем в textarea текстом.
	 *
	 * @param mixed $meta  Значение.
	 * @param array $field Поле.
	 * @return mixed
	 */
	public static function meta_box_value( $meta, $field ) {
		if ( ! empty( $field['sky_group'] ) && is_array( $meta ) ) {
			return self::encode( $meta, (string) $field['id'] );
		}
		return $meta;
	}

	/**
	 * Регистрация в ACF.
	 */
	public static function acf(): void {
		if ( 'acf' !== Fields::provider() || ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}
		foreach ( self::resolved() as $box ) {
			$location = [];
			foreach ( (array) $box['post_types'] as $pt ) {
				$location[] = [
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => $pt,
					],
				];
			}
			acf_add_local_field_group(
				[
					'key'      => 'group_sky_' . $box['id'],
					'title'    => $box['title'] ?? $box['id'],
					'fields'   => self::acf_fields( (array) $box['fields'], 'sky_' . $box['id'] ),
					'location' => $location,
					'position' => ( $box['context'] ?? '' ) === 'side' ? 'side' : 'normal',
				]
			);
		}
	}

	/**
	 * post_types = '*' раскрывается в публичные типы записей.
	 */
	private static function resolved(): array {
		$out = [];
		foreach ( self::$boxes as $box ) {
			if ( '*' === ( $box['post_types'] ?? '' ) ) {
				$box['post_types'] = array_values( array_diff( get_post_types( [ 'public' => true ] ), [ 'attachment' ] ) );
			}
			$box['post_types'] = (array) ( $box['post_types'] ?? [ 'post' ] );
			$out[]             = $box;
		}
		return $out;
	}

	/**
	 * Поля Meta Box → поля ACF.
	 *
	 * @param array  $fields Поля.
	 * @param string $prefix Префикс ключа.
	 */
	private static function acf_fields( array $fields, string $prefix ): array {
		$out = [];
		foreach ( $fields as $field ) {
			if ( empty( $field['id'] ) ) {
				continue;
			}
			$type = self::ACF_TYPES[ $field['type'] ?? 'text' ] ?? 'text';
			$key  = $prefix . '_' . $field['id'];
			$acf  = [
				'key'          => 'field_' . $key,
				'name'         => $field['id'],
				'label'        => $field['name'] ?? $field['id'],
				'type'         => $type,
				'instructions' => $field['desc'] ?? '',
			];
			if ( isset( $field['options'] ) ) {
				$acf['choices'] = (array) $field['options'];
			}
			if ( 'image' === $type || 'file' === $type ) {
				$acf['return_format'] = 'id';
			}
			if ( 'group' === $type ) {
				// Клонируемая группа Meta Box = repeater ACF, а он есть только в ACF PRO.
				if ( ! empty( $field['clone'] ) && ! ( defined( 'ACF_PRO' ) && ACF_PRO ) ) {
					$text                = self::text_fallback( $field );
					$acf['type']         = 'textarea';
					$acf['instructions'] = $text['desc'];
					$acf['rows']         = $text['rows'];
					$acf['new_lines']    = '';
					$out[]               = $acf;
					$id                  = (string) $field['id'];
					add_filter(
						'acf/load_value/key=' . $acf['key'],
						static fn( $value ) => is_array( $value ) ? self::encode( $value, $id ) : $value
					);
					continue;
				}
				$acf['sub_fields'] = self::acf_fields( (array) ( $field['fields'] ?? [] ), $key );
				if ( ! empty( $field['clone'] ) ) {
					$acf['type'] = 'repeater';
				}
			}
			$out[] = $acf;
		}
		return $out;
	}

	/**
	 * Группа → textarea с подсказкой о формате. Остальные поля возвращаются как есть.
	 *
	 * @param array $field Поле в формате Meta Box.
	 */
	private static function text_fallback( array $field ): array {
		if ( 'group' !== ( $field['type'] ?? '' ) || empty( $field['id'] ) ) {
			return $field;
		}
		$names = array_map(
			static fn( $sub ) => (string) ( $sub['name'] ?? $sub['id'] ?? '' ),
			(array) ( $field['fields'] ?? [] )
		);
		$desc  = ! empty( $field['clone'] )
			? 'Каждый пункт — отдельный блок, блоки разделяются пустой строкой. Строки блока: '
			: 'Строки: ';
		$desc .= implode( '; ', $names ) . '. Последняя часть может занимать несколько строк, но без пустых.';

		return [
			'id'        => $field['id'],
			'name'      => $field['name'] ?? $field['id'],
			'type'      => 'textarea',
			'rows'      => 8,
			'desc'      => trim( ( isset( $field['desc'] ) ? $field['desc'] . ' ' : '' ) . $desc ),
			'sky_group' => true,
		];
	}

	/**
	 * Текст из textarea → массив, как у настоящей группы.
	 *
	 * @param string $text Значение поля.
	 * @param string $key  Ключ поля-группы.
	 */
	public static function decode( string $text, string $key ): array {
		$group = self::group( $key );
		if ( ! $group || ! $group['fields'] ) {
			return [];
		}
		$subs  = $group['fields'];
		$last  = count( $subs ) - 1;
		$items = [];
		$text  = str_replace( [ "\r\n", "\r" ], "\n", $text );
		foreach ( preg_split( '/\n[ \t]*\n/', trim( $text ) ) as $block ) {
			$lines = explode( "\n", trim( $block ) );
			if ( '' === trim( $lines[0] ) ) {
				continue;
			}
			$item = [];
			foreach ( $subs as $i => $sub ) {
				$item[ $sub ] = $i < $last
					? trim( $lines[ $i ] ?? '' )
					: trim( implode( "\n", array_slice( $lines, $i ) ) );
			}
			$items[] = $item;
		}
		return $group['clone'] ? $items : ( $items[0] ?? [] );
	}

	/**
	 * Массив группы → текст для textarea (обратное к decode()).
	 *
	 * @param array  $value Значение группы.
	 * @param string $key   Ключ поля-группы.
	 */
	public static function encode( array $value, string $key ): string {
		$group = self::group( $key );
		if ( ! $group ) {
			return '';
		}
		$subs   = $group['fields'];
		$last   = count( $subs ) - 1;
		$items  = $group['clone'] ? $value : [ $value ];
		$blocks = [];
		foreach ( $items as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$lines = [];
			foreach ( $subs as $i => $sub ) {
				$v = is_scalar( $item[ $sub ] ?? '' ) ? (string) ( $item[ $sub ] ?? '' ) : '';
				$v = str_replace( [ "\r\n", "\r" ], "\n", trim( $v ) );
				// В середине блока перевод строки сдвинул бы подполя, в конце — пустая строка разорвала бы блок.
				$lines[] = $i < $last ? str_replace( "\n", ' ', $v ) : preg_replace( '/\n\s*\n/', "\n", $v );
			}
			$blocks[] = implode( "\n", $lines );
		}
		return implode( "\n\n", $blocks );
	}
}
