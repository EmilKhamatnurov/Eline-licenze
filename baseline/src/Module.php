<?php
/**
 * Интерфейс модуля.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline;

defined( 'ABSPATH' ) || exit;

/**
 * Модуль вешает свои хуки в register() и больше ничего не делает при загрузке.
 */
interface Module {

	/**
	 * Регистрация хуков.
	 */
	public function register(): void;
}
