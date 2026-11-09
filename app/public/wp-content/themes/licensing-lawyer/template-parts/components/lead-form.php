<?php
/**
 * Компонент «Форма заявки» — один для hero, нижнего блока и внутренних страниц (DESIGN.md §5).
 *
 *   sky_component( 'lead-form', [ 'variant' => 'compact', 'id' => 'hero-form', 'dark' => true, 'button_class' => 'btn--gold' ] );
 *   sky_component( 'lead-form', [ 'title' => 'Обсудим вашу задачу' ] );
 *
 * Аргументы:
 *   variant      — full (имя, телефон, тема; карточка на --paper) | compact (телефон + кнопка в строку, для hero);
 *   id           — якорь и id формы, уникальный на странице (form, hero-form…);
 *   title, text  — заголовок и текст над полями (full);
 *   title_tag    — тег заголовка: h2 (по умолчанию), h3;
 *   button       — текст кнопки;
 *   button_class — модификатор кнопки: btn--gold (hero), btn--light…;
 *   topic        — поле «Что нужно (необязательно)»; по умолчанию есть в full, нет в compact;
 *   topic_value  — начальное значение этого поля (например, поисковый запрос каталога);
 *   topic_label  — подпись этого поля (по умолчанию «Что нужно (необязательно)»);
 *   topic_placeholder — подсказка в поле;
 *   message_type — input (строка, по умолчанию) | textarea;
 *   topics       — список тем для выпадающего списка «Тема обращения» (name="topic"); пусто — без списка.
 *                  Сервер добавит тему в начало комментария заявки (inc/lead-form.php);
 *   phone_label  — подпись поля телефона;
 *   source       — метка источника в заявке (по умолчанию заголовок страницы + место формы);
 *   dark         — форма на тёмном фоне (hero);
 *   class        — дополнительные классы обёртки.
 *
 * Отправка: обработчик baseline (admin-post.php?action=sky_lead), проверка полей и AJAX — inc/lead-form.php,
 * маска телефона и состояния — assets/js/lead-form.js. Без JS — обычный POST с теми же сообщениями.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'variant'           => 'full',
		'id'                => 'form',
		'title'             => '',
		'text'              => '',
		'title_tag'         => 'h2',
		'button'            => '',
		'button_class'      => '',
		'topic'             => null,
		'topic_value'       => '',
		'topic_label'       => 'Что нужно (необязательно)',
		'topic_placeholder' => 'Например, лицензия на медицинскую деятельность',
		'message_type'      => 'input',
		'topics'            => [],
		'phone_label'       => 'Телефон',
		'source'            => '',
		'dark'              => false,
		'class'             => '',
	]
);

$sky_compact = 'compact' === $args['variant'];
$sky_id      = sanitize_html_class( $args['id'] );
$sky_topic   = null === $args['topic'] ? ! $sky_compact : (bool) $args['topic'];
$sky_button  = '' !== $args['button'] ? $args['button'] : ( $sky_compact ? 'Получить консультацию' : 'Отправить заявку' );
$sky_tag     = in_array( $args['title_tag'], [ 'h2', 'h3', 'p' ], true ) ? $args['title_tag'] : 'h2';
$sky_msgs    = sky_starter_lead_messages();
$sky_state   = sky_starter_lead_state( $sky_id );
$sky_ok      = 'ok' === $sky_state['status'];
$sky_errors  = array_fill_keys( $sky_state['fields'], true );
$sky_policy  = get_privacy_policy_url();

// Метка источника: «Название страницы · первый экран».
$sky_source = $args['source'];
if ( '' === $sky_source ) {
	$sky_source = is_singular() ? wp_strip_all_tags( (string) get_the_title() ) : wp_strip_all_tags( wp_get_document_title() );
	$sky_source = trim( $sky_source . ( $sky_compact ? ' · первый экран' : ' · форма заявки' ) );
}

// Общая ошибка после обычной отправки (статусы baseline: expired, limit, error…).
$sky_general = ( '' !== $sky_state['status'] && ! $sky_ok ) ? ( $sky_errors ? $sky_msgs['fields'] : ( $sky_msgs[ $sky_state['status'] ] ?? $sky_msgs['error'] ) ) : '';

// Поля: id, подписи, ошибки.
$sky_f           = static fn( string $field ): string => $sky_id . '-' . $field;
$sky_field_error = static function ( string $field, string $text ) use ( $sky_f, $sky_errors ): void {
	printf(
		'<p class="field__error" id="%s"%s>%s</p>',
		esc_attr( $sky_f( $field ) . '-error' ),
		isset( $sky_errors[ $field ] ) ? '' : ' hidden',
		esc_html( isset( $sky_errors[ $field ] ) ? $text : '' )
	);
};
$sky_invalid     = static fn( string $field ): string => isset( $sky_errors[ $field ] ) ? ' aria-invalid="true" aria-describedby="' . esc_attr( $sky_f( $field ) . '-error' ) . '"' : '';

$sky_classes = [ 'request', 'request--' . ( $sky_compact ? 'compact' : 'full' ) ];
if ( $args['dark'] ) {
	$sky_classes[] = 'is-dark';
}
if ( $args['class'] ) {
	$sky_classes[] = $args['class'];
}

wp_enqueue_script( 'sky-lead-form' );
?>
<div class="<?php echo esc_attr( implode( ' ', $sky_classes ) ); ?>" id="<?php echo esc_attr( $sky_id ); ?>" data-request data-goal="<?php echo esc_attr( (string) ( function_exists( 'sky_config' ) ? sky_config( 'analytics.goals.lead', 'lead' ) : 'lead' ) ); ?>">
	<?php if ( ! $sky_compact && ( $args['title'] || $args['text'] ) ) : ?>
		<div class="request__head">
			<?php if ( $args['title'] ) : ?>
				<<?php echo esc_html( $sky_tag ); ?> class="request__title"><?php echo esc_html( $args['title'] ); ?></<?php echo esc_html( $sky_tag ); ?>>
			<?php endif; ?>
			<?php if ( $args['text'] ) : ?>
				<p class="request__text"><?php echo esc_html( $args['text'] ); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="request__success" role="status" tabindex="-1"<?php echo $sky_ok ? '' : ' hidden'; ?>>
		<svg class="request__success-icon" aria-hidden="true" focusable="false" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M7.5 12.5l3 3 6-6.5"/></svg>
		<p class="request__success-text"><?php echo esc_html( $sky_msgs['ok'] ); ?></p>
	</div>

	<form class="request__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"<?php echo $sky_ok ? ' hidden' : ''; ?>>
		<input type="hidden" name="action" value="sky_lead">
		<input type="hidden" name="source" value="<?php echo esc_attr( $sky_source ); ?>">
		<input type="hidden" name="anchor" value="<?php echo esc_attr( $sky_id ); ?>">
		<input type="hidden" name="form_variant" value="<?php echo esc_attr( $sky_compact ? 'compact' : 'full' ); ?>">
		<input type="hidden" name="sky_ts" value="<?php echo esc_attr( (string) time() ); ?>">
		<?php
		if ( ! function_exists( 'sky_config' ) || sky_config( 'forms.nonce', true ) ) {
			// Без id: на странице несколько форм, а wp_nonce_field() дал бы им одинаковый id="sky_lead_nonce".
			printf( '<input type="hidden" name="sky_lead_nonce" value="%s">', esc_attr( wp_create_nonce( 'sky_lead' ) ) );
		}
		?>

		<?php // Honeypot: человек его не видит и не попадает в него с клавиатуры. ?>
		<div class="request__hp" aria-hidden="true">
			<label for="<?php echo esc_attr( $sky_f( 'hp' ) ); ?>">Не заполняйте это поле</label>
			<input id="<?php echo esc_attr( $sky_f( 'hp' ) ); ?>" type="text" name="sky_hp" tabindex="-1" autocomplete="off">
		</div>

		<p class="request__error" role="alert"<?php echo '' !== $sky_general ? '' : ' hidden'; ?>><?php echo esc_html( $sky_general ); ?></p>

		<div class="request__fields">
			<?php if ( ! $sky_compact ) : ?>
				<div class="field<?php echo isset( $sky_errors['name'] ) ? ' field--error' : ''; ?>" data-field="name">
					<label class="field__label" for="<?php echo esc_attr( $sky_f( 'name' ) ); ?>">Имя</label>
					<input class="field__input" id="<?php echo esc_attr( $sky_f( 'name' ) ); ?>" type="text" name="name" required maxlength="100" autocomplete="name"<?php echo $sky_invalid( 'name' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- экранировано в $sky_invalid. ?>>
					<?php $sky_field_error( 'name', $sky_msgs['name'] ); ?>
				</div>
			<?php endif; ?>

			<div class="field<?php echo isset( $sky_errors['phone'] ) ? ' field--error' : ''; ?>" data-field="phone">
				<label class="field__label" for="<?php echo esc_attr( $sky_f( 'phone' ) ); ?>"><?php echo esc_html( $args['phone_label'] ); ?></label>
				<input class="field__input" id="<?php echo esc_attr( $sky_f( 'phone' ) ); ?>" type="tel" name="phone" required maxlength="30" autocomplete="tel" inputmode="tel" placeholder="+7 (___) ___-__-__" data-phone-mask<?php echo $sky_invalid( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- экранировано в $sky_invalid. ?>>
				<?php $sky_field_error( 'phone', $sky_msgs['phone'] ); ?>
			</div>

			<?php if ( $args['topics'] ) : ?>
				<div class="field" data-field="topic">
					<label class="field__label" for="<?php echo esc_attr( $sky_f( 'topic' ) ); ?>">Тема обращения</label>
					<select class="field__input" id="<?php echo esc_attr( $sky_f( 'topic' ) ); ?>" name="topic">
						<?php foreach ( (array) $args['topics'] as $sky_topic_option ) : ?>
							<option><?php echo esc_html( (string) $sky_topic_option ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>

			<?php if ( $sky_topic ) : ?>
				<div class="field" data-field="message">
					<label class="field__label" for="<?php echo esc_attr( $sky_f( 'message' ) ); ?>"><?php echo esc_html( $args['topic_label'] ); ?></label>
					<?php if ( 'textarea' === $args['message_type'] ) : ?>
						<textarea class="field__input" id="<?php echo esc_attr( $sky_f( 'message' ) ); ?>" name="message" rows="4" maxlength="1500" placeholder="<?php echo esc_attr( $args['topic_placeholder'] ); ?>"><?php echo esc_textarea( (string) $args['topic_value'] ); ?></textarea>
					<?php else : ?>
						<input class="field__input" id="<?php echo esc_attr( $sky_f( 'message' ) ); ?>" type="text" name="message" maxlength="300" placeholder="<?php echo esc_attr( $args['topic_placeholder'] ); ?>" value="<?php echo esc_attr( (string) $args['topic_value'] ); ?>">
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php // Согласие — до кнопки: сначала отмечают, потом отправляют. ?>
			<div class="request__consent<?php echo isset( $sky_errors['consent'] ) ? ' field--error' : ''; ?>" data-field="consent">
				<label class="check" for="<?php echo esc_attr( $sky_f( 'consent' ) ); ?>">
					<input class="check__input" id="<?php echo esc_attr( $sky_f( 'consent' ) ); ?>" type="checkbox" name="consent" value="1" required<?php echo $sky_invalid( 'consent' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- экранировано в $sky_invalid. ?>>
					<span class="check__text">
						Согласен на обработку персональных данных<?php if ( $sky_policy ) : ?>
							в соответствии с <a href="<?php echo esc_url( $sky_policy ); ?>" target="_blank" rel="noopener">политикой конфиденциальности</a><?php endif; ?>
					</span>
				</label>
				<?php $sky_field_error( 'consent', $sky_msgs['consent'] ); ?>
			</div>

			<button class="btn request__submit <?php echo esc_attr( $args['button_class'] ); ?>" type="submit"><?php echo esc_html( $sky_button ); ?></button>
		</div>
	</form>
</div>
