<?php
/**
 * Модуль meta: Title и meta description.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline\Modules;

use Sky\Baseline\Config;
use Sky\Baseline\FieldGroups;
use Sky\Baseline\Module;
use Sky\Baseline\Seo\Context;

defined( 'ABSPATH' ) || exit;

/**
 * Title по шаблонам из title.templates, description с фолбэками.
 */
final class Meta implements Module {

	/**
	 * Хуки.
	 */
	public function register(): void {
		add_filter( 'pre_get_document_title', [ $this, 'title' ], 20 );
		add_action( 'wp_head', [ $this, 'description' ], 2 );

		if ( Config::get( 'fields.seo_fields', true ) ) {
			FieldGroups::add(
				[
					'id'         => 'sky_seo',
					'title'      => 'SEO',
					'post_types' => '*',
					'priority'   => 'low',
					'fields'     => [
						[
							'id'   => 'seo_title',
							'name' => 'Title',
							'type' => 'text',
							'desc' => 'Пусто — Title по шаблону из sky-config.php.',
						],
						[
							'id'   => 'seo_description',
							'name' => 'Meta description',
							'type' => 'textarea',
							'rows' => 3,
							'desc' => 'Пусто — отрывок записи.',
						],
						[
							'id'   => 'seo_noindex',
							'name' => 'Закрыть от индексации (noindex)',
							'type' => 'checkbox',
						],
					],
				]
			);
		}
	}

	/**
	 * Ядро не экранирует результат pre_get_document_title — делаем это здесь.
	 */
	public function title(): string {
		return esc_html( Context::title() );
	}

	/**
	 * Вывод meta description.
	 */
	public function description(): void {
		$d = Context::description();
		if ( '' !== $d ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $d ) );
		}
	}
}
