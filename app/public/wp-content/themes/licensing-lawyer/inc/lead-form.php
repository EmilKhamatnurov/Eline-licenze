<?php
/**
 * Форма заявки: проверка полей и AJAX поверх модуля forms baseline.
 *
 * Конвейер заявки остаётся в baseline (Sky\Baseline\Modules\Forms::handle): nonce, honeypot,
 * time-trap, лимит по IP, согласие, сохранение в «Заявки», письмо, Telegram, хук sky_lead_saved.
 * Тема перед ним (приоритет 5):
 *   - проверяет каждое поле и возвращает ошибки по полям;
 *   - приводит телефон к +7 (XXX) XXX-XX-XX;
 *   - для компактной формы без имени подставляет «Не указано» (baseline требует имя);
 *   - при AJAX (sky_ajax=1) превращает редирект baseline в JSON.
 * Без JS форма работает обычным POST: редирект на ?lead=<статус>&lead_form=<id>#<id>.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_post_nopriv_sky_lead', 'sky_starter_lead_prehandle', 5 );
add_action( 'admin_post_sky_lead', 'sky_starter_lead_prehandle', 5 );

/**
 * Тексты ошибок и состояний — общие для PHP и JS (передаются в lead-form.js).
 */
function sky_starter_lead_messages(): array {
	$base = function_exists( 'sky_config' ) ? (array) sky_config( 'forms.messages', [] ) : [];
	return array_merge(
		[
			'ok'      => 'Заявка отправлена. Перезвоним в рабочее время.',
			'error'   => 'Не удалось отправить. Проверьте имя и телефон.',
			'consent' => 'Нужно согласие на обработку персональных данных.',
			'expired' => 'Страница устарела. Обновите её и отправьте заявку ещё раз.',
			'limit'   => 'Слишком много заявок подряд. Попробуйте позже или позвоните нам.',
		],
		$base,
		[
			'fields'      => 'Проверьте поля формы.',
			'name'        => 'Укажите имя.',
			'phone_empty' => 'Укажите телефон.',
			'phone'       => 'Введите номер полностью: +7 и 10 цифр.',
			'network'     => 'Нет связи с сервером. Проверьте интернет и отправьте ещё раз или позвоните нам.',
			'server'      => 'Не удалось отправить заявку. Попробуйте ещё раз или позвоните нам.',
			'sending'     => 'Отправляем…',
		]
	);
}

/**
 * 10 цифр российского номера без кода страны или пустая строка.
 * Принимает «8 912…», «+7 912…», «912…» в любом оформлении.
 *
 * @param string $phone Телефон как ввёл посетитель.
 */
function sky_starter_phone_national( string $phone ): string {
	$digits = (string) preg_replace( '/\D+/', '', $phone );
	if ( 11 === strlen( $digits ) && in_array( $digits[0], [ '7', '8' ], true ) ) {
		$digits = substr( $digits, 1 );
	}
	return 10 === strlen( $digits ) ? $digits : '';
}

/**
 * +7 (912) 345-67-89.
 *
 * @param string $national 10 цифр.
 */
function sky_starter_phone_format( string $national ): string {
	return sprintf( '+7 (%s) %s-%s-%s', substr( $national, 0, 3 ), substr( $national, 3, 3 ), substr( $national, 6, 2 ), substr( $national, 8, 2 ) );
}

/**
 * Ошибки по полям: [ 'name' => '…', 'phone' => '…', 'consent' => '…' ].
 *
 * @param array  $data    name, phone, consent.
 * @param string $variant full | compact (в компактной форме имени нет).
 */
function sky_starter_lead_validate( array $data, string $variant ): array {
	$msgs   = sky_starter_lead_messages();
	$errors = [];

	if ( 'compact' !== $variant && mb_strlen( trim( (string) ( $data['name'] ?? '' ) ) ) < 2 ) {
		$errors['name'] = $msgs['name'];
	}

	$phone = trim( (string) ( $data['phone'] ?? '' ) );
	if ( '' === preg_replace( '/\D+/', '', $phone ) ) {
		$errors['phone'] = $msgs['phone_empty'];
	} elseif ( '' === sky_starter_phone_national( $phone ) ) {
		$errors['phone'] = $msgs['phone'];
	}

	if ( empty( $data['consent'] ) ) {
		$errors['consent'] = $msgs['consent'];
	}

	return $errors;
}

/**
 * Перед обработчиком baseline: проверка полей, нормализация, перехват редиректа для AJAX.
 */
function sky_starter_lead_prehandle(): void {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce проверяет обработчик baseline сразу после этой функции; здесь только проверка формата полей.
	$ajax    = ! empty( $_POST['sky_ajax'] );
	$anchor  = sanitize_html_class( wp_unslash( $_POST['anchor'] ?? 'form' ) );
	$variant = 'compact' === sanitize_key( wp_unslash( $_POST['form_variant'] ?? '' ) ) ? 'compact' : 'full';

	$filter = static fn( $location ) => sky_starter_lead_redirect( (string) $location, $ajax, $anchor );
	add_filter( 'wp_redirect', $filter, 1 );

	// Бот (заполнен honeypot): baseline сам ответит «успехом» и ничего не сохранит.
	if ( ! empty( $_POST['sky_hp'] ) ) {
		return;
	}

	$data = [
		'name'    => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
		'phone'   => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
		'consent' => ! empty( $_POST['consent'] ),
	];
	// phpcs:enable

	$errors = sky_starter_lead_validate( $data, $variant );
	if ( $errors ) {
		if ( $ajax ) {
			wp_send_json(
				[
					'status'  => 'error',
					'message' => sky_starter_lead_messages()['fields'],
					'errors'  => $errors,
				]
			);
		}
		$referer = wp_get_referer();
		$referer = $referer ? $referer : home_url( '/' );
		$url     = add_query_arg(
			[
				'lead'        => 'error',
				'lead_form'   => $anchor,
				'lead_fields' => implode( ',', array_keys( $errors ) ),
			],
			remove_query_arg( [ 'lead', 'lead_form', 'lead_fields' ], $referer )
		);
		remove_filter( 'wp_redirect', $filter, 1 );
		wp_safe_redirect( $url . '#' . $anchor );
		exit;
	}

	// Тема из выпадающего списка (lead-form, аргумент topics) — в начало комментария: baseline её не знает.
	$topic = sanitize_text_field( wp_unslash( $_POST['topic'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce проверяет baseline.
	if ( '' !== $topic ) {
		$message          = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce проверяет baseline.
		$_POST['message'] = 'Тема: ' . mb_substr( $topic, 0, 100 ) . ( '' !== $message ? "\n" . $message : '' );
	}

	// Дальше читает baseline: телефон в едином формате, имя для компактной формы.
	$_POST['phone'] = sky_starter_phone_format( sky_starter_phone_national( $data['phone'] ) );
	if ( 'compact' === $variant && '' === $data['name'] ) {
		$_POST['name'] = 'Не указано';
	}
}

/**
 * Редирект baseline (?lead=<статус>#<якорь>): для AJAX — JSON, для обычной отправки — + lead_form.
 *
 * @param string $location Адрес редиректа.
 * @param bool   $ajax     Отправка из lead-form.js.
 * @param string $anchor   id формы.
 */
function sky_starter_lead_redirect( string $location, bool $ajax, string $anchor ): string {
	[ $url, $hash ] = array_pad( explode( '#', $location, 2 ), 2, '' );
	$query          = [];
	wp_parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
	$status = sanitize_key( (string) ( $query['lead'] ?? '' ) );

	if ( $ajax ) {
		$msgs = sky_starter_lead_messages();
		wp_send_json(
			[
				'status'  => '' !== $status ? $status : 'error',
				'message' => (string) ( $msgs[ $status ] ?? $msgs['error'] ),
				'errors'  => 'consent' === $status ? [ 'consent' => $msgs['consent'] ] : [],
			]
		);
	}

	// В referer могли остаться поля прошлой ошибки — заменить актуальными.
	$url = add_query_arg(
		[
			'lead_form'   => $anchor,
			'lead_fields' => 'consent' === $status ? 'consent' : false,
		],
		$url
	);
	return '' !== $hash ? $url . '#' . $hash : $url;
}

/**
 * Состояние формы после обычной (не AJAX) отправки — только для формы с этим id.
 *
 * @param string $id id формы.
 * @return array{status: string, fields: string[]}
 */
function sky_starter_lead_state( string $id ): array {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- только выбор сообщения для показа.
	$form = sanitize_html_class( wp_unslash( $_GET['lead_form'] ?? '' ) );
	if ( '' === $form || $form !== $id ) {
		return [
			'status' => '',
			'fields' => [],
		];
	}
	$fields = array_intersect(
		explode( ',', sanitize_text_field( wp_unslash( $_GET['lead_fields'] ?? '' ) ) ),
		[ 'name', 'phone', 'consent' ]
	);
	$state  = [
		'status' => sanitize_key( wp_unslash( $_GET['lead'] ?? '' ) ),
		'fields' => array_values( $fields ),
	];
	// phpcs:enable
	return $state;
}
