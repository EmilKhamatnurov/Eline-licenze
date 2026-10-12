<?php
/**
 * Модуль settings: контакты организации в Customizer.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline\Modules;

use Sky\Baseline\Config;
use Sky\Baseline\Module;
use Sky\Baseline\Organization;

defined( 'ABSPATH' ) || exit;

/**
 * Customizer → «Контакты организации». Пустое поле = значение из sky-config.php (видно как placeholder).
 */
final class Settings implements Module {

	/**
	 * Хуки.
	 */
	public function register(): void {
		add_action( 'customize_register', [ $this, 'customize' ] );
	}

	/**
	 * Секция и поля.
	 *
	 * @param \WP_Customize_Manager $wp_customize Менеджер.
	 */
	public function customize( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section(
			'sky_contacts',
			[
				'title'       => 'Контакты организации',
				'description' => 'Пустое поле — используется значение из sky-config.php (показано серым).',
				'priority'    => 30,
			]
		);

		foreach ( Organization::CUSTOMIZER_FIELDS as $key => $label ) {
			$setting = 'sky_' . $key;
			$wp_customize->add_setting(
				$setting,
				[
					'default'           => '',
					'sanitize_callback' => 'email' === $key ? 'sanitize_email' : 'sanitize_text_field',
				]
			);
			$wp_customize->add_control(
				$setting,
				[
					'label'       => $label,
					'section'     => 'sky_contacts',
					'type'        => 'email' === $key ? 'email' : 'text',
					'input_attrs' => [ 'placeholder' => (string) Config::get( 'organization.' . $key, '' ) ],
				]
			);
		}
	}
}
