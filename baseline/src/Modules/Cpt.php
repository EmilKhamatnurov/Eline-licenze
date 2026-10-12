<?php
/**
 * Модуль cpt: типы записей, таксономии и поля из конфига.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline\Modules;

use Sky\Baseline\Config;
use Sky\Baseline\FieldGroups;
use Sky\Baseline\Module;

defined( 'ABSPATH' ) || exit;

/**
 * Декларативная регистрация. Правила перезаписи сбрасываются сами при изменении конфига.
 */
final class Cpt implements Module {

	/**
	 * Хуки.
	 */
	public function register(): void {
		// Приоритет 0: ACF собирает группы полей на init:5 и должен уже видеть наши типы.
		add_action( 'init', [ $this, 'register_types' ], 0 );
		add_action( 'init', [ $this, 'maybe_flush' ], 99 );
		add_action( 'pre_get_posts', [ $this, 'archive_order' ] );
		foreach ( (array) Config::get( 'cpt.fields', [] ) as $box ) {
			FieldGroups::add( (array) $box );
		}
	}

	/**
	 * register_post_type / register_taxonomy.
	 */
	public function register_types(): void {
		// Таксономии раньше типов: правила /uslugi/razdel/<term>/ должны стоять выше
		// правил вложений типа /uslugi/<post>/<attachment>/, иначе термины уйдут в 404.
		foreach ( (array) Config::get( 'cpt.taxonomies', [] ) as $name => $def ) {
			$def = (array) $def;
			register_taxonomy( (string) $name, (array) ( $def['post_types'] ?? [] ), self::taxonomy_args( $def ) );
		}
		foreach ( (array) Config::get( 'cpt.post_types', [] ) as $name => $def ) {
			$def = (array) $def;
			register_post_type( (string) $name, self::post_type_args( $def ) );
		}
	}

	/**
	 * Сброс правил ЧПУ, если описание типов изменилось. Хэш хранится в autoload-опции.
	 */
	public function maybe_flush(): void {
		if ( wp_installing() ) {
			return;
		}
		$hash = md5( (string) wp_json_encode( [ Config::get( 'cpt.post_types', [] ), Config::get( 'cpt.taxonomies', [] ) ] ) );
		if ( get_option( 'sky_cpt_hash' ) !== $hash ) {
			flush_rewrite_rules( false );
			update_option( 'sky_cpt_hash', $hash, true );
		}
	}

	/**
	 * Порядок записей в архиве типа и в его таксономиях: cpt.post_types.<pt>.order.
	 * menu_order — по полю «Порядок» (затем новые выше), title — по алфавиту, иначе — по дате.
	 *
	 * @param \WP_Query $q Запрос.
	 */
	public function archive_order( \WP_Query $q ): void {
		if ( is_admin() || ! $q->is_main_query() || $q->get( 'orderby' ) ) {
			return;
		}
		$pt = '';
		if ( $q->is_post_type_archive() ) {
			$pt = $q->get( 'post_type' );
			$pt = (string) ( is_array( $pt ) ? reset( $pt ) : $pt );
		} elseif ( $q->is_tax() ) {
			foreach ( (array) Config::get( 'cpt.taxonomies', [] ) as $tax => $def ) {
				if ( $q->get( (string) $tax ) ) {
					$pt = (string) ( ( (array) ( $def['post_types'] ?? [] ) )[0] ?? '' );
					break;
				}
			}
		}
		$order = '' !== $pt ? (string) Config::get( 'cpt.post_types.' . $pt . '.order', '' ) : '';
		if ( 'menu_order' === $order ) {
			$q->set(
				'orderby',
				[
					'menu_order' => 'ASC',
					'date'       => 'DESC',
				]
			);
		} elseif ( 'title' === $order ) {
			$q->set( 'orderby', [ 'title' => 'ASC' ] );
		}
	}

	/**
	 * Аргументы типа записи.
	 *
	 * @param array $def Описание из конфига.
	 */
	private static function post_type_args( array $def ): array {
		$slug     = (string) ( $def['slug'] ?? '' );
		$archive  = $def['archive'] ?? true;
		$supports = (array) ( $def['supports'] ?? [ 'title', 'editor', 'thumbnail', 'excerpt' ] );
		// Поле «Порядок» в редакторе нужно, чтобы order => menu_order было чем управлять.
		if ( 'menu_order' === ( $def['order'] ?? '' ) && ! in_array( 'page-attributes', $supports, true ) ) {
			$supports[] = 'page-attributes';
		}
		$args = [
			'labels'        => self::labels( $def ),
			'public'        => true,
			'show_in_rest'  => true,
			'has_archive'   => $archive,
			'hierarchical'  => (bool) ( $def['hierarchical'] ?? false ),
			'menu_icon'     => $def['icon'] ?? 'dashicons-admin-post',
			'menu_position' => 20,
			'supports'      => $supports,
			'rewrite'       => '' !== $slug ? [
				'slug'       => $slug,
				'with_front' => false,
			] : true,
		];
		return array_replace( $args, (array) ( $def['args'] ?? [] ) );
	}

	/**
	 * Аргументы таксономии.
	 *
	 * @param array $def Описание из конфига.
	 */
	private static function taxonomy_args( array $def ): array {
		$slug = (string) ( $def['slug'] ?? '' );
		$args = [
			'labels'            => self::labels( $def ),
			'public'            => true,
			'show_in_rest'      => true,
			'hierarchical'      => (bool) ( $def['hierarchical'] ?? true ),
			'show_admin_column' => true,
			'rewrite'           => '' !== $slug ? [
				'slug'         => $slug,
				'with_front'   => false,
				'hierarchical' => (bool) ( $def['hierarchical'] ?? true ),
			] : true,
		];
		return array_replace( $args, (array) ( $def['args'] ?? [] ) );
	}

	/**
	 * Подписи из label/singular. Формулировки без рода — подходят к любому слову.
	 *
	 * @param array $def Описание.
	 */
	private static function labels( array $def ): array {
		$name     = (string) ( $def['label'] ?? 'Записи' );
		$singular = (string) ( $def['singular'] ?? $name );
		$labels   = [
			'name'               => $name,
			'singular_name'      => $singular,
			'menu_name'          => $name,
			'all_items'          => $name,
			'add_new'            => 'Добавить',
			'add_new_item'       => 'Добавить: ' . $singular,
			'edit_item'          => 'Редактировать: ' . $singular,
			'new_item'           => 'Новая запись',
			'view_item'          => 'Посмотреть',
			'search_items'       => 'Найти',
			'not_found'          => 'Ничего не найдено',
			'not_found_in_trash' => 'В корзине пусто',
		];
		return array_replace( $labels, (array) ( $def['labels'] ?? [] ) );
	}
}
