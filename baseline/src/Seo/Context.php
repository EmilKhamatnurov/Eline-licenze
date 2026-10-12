<?php
/**
 * SEO-данные текущего запроса: Title, description, canonical, картинка.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline\Seo;

use Sky\Baseline\Config;
use Sky\Baseline\Fields;
use Sky\Baseline\Organization;

defined( 'ABSPATH' ) || exit;

/**
 * Считается один раз за запрос и используется модулями meta, opengraph, schema, canonical.
 * Все методы возвращают обычный текст без HTML-сущностей — экранирование при выводе.
 */
final class Context {

	/**
	 * Кэш на запрос.
	 *
	 * @var array<string, mixed>
	 */
	private static array $cache = [];

	/**
	 * Сброс кэша — для тестов.
	 */
	public static function reset(): void {
		self::$cache = [];
	}

	/**
	 * Шаблоны Title (фильтр sky_title_templates сохранён).
	 */
	public static function templates(): array {
		return (array) apply_filters( 'sky_title_templates', (array) Config::get( 'title.templates', [] ) );
	}

	/**
	 * Подстановка токенов %site%, %tagline%, %city%, %city_in%, %phone% и переданных.
	 * Пустой токен не оставляет двойных пробелов и висящих разделителей.
	 *
	 * @param string $tpl   Шаблон.
	 * @param array  $extra Дополнительные токены.
	 */
	public static function render( string $tpl, array $extra = [] ): string {
		$tokens = array_merge(
			[
				'%site%'    => get_bloginfo( 'name' ),
				'%tagline%' => get_bloginfo( 'description' ),
				'%city%'    => Organization::city(),
				'%city_in%' => Organization::city_in(),
				'%phone%'   => (string) Organization::get( 'phone' ),
			],
			$extra
		);
		$out    = strtr( $tpl, array_map( 'strval', $tokens ) );
		$out    = (string) preg_replace( '/\s{2,}/u', ' ', $out );
		$out    = (string) preg_replace( '/^[\s|—–:,-]+|[\s|—–:,-]+$/u', '', $out );
		return self::plain( $out );
	}

	/**
	 * Title документа.
	 */
	public static function title(): string {
		if ( isset( self::$cache['title'] ) ) {
			return self::$cache['title'];
		}

		$tpl   = self::templates();
		$pick  = static fn( string $key, string $fallback ): string => (string) ( $tpl[ $key ] ?? $tpl[ $fallback ] ?? '%title% | %site%' );
		$id    = self::object_id();
		$title = '';

		$override = $id ? Fields::string( 'seo_title', $id ) : '';

		if ( '' !== $override ) {
			$title = self::plain( $override );
		} elseif ( is_front_page() ) {
			$title = self::render( $pick( 'front', 'page' ) );
		} elseif ( is_home() ) {
			$title = self::render( $pick( 'home', 'page' ), [ '%title%' => $id ? get_the_title( $id ) : 'Блог' ] );
		} elseif ( is_singular() ) {
			$pt    = (string) get_post_type( $id );
			$key   = isset( $tpl[ $pt ] ) ? $pt : ( is_page() ? 'page' : 'post' );
			$title = self::render( $pick( $key, 'post' ), [ '%title%' => get_the_title( $id ) ] );
		} elseif ( is_post_type_archive() ) {
			$pt    = self::archive_post_type();
			$title = self::render( $pick( 'archive_' . $pt, 'archive' ), [ '%archive%' => post_type_archive_title( '', false ) ] );
		} elseif ( is_tax() || is_category() || is_tag() ) {
			$term  = get_queried_object();
			$title = self::render( $pick( 'tax_' . $term->taxonomy, 'tax' ), [ '%term%' => $term->name ] );
		} elseif ( is_author() ) {
			$title = self::render( $pick( 'author', 'page' ), [ '%title%' => get_the_author_meta( 'display_name', (int) get_queried_object_id() ) ] );
		} elseif ( is_date() ) {
			$title = self::render( $pick( 'date', 'page' ), [ '%title%' => wp_strip_all_tags( get_the_archive_title() ) ] );
		} elseif ( is_search() ) {
			$title = self::render( $pick( 'search', 'page' ), [ '%query%' => get_search_query( false ) ] );
		} elseif ( is_404() ) {
			$title = self::render( $pick( '404', 'page' ) );
		} else {
			$title = self::plain( get_bloginfo( 'name' ) );
		}

		$paged = self::paged();
		if ( $paged > 1 && ! is_404() ) {
			$suffix = strtr( (string) Config::get( 'title.paged_suffix', ' — страница %page%' ), [ '%page%' => (string) $paged ] );
			$site   = ' | ' . self::plain( get_bloginfo( 'name' ) );
			// Номер страницы встаёт перед « | Сайт», чтобы Title каждой страницы списка был уникален.
			$title = str_ends_with( $title, $site )
				? substr( $title, 0, -strlen( $site ) ) . $suffix . $site
				: $title . $suffix;
		}

		self::$cache['title'] = (string) apply_filters( 'sky_document_title', $title );
		return self::$cache['title'];
	}

	/**
	 * Meta description.
	 */
	public static function description(): string {
		if ( isset( self::$cache['description'] ) ) {
			return self::$cache['description'];
		}

		$id = self::object_id();
		$d  = $id ? Fields::string( 'seo_description', $id ) : '';

		if ( '' === $d ) {
			if ( is_front_page() && ! is_singular() ) {
				$d = get_bloginfo( 'description' );
			} elseif ( is_front_page() || is_home() || is_singular() ) {
				$d = ! $id ? '' : ( has_excerpt( $id ) ? get_the_excerpt( $id ) : self::content_summary( $id ) );
				if ( '' === trim( $d ) && is_front_page() ) {
					$d = get_bloginfo( 'description' );
				}
			} elseif ( is_tax() || is_category() || is_tag() ) {
				$term = get_queried_object();
				$d    = term_description( $term );
				if ( '' === trim( wp_strip_all_tags( $d ) ) ) {
					$d = self::description_template( [ 'tax_' . $term->taxonomy, 'tax' ], [ '%term%' => $term->name ] );
				}
			} elseif ( is_post_type_archive() ) {
				$obj = get_post_type_object( self::archive_post_type() );
				$d   = $obj ? (string) $obj->description : '';
				if ( '' === trim( $d ) ) {
					$d = self::description_template( [ 'archive_' . self::archive_post_type(), 'archive' ], [ '%archive%' => post_type_archive_title( '', false ) ] );
				}
			}
		}

		$d   = self::plain( (string) $d );
		$max = (int) Config::get( 'meta.description_max', 300 );
		if ( $max > 0 && mb_strlen( $d ) > $max ) {
			$cut = mb_substr( $d, 0, $max - 1 );
			$sp  = mb_strrpos( $cut, ' ' );
			$d   = rtrim( false !== $sp && $sp > $max * 0.6 ? mb_substr( $cut, 0, $sp ) : $cut, ' ,.;:—–-' ) . '…';
		}

		self::$cache['description'] = (string) apply_filters( 'sky_meta_description', $d );
		return self::$cache['description'];
	}

	/**
	 * Канонический URL текущей страницы или '' (404, поиск).
	 */
	public static function canonical(): string {
		if ( isset( self::$cache['canonical'] ) ) {
			return self::$cache['canonical'];
		}

		$url = '';
		if ( is_404() || is_search() ) {
			$url = '';
		} elseif ( is_singular() ) {
			$url = (string) wp_get_canonical_url();
		} elseif ( is_front_page() ) {
			$url = home_url( '/' );
		} elseif ( is_home() ) {
			$page = (int) get_option( 'page_for_posts' );
			$url  = $page ? (string) get_permalink( $page ) : home_url( '/' );
		} elseif ( is_post_type_archive() ) {
			$url = (string) get_post_type_archive_link( self::archive_post_type() );
		} elseif ( is_tax() || is_category() || is_tag() ) {
			$link = get_term_link( get_queried_object() );
			$url  = is_wp_error( $link ) ? '' : $link;
		} elseif ( is_author() ) {
			$url = get_author_posts_url( (int) get_queried_object_id() );
		}

		$paged = self::paged();
		if ( $url && $paged > 1 && ! is_singular() ) {
			$url = self::paged_url( $url, $paged );
		}

		self::$cache['canonical'] = (string) apply_filters( 'sky_canonical_url', $url );
		return self::$cache['canonical'];
	}

	/**
	 * URL текущего запроса без мусорных параметров — для @id в Schema.
	 */
	public static function current_url(): string {
		$canonical = self::canonical();
		if ( '' !== $canonical ) {
			return $canonical;
		}
		if ( is_search() ) {
			return add_query_arg( 's', rawurlencode( get_search_query( false ) ), home_url( '/' ) );
		}
		global $wp;
		$path = isset( $wp->request ) ? (string) $wp->request : '';
		return home_url( '' === $path ? '/' : user_trailingslashit( $path ) );
	}

	/**
	 * Картинка для og:image: миниатюра → opengraph.default_image → логотип → иконка сайта.
	 *
	 * @return array{url: string, width: int, height: int}|null
	 */
	public static function image(): ?array {
		if ( array_key_exists( 'image', self::$cache ) ) {
			return self::$cache['image'];
		}

		$candidates = [];
		$id         = self::object_id();
		if ( $id && has_post_thumbnail( $id ) ) {
			$candidates[] = (int) get_post_thumbnail_id( $id );
		}
		$candidates[] = Config::get( 'opengraph.default_image', '' );
		$candidates[] = Organization::get( 'logo' );
		$candidates[] = (int) get_theme_mod( 'custom_logo' );
		$candidates[] = (int) get_option( 'site_icon' );

		$image = null;
		foreach ( $candidates as $candidate ) {
			$image = self::image_data( $candidate );
			if ( $image ) {
				break;
			}
		}

		self::$cache['image'] = $image;
		return $image;
	}

	/**
	 * Номер страницы списка (или страницы разбитой записи).
	 */
	public static function paged(): int {
		return max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	}

	/**
	 * URL N-й страницы списка.
	 *
	 * @param string $url   URL первой страницы.
	 * @param int    $paged Номер.
	 */
	public static function paged_url( string $url, int $paged ): string {
		global $wp_rewrite;
		if ( ! $wp_rewrite || ! $wp_rewrite->using_permalinks() ) {
			return add_query_arg( 'paged', $paged, $url );
		}
		return user_trailingslashit( trailingslashit( $url ) . $wp_rewrite->pagination_base . '/' . $paged, 'paged' );
	}

	/**
	 * ID записи, к которой относятся SEO-поля: запись, статическая главная или страница блога.
	 */
	public static function object_id(): int {
		if ( is_home() && ! is_front_page() ) {
			return (int) get_option( 'page_for_posts' );
		}
		return is_singular() ? (int) get_queried_object_id() : 0;
	}

	/**
	 * Тип записи архива (query var может быть массивом).
	 */
	public static function archive_post_type(): string {
		$pt = get_query_var( 'post_type' );
		return (string) ( is_array( $pt ) ? reset( $pt ) : $pt );
	}

	/**
	 * Текст без тегов и HTML-сущностей, с нормализованными пробелами.
	 *
	 * @param string $text Исходный текст.
	 */
	public static function plain( string $text ): string {
		$text = html_entity_decode( wp_strip_all_tags( $text ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		return trim( (string) preg_replace( '/\s+/u', ' ', $text ) );
	}

	/**
	 * Начало текста записи для description, когда нет отрывка.
	 * Без the_content: шорткоды и компоненты в <head> не выполняются. Заголовки и пункты списков
	 * отделяются точкой, иначе после удаления тегов «Когда обращаться» сливается со списком.
	 *
	 * @param int $id ID записи.
	 */
	private static function content_summary( int $id ): string {
		$html = strip_shortcodes( excerpt_remove_blocks( (string) get_post_field( 'post_content', $id ) ) );
		// \x01 — вставленная точка: после неё первая буква становится заглавной.
		$html = (string) preg_replace( '#</(?:h[1-6]|li|dt|dd|th|td)>#i', "\x01 $0", $html );
		$html = (string) preg_replace( '#</(?:p|div|ul|ol|blockquote)>|<br\s*/?>#i', ' $0', $html );
		$text = self::plain( $html );
		// «иск;» + точка → «иск;», в конце текста точка не дублируется.
		$text = (string) preg_replace( '/([.!?;:…])\s*\x01/u', '$1', $text );
		$text = (string) preg_replace_callback( '/\x01\s*(\p{Ll})/u', static fn( $m ) => '. ' . mb_strtoupper( $m[1] ), $text );
		$text = str_replace( "\x01", '.', $text );
		$text = (string) preg_replace( '/\s+([.,;:!?])/u', '$1', $text );
		return mb_substr( $text, 0, 1000 );
	}

	/**
	 * Шаблон description из meta.description_templates: первый непустой из ключей.
	 *
	 * @param string[] $keys  Например [ 'tax_service_cat', 'tax' ].
	 * @param array    $extra Токены.
	 */
	private static function description_template( array $keys, array $extra ): string {
		foreach ( $keys as $key ) {
			$tpl = (string) Config::get( 'meta.description_templates.' . $key, '' );
			if ( '' !== $tpl ) {
				return self::render( $tpl, $extra );
			}
		}
		return '';
	}

	/**
	 * Данные картинки по ID вложения или URL.
	 *
	 * @param mixed $candidate ID или URL.
	 * @return array{url: string, width: int, height: int}|null
	 */
	private static function image_data( $candidate ): ?array {
		if ( is_numeric( $candidate ) && (int) $candidate > 0 ) {
			$src = wp_get_attachment_image_src( (int) $candidate, 'full' );
			return $src ? [
				'url'    => (string) $src[0],
				'width'  => (int) $src[1],
				'height' => (int) $src[2],
			] : null;
		}
		if ( is_string( $candidate ) && str_starts_with( $candidate, 'http' ) ) {
			return [
				'url'    => $candidate,
				'width'  => 0,
				'height' => 0,
			];
		}
		return null;
	}
}
