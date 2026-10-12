<?php
/**
 * Цепочка хлебных крошек.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline\Seo;

use Sky\Baseline\Config;

defined( 'ABSPATH' ) || exit;

/**
 * Крошки: [ [ 'name' => …, 'url' => … ], …, [ 'name' => текущая ] ].
 */
final class Breadcrumbs {

	/**
	 * Кэш на запрос.
	 *
	 * @var array|null
	 */
	private static ?array $items = null;

	/**
	 * Сброс кэша — для тестов.
	 */
	public static function reset(): void {
		self::$items = null;
	}

	/**
	 * Цепочка для текущего запроса. Последний элемент — без url.
	 */
	public static function items(): array {
		if ( null !== self::$items ) {
			return self::$items;
		}
		if ( ! did_action( 'wp' ) ) {
			return [];
		}

		$items = [];
		if ( ! is_front_page() ) {
			$items[] = [
				'name' => (string) Config::get( 'breadcrumbs.home_label', 'Главная' ),
				'url'  => home_url( '/' ),
			];

			if ( is_home() ) {
				$page    = (int) get_option( 'page_for_posts' );
				$items[] = [ 'name' => $page ? get_the_title( $page ) : 'Блог' ];
			} elseif ( is_singular() ) {
				self::singular( $items, (int) get_queried_object_id() );
			} elseif ( is_post_type_archive() ) {
				$items[] = [ 'name' => post_type_archive_title( '', false ) ];
			} elseif ( is_tax() || is_category() || is_tag() ) {
				$term = get_queried_object();
				$pt   = self::tax_post_type( $term->taxonomy );
				if ( $pt ) {
					self::post_type_parent( $items, $pt );
				}
				self::term_chain( $items, $term, false );
			} elseif ( is_author() ) {
				$items[] = [ 'name' => get_the_author_meta( 'display_name', (int) get_queried_object_id() ) ];
			} elseif ( is_date() ) {
				$items[] = [ 'name' => wp_strip_all_tags( get_the_archive_title() ) ];
			} elseif ( is_search() ) {
				$items[] = [ 'name' => sprintf( (string) Config::get( 'breadcrumbs.search_label', 'Поиск: %s' ), get_search_query( false ) ) ];
			} elseif ( is_404() ) {
				$items[] = [ 'name' => (string) Config::get( 'breadcrumbs.404_label', 'Страница не найдена' ) ];
			}
		}

		foreach ( $items as &$item ) {
			$item['name'] = Context::plain( (string) $item['name'] );
		}
		unset( $item );

		self::$items = (array) apply_filters( 'sky_breadcrumbs_items', $items );
		return self::$items;
	}

	/**
	 * К какому типу записи привязана таксономия.
	 *
	 * @param string $taxonomy Таксономия.
	 */
	public static function tax_post_type( string $taxonomy ): string {
		$tax = get_taxonomy( $taxonomy );
		return $tax && ! empty( $tax->object_type ) ? (string) $tax->object_type[0] : '';
	}

	/**
	 * Хаб раздела: страница из breadcrumbs.hubs / cpt.post_types.*.hub, иначе архив типа.
	 *
	 * @param array  $items     Цепочка.
	 * @param string $post_type Тип записи.
	 */
	private static function post_type_parent( array &$items, string $post_type ): void {
		$hub = Config::get( 'breadcrumbs.hubs.' . $post_type ) ?? Config::get( 'cpt.post_types.' . $post_type . '.hub' );
		if ( $hub ) {
			$page = is_numeric( $hub ) ? get_post( (int) $hub ) : get_page_by_path( trim( (string) $hub, '/' ) );
			if ( $page ) {
				foreach ( array_reverse( get_post_ancestors( $page ) ) as $anc_id ) {
					$items[] = [
						'name' => get_the_title( $anc_id ),
						'url'  => get_permalink( $anc_id ),
					];
				}
				$items[] = [
					'name' => get_the_title( $page ),
					'url'  => get_permalink( $page ),
				];
				return;
			}
		}

		if ( 'post' === $post_type ) {
			$blog = (int) get_option( 'page_for_posts' );
			if ( $blog && (int) get_option( 'page_on_front' ) !== $blog ) {
				$items[] = [
					'name' => get_the_title( $blog ),
					'url'  => get_permalink( $blog ),
				];
			}
			return;
		}

		$obj = get_post_type_object( $post_type );
		if ( $obj && $obj->has_archive ) {
			$items[] = [
				'name' => $obj->labels->name,
				'url'  => get_post_type_archive_link( $post_type ),
			];
		}
	}

	/**
	 * Цепочка для записи любого типа.
	 *
	 * @param array $items Цепочка.
	 * @param int   $id    ID записи.
	 */
	private static function singular( array &$items, int $id ): void {
		$post_type = (string) get_post_type( $id );

		if ( 'page' !== $post_type && 'attachment' !== $post_type ) {
			self::post_type_parent( $items, $post_type );
		}

		$configured = (string) Config::get( 'breadcrumbs.primary_taxonomy.' . $post_type, '' );
		$tax        = (string) apply_filters( 'sky_primary_taxonomy', $configured, $post_type );
		if ( $tax ) {
			$terms = get_the_terms( $id, $tax );
			if ( $terms && ! is_wp_error( $terms ) ) {
				self::term_chain( $items, reset( $terms ), true );
			}
		}

		if ( is_post_type_hierarchical( $post_type ) ) {
			foreach ( array_reverse( get_post_ancestors( $id ) ) as $anc_id ) {
				$items[] = [
					'name' => get_the_title( $anc_id ),
					'url'  => get_permalink( $anc_id ),
				];
			}
		}

		$items[] = [ 'name' => get_the_title( $id ) ];
	}

	/**
	 * Термин с родителями.
	 *
	 * @param array    $items     Цепочка.
	 * @param \WP_Term $term      Термин.
	 * @param bool     $link_last Ссылка на сам термин (не последний элемент).
	 */
	private static function term_chain( array &$items, \WP_Term $term, bool $link_last ): void {
		foreach ( array_reverse( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) ) as $anc_id ) {
			$anc = get_term( $anc_id, $term->taxonomy );
			if ( $anc instanceof \WP_Term ) {
				$link    = get_term_link( $anc );
				$items[] = [
					'name' => $anc->name,
					'url'  => is_wp_error( $link ) ? '' : $link,
				];
			}
		}
		$item = [ 'name' => $term->name ];
		if ( $link_last ) {
			$link        = get_term_link( $term );
			$item['url'] = is_wp_error( $link ) ? '' : $link;
		}
		$items[] = $item;
	}
}
