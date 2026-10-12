<?php
/**
 * Каркас страницы: верхняя полоса, шапка, подвал (DESIGN.md §5 «Шапка»).
 * Шапку и подвал Kadence заменяют наши template-parts/layout/* — через его же хуки,
 * header.php и footer.php Kadence не переопределяются. Проверено по коду Kadence 1.5.2.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

// Приоритет 20: functions.php Kadence уже загружен и повесил свои хуки.
add_action( 'after_setup_theme', 'sky_starter_layout_setup', 20 );
add_action( 'customize_register', 'sky_starter_layout_customizer', 20 );
add_action( 'wp_enqueue_scripts', 'sky_starter_layout_dequeue', 30 );
add_action( 'wp_body_open', 'sky_starter_form_fallback', 1 );

/**
 * Адрес формы, если на странице нет #form (header.js подменяет ссылки «#form»): форма в «Контактах».
 */
function sky_starter_form_fallback(): void {
	printf( '<script>document.body.dataset.formFallback=%s;</script>' . "\n", wp_json_encode( home_url( '/kontakty/#form' ) ) );
}

/**
 * Стили шапки и подвала Kadence (~45 КБ) не нужны: их разметки на странице нет.
 */
function sky_starter_layout_dequeue(): void {
	wp_dequeue_style( 'kadence-header' );
	wp_dequeue_style( 'kadence-footer' );
}

/**
 * Меню и замена шапки/подвала Kadence.
 */
function sky_starter_layout_setup(): void {
	register_nav_menus(
		[
			'primary'         => 'Шапка: основное меню',
			'footer_licenses' => 'Подвал: лицензии',
			'footer_services' => 'Подвал: услуги',
		]
	);

	// Мобильная панель Kadence (navigation_popup) подключается из его шапки — уходит вместе с ней.
	remove_action( 'kadence_header', 'Kadence\header_markup' );
	remove_action( 'kadence_footer', 'Kadence\footer_markup' );
	add_action( 'kadence_header', 'sky_starter_header' );
	add_action( 'kadence_footer', 'sky_starter_footer' );
}

/**
 * Верхняя полоса и шапка.
 */
function sky_starter_header(): void {
	get_template_part( 'template-parts/layout/topbar' );
	get_template_part( 'template-parts/layout/header' );
}

/**
 * Подвал и панель действий на телефоне.
 */
function sky_starter_footer(): void {
	get_template_part( 'template-parts/layout/footer' );
	get_template_part( 'template-parts/layout/mobile-bar' );
}

/**
 * Поля Customizer для шапки — в секцию baseline «Контакты организации».
 * Значения читаются через sky_opt( 'sky_telegram' ): Customizer, затем organization.* конфига.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function sky_starter_layout_customizer( $wp_customize ): void {
	$section = $wp_customize->get_section( 'sky_contacts' ) ? 'sky_contacts' : 'title_tagline';

	$fields = [
		'sky_telegram'    => [ 'Telegram (https://t.me/…)', 'esc_url_raw', 'url' ],
		'sky_whatsapp'    => [ 'WhatsApp (https://wa.me/7…)', 'esc_url_raw', 'url' ],
		'sky_topbar_text' => [ 'Текст верхней полосы', 'sanitize_text_field', 'text' ],
	];
	foreach ( $fields as $id => [ $label, $sanitize, $type ] ) {
		$wp_customize->add_setting(
			$id,
			[
				'type'              => 'theme_mod',
				'sanitize_callback' => $sanitize,
			]
		);
		$wp_customize->add_control(
			$id,
			[
				'label'   => $label,
				'section' => $section,
				'type'    => $type,
			]
		);
	}
}

/**
 * Значение организации строкой. Без baseline — пусто, шаблоны показывают заглушки.
 *
 * @param string $key Ключ без префикса: phone, telegram, legal_name, inn…
 */
function sky_starter_org( string $key ): string {
	if ( ! function_exists( 'sky_opt' ) ) {
		return 'name' === $key ? (string) get_bloginfo( 'name' ) : '';
	}
	$value = sky_opt( 'sky_' . $key );
	return is_scalar( $value ) ? trim( (string) $value ) : '';
}

/**
 * Ссылка tel: из телефона в любом формате. Пустой телефон — пустая строка.
 *
 * @param string $phone Телефон.
 */
function sky_starter_tel( string $phone ): string {
	$digits = preg_replace( '/\D+/', '', $phone );
	if ( '' === $digits ) {
		return '';
	}
	if ( 11 === strlen( $digits ) && '8' === $digits[0] ) {
		$digits = '7' . substr( $digits, 1 );
	}
	return 'tel:+' . $digits;
}

/**
 * Якорь формы заявки: на него ведут «Получить консультацию» и кнопки-заглушки.
 */
function sky_starter_form_anchor(): string {
	return (string) apply_filters( 'sky_starter_form_anchor', '#form' );
}

/**
 * Меню по умолчанию, пока в «Внешний вид → Меню» не назначено своё.
 * Адреса — по структуре DESIGN.md §7.
 *
 * @param array $args Аргументы wp_nav_menu().
 */
function sky_starter_menu_fallback( $args ): string {
	$args  = (array) $args;
	$menus = [
		'primary'         => [
			'/licenzirovanie/'     => 'Лицензирование',
			'/yurist-na-autsorse/' => 'Юрист на аутсорсе',
			'/dokumenty/'          => 'Документы',
			'/yuridicheskiy-ii/'   => 'Юридический ИИ',
			'/o-kompanii/'         => 'О компании',
			'/kontakty/'           => 'Контакты',
		],
		'footer_licenses' => [
			'/licenzirovanie/alkogol/'     => 'Алкоголь',
			'/licenzirovanie/medicina/'    => 'Медицина и фарма',
			'/licenzirovanie/obrazovanie/' => 'Образование',
			'/licenzirovanie/'             => 'Все виды',
		],
		'footer_services' => [
			'/yurist-na-autsorse/' => 'Юрист на аутсорсе',
			'/dokumenty/'          => 'Документы',
			'/yuridicheskiy-ii/'   => 'Юридический ИИ',
		],
	];
	$items = $menus[ $args['theme_location'] ?? '' ] ?? [];
	if ( ! $items ) {
		return '';
	}

	$current = trailingslashit( (string) wp_parse_url( add_query_arg( [] ), PHP_URL_PATH ) );
	$html    = '';
	foreach ( $items as $path => $label ) {
		$item_path  = trailingslashit( (string) wp_parse_url( home_url( $path ), PHP_URL_PATH ) );
		$is_current = $current === $item_path;
		// Раздел подсвечивается и на вложенных страницах, как current-menu-ancestor у wp_nav_menu.
		$is_parent = ! $is_current && 'primary' === $args['theme_location'] && str_starts_with( $current, $item_path );
		$classes   = 'menu-item' . ( $is_current ? ' current-menu-item' : '' ) . ( $is_parent ? ' current-menu-ancestor' : '' );
		$html     .= sprintf(
			'<li class="%s"><a href="%s"%s>%s</a></li>',
			esc_attr( $classes ),
			esc_url( home_url( $path ) ),
			$is_current ? ' aria-current="page"' : '',
			esc_html( $label )
		);
	}
	$html = sprintf( '<ul class="%s">%s</ul>', esc_attr( $args['menu_class'] ?? 'menu' ), $html );

	if ( ! empty( $args['echo'] ) ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- собрано из экранированных частей выше.
	}
	return $html;
}

/**
 * Иконки шапки. Декоративные: подпись — у кнопки (aria-label).
 *
 * @param string $name phone, menu, close.
 */
function sky_starter_icon( string $name ): string {
	$paths = [
		'phone' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"/>',
		'menu'  => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close' => '<path d="M6 6l12 12M18 6L6 18"/>',
	];
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="icon icon--' . esc_attr( $name ) . '" aria-hidden="true" focusable="false" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $paths[ $name ] . '</svg>';
}
