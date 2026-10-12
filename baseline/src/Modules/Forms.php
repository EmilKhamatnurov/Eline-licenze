<?php
/**
 * Модуль forms: форма заявки, сохранение, письмо, Telegram.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline\Modules;

use Sky\Baseline\Config;
use Sky\Baseline\Module;

defined( 'ABSPATH' ) || exit;

/**
 * Заявка сначала сохраняется в CPT sky_lead, потом уходит письмом и в Telegram:
 * сбой почты или Telegram не теряет заявку.
 */
final class Forms implements Module {

	public const POST_TYPE = 'sky_lead';

	/**
	 * Счётчик форм на странице — для уникальных id полей.
	 *
	 * @var int
	 */
	private static int $count = 0;

	/**
	 * Хуки.
	 */
	public function register(): void {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'admin_post_nopriv_sky_lead', [ $this, 'handle' ] );
		add_action( 'admin_post_sky_lead', [ $this, 'handle' ] );
		add_filter( 'wp_robots', [ $this, 'robots' ], 20 );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', [ $this, 'columns' ] );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', [ $this, 'column' ], 10, 2 );
		add_shortcode( 'sky_lead_form', [ $this, 'shortcode' ] );
	}

	/**
	 * [sky_lead_form title="…" source="…" button="…"] — форма в контенте записи.
	 *
	 * @param array|string $atts Атрибуты.
	 */
	public function shortcode( $atts ): string {
		$atts = shortcode_atts(
			[
				'title'  => 'Оставить заявку',
				'source' => '',
				'id'     => 'form',
				'button' => 'Отправить',
			],
			(array) $atts,
			'sky_lead_form'
		);
		return self::render( $atts );
	}

	/**
	 * HTML формы.
	 *
	 * @param array $args title, source, id, button.
	 */
	public static function render( array $args = [] ): string {
		$args = wp_parse_args(
			$args,
			[
				'title'  => 'Оставить заявку',
				'source' => '',
				'id'     => 'form',
				'button' => 'Отправить',
			]
		);

		++self::$count;
		$n      = self::$count;
		$id     = sanitize_html_class( $args['id'] );
		$policy = get_privacy_policy_url();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- только выбор сообщения.
		$status = isset( $_GET['lead'] ) ? sanitize_key( wp_unslash( $_GET['lead'] ) ) : '';
		$msgs   = (array) Config::get( 'forms.messages', [] );
		$source = '' !== $args['source'] ? $args['source'] : wp_strip_all_tags( (string) get_the_title() );

		ob_start();
		?>
		<div class="lead" id="<?php echo esc_attr( $id ); ?>">
			<?php if ( $args['title'] ) : ?>
				<h2 class="lead__title"><?php echo esc_html( $args['title'] ); ?></h2>
			<?php endif; ?>

			<?php if ( 1 === $n && isset( $msgs[ $status ] ) ) : ?>
				<p class="lead__msg lead__msg--<?php echo 'ok' === $status ? 'ok' : 'err'; ?>" role="status"><?php echo esc_html( $msgs[ $status ] ); ?></p>
			<?php endif; ?>

			<form class="lead-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="sky_lead">
				<input type="hidden" name="source" value="<?php echo esc_attr( $source ); ?>">
				<input type="hidden" name="anchor" value="<?php echo esc_attr( $id ); ?>">
				<input type="hidden" name="sky_ts" value="<?php echo esc_attr( (string) time() ); ?>">
				<?php
				if ( Config::get( 'forms.nonce', true ) ) {
					wp_nonce_field( 'sky_lead', 'sky_lead_nonce' );
				}
				?>

				<div class="lead-form__hp" aria-hidden="true">
					<label>Не заполняйте это поле
						<input type="text" name="sky_hp" tabindex="-1" autocomplete="off">
					</label>
				</div>

				<p class="lead-form__row">
					<label for="lf-name-<?php echo (int) $n; ?>">Имя</label>
					<input id="lf-name-<?php echo (int) $n; ?>" type="text" name="name" required maxlength="100" autocomplete="name">
				</p>

				<p class="lead-form__row">
					<label for="lf-phone-<?php echo (int) $n; ?>">Телефон</label>
					<input id="lf-phone-<?php echo (int) $n; ?>" type="tel" name="phone" required maxlength="30" autocomplete="tel" inputmode="tel">
				</p>

				<p class="lead-form__row">
					<label for="lf-msg-<?php echo (int) $n; ?>">Комментарий</label>
					<textarea id="lf-msg-<?php echo (int) $n; ?>" name="message" rows="3" maxlength="1500"></textarea>
				</p>

				<p class="lead-form__consent">
					<label>
						<input type="checkbox" name="consent" value="1" required>
						Я согласен на обработку персональных данных<?php if ( $policy ) : ?>
						и принимаю условия <a href="<?php echo esc_url( $policy ); ?>" target="_blank" rel="noopener">политики конфиденциальности</a><?php endif; ?>
					</label>
				</p>

				<button type="submit" class="wp-element-button lead-form__submit"><?php echo esc_html( $args['button'] ); ?></button>
			</form>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Обработчик admin-post.
	 */
	public function handle(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce проверяется ниже, если включён.
		$referer = wp_get_referer();
		$referer = $referer ? $referer : home_url( '/' );
		$anchor  = sanitize_html_class( wp_unslash( $_POST['anchor'] ?? 'form' ) );
		$back    = static function ( string $status ) use ( $referer, $anchor ): void {
			wp_safe_redirect( add_query_arg( 'lead', $status, remove_query_arg( 'lead', $referer ) ) . '#' . $anchor );
			exit;
		};

		if ( Config::get( 'forms.nonce', true ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['sky_lead_nonce'] ?? '' ) );
			if ( ! wp_verify_nonce( $nonce, 'sky_lead' ) ) {
				$back( 'expired' );
			}
		}

		// Бот: заполнен honeypot или форма отправлена быстрее человека. Показываем успех, ничего не делаем.
		$elapsed = time() - absint( $_POST['sky_ts'] ?? 0 );
		if ( ! empty( $_POST['sky_hp'] ) || $elapsed < (int) Config::get( 'forms.min_fill_seconds', 3 ) ) {
			$back( 'ok' );
		}

		if ( empty( $_POST['consent'] ) ) {
			$back( 'consent' );
		}

		$data = [
			'name'    => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
			'phone'   => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
			'message' => sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) ),
			'source'  => sanitize_text_field( wp_unslash( $_POST['source'] ?? '' ) ),
			'url'     => esc_url_raw( $referer ),
			'ip'      => self::ip(),
			'time'    => current_time( 'mysql' ),
		];
		// phpcs:enable

		if ( '' === $data['name'] || strlen( (string) preg_replace( '/\D/', '', $data['phone'] ) ) < 10 ) {
			$back( 'error' );
		}

		if ( self::rate_limited( $data['ip'] ) ) {
			$back( 'limit' );
		}

		$lead_id = self::save( $data );
		self::send_mail( $data );
		self::send_telegram( $data, $lead_id );

		/**
		 * Заявка сохранена — точка расширения для CRM.
		 *
		 * @param int   $lead_id ID записи sky_lead (0, если сохранить не удалось).
		 * @param array $data    Поля заявки.
		 */
		do_action( 'sky_lead_saved', $lead_id, $data );

		$back( 'ok' );
	}

	/**
	 * Страницы с ?lead= — не индексировать.
	 *
	 * @param array $robots Директивы.
	 */
	public function robots( array $robots ): array {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- только проверка наличия параметра.
		return isset( $_GET['lead'] ) ? Robots::noindex( $robots ) : $robots;
	}

	/**
	 * CPT заявок: только в админке, без URL на сайте.
	 */
	public function register_post_type(): void {
		register_post_type(
			self::POST_TYPE,
			[
				'labels'              => [
					'name'          => 'Заявки',
					'singular_name' => 'Заявка',
					'menu_name'     => 'Заявки',
				],
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_icon'           => 'dashicons-email-alt',
				'menu_position'       => 25,
				'supports'            => [ 'title', 'editor' ],
				'capability_type'     => 'post',
				'exclude_from_search' => true,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'show_in_rest'        => false,
				'can_export'          => false, // Телефоны и IP не должны попадать в Инструменты → Экспорт.
			]
		);
	}

	/**
	 * Колонки в списке заявок.
	 *
	 * @param array $cols Колонки.
	 */
	public function columns( array $cols ): array {
		return [
			'cb'          => $cols['cb'] ?? '',
			'title'       => 'Контакт',
			'lead_source' => 'Страница',
			'lead_ip'     => 'IP',
			'date'        => 'Дата',
		];
	}

	/**
	 * Значение колонки.
	 *
	 * @param string $col     Колонка.
	 * @param int    $post_id ID заявки.
	 */
	public function column( string $col, int $post_id ): void {
		if ( 'lead_source' === $col || 'lead_ip' === $col ) {
			echo esc_html( (string) get_post_meta( $post_id, $col, true ) );
		}
	}

	/**
	 * Сохранение заявки.
	 *
	 * @param array $data Поля.
	 */
	private static function save( array $data ): int {
		$id = wp_insert_post(
			[
				'post_type'    => self::POST_TYPE,
				'post_status'  => 'publish',
				'post_title'   => sprintf( '%s — %s', $data['name'], $data['phone'] ),
				'post_content' => $data['message'],
				'meta_input'   => [
					'lead_phone'   => $data['phone'],
					'lead_source'  => $data['source'],
					'lead_url'     => $data['url'],
					'lead_ip'      => $data['ip'],
					'lead_consent' => $data['time'],
				],
			],
			true
		);
		if ( is_wp_error( $id ) ) {
			error_log( '[sky] Заявка не сохранена: ' . $id->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return 0;
		}
		return (int) $id;
	}

	/**
	 * Текст заявки для письма и Telegram.
	 *
	 * @param array $data Поля.
	 */
	private static function text( array $data ): string {
		return sprintf(
			"Имя: %s\nТелефон: %s\nКомментарий: %s\n\nСтраница: %s\n%s\nIP: %s\nВремя: %s\nСогласие на обработку ПДн: получено",
			$data['name'],
			$data['phone'],
			'' !== $data['message'] ? $data['message'] : '—',
			$data['source'],
			$data['url'],
			$data['ip'],
			$data['time']
		);
	}

	/**
	 * Письмо.
	 *
	 * @param array $data Поля.
	 */
	private static function send_mail( array $data ): void {
		$to      = (string) Config::get( 'forms.email_to', '' );
		$to      = '' !== $to ? $to : (string) get_option( 'admin_email' );
		$subject = strtr( (string) Config::get( 'forms.subject', 'Заявка с сайта: %site%' ), [ '%site%' => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) ] );
		wp_mail( $to, $subject, self::text( $data ), [ 'Content-Type: text/plain; charset=UTF-8' ] );
	}

	/**
	 * Telegram. Ошибка пишется в лог и мету заявки и не мешает ответу пользователю.
	 * Токен лучше держать в wp-config.php (SKY_TG_TOKEN), а не в конфиге в git.
	 *
	 * @param array $data    Поля.
	 * @param int   $lead_id ID заявки.
	 */
	private static function send_telegram( array $data, int $lead_id ): void {
		if ( ! Config::get( 'forms.telegram.enabled', false ) ) {
			return;
		}
		$token = defined( 'SKY_TG_TOKEN' ) ? (string) SKY_TG_TOKEN : (string) Config::get( 'forms.telegram.token', '' );
		$chat  = defined( 'SKY_TG_CHAT_ID' ) ? (string) SKY_TG_CHAT_ID : (string) Config::get( 'forms.telegram.chat_id', '' );
		if ( '' === $token || '' === $chat ) {
			return;
		}

		$response = wp_remote_post(
			'https://api.telegram.org/bot' . rawurlencode( $token ) . '/sendMessage',
			[
				'timeout' => max( 1, (int) Config::get( 'forms.telegram.timeout', 5 ) ),
				'body'    => [
					'chat_id'                  => $chat,
					'text'                     => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) . "\n\n" . self::text( $data ),
					'disable_web_page_preview' => 'true',
				],
			]
		);

		$error = '';
		if ( is_wp_error( $response ) ) {
			$error = $response->get_error_message();
		} elseif ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			$error = 'HTTP ' . wp_remote_retrieve_response_code( $response ) . ': ' . mb_substr( wp_remote_retrieve_body( $response ), 0, 300 );
		}
		if ( '' !== $error ) {
			// Сообщение об ошибке не содержит токен: он только в URL запроса.
			error_log( '[sky] Telegram: ' . $error ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			if ( $lead_id ) {
				update_post_meta( $lead_id, 'lead_telegram_error', $error );
			}
		}
	}

	/**
	 * Не больше forms.rate_limit заявок с одного IP за 10 минут.
	 *
	 * @param string $ip IP.
	 */
	private static function rate_limited( string $ip ): bool {
		$limit = (int) Config::get( 'forms.rate_limit', 5 );
		if ( $limit <= 0 || '' === $ip ) {
			return false;
		}
		$key   = 'sky_lead_' . md5( $ip );
		$count = (int) get_transient( $key );
		if ( $count >= $limit ) {
			return true;
		}
		set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );
		return false;
	}

	/**
	 * IP посетителя. За прокси настройте REMOTE_ADDR на сервере, заголовкам не доверяем.
	 */
	private static function ip(): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
	}
}
