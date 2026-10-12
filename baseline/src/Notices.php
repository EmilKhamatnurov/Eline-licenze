<?php
/**
 * Уведомления в админке.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline;

defined( 'ABSPATH' ) || exit;

/**
 * Очередь сообщений для admin_notices.
 */
final class Notices {

	/**
	 * Сообщения: [ [ текст, тип ], ... ].
	 *
	 * @var array
	 */
	private static array $queue = [];

	/**
	 * Хук вешается один раз.
	 *
	 * @var bool
	 */
	private static bool $hooked = false;

	/**
	 * Добавить сообщение.
	 *
	 * @param string $message Текст.
	 * @param string $type    error|warning|info.
	 */
	public static function add( string $message, string $type = 'error' ): void {
		self::$queue[] = [ $message, $type ];
		if ( ! self::$hooked ) {
			self::$hooked = true;
			add_action( 'admin_notices', [ self::class, 'render' ] );
		}
	}

	/**
	 * Вывод.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		foreach ( self::$queue as [ $message, $type ] ) {
			printf(
				'<div class="notice notice-%s"><p><strong>7sky baseline:</strong> %s</p></div>',
				esc_attr( $type ),
				esc_html( $message )
			);
		}
	}
}
