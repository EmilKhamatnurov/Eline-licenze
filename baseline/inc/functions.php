<?php
/**
 * Публичный API для темы. Сигнатуры функций 1.x сохранены.
 *
 * @package Sky\Baseline
 */

use Sky\Baseline\Config;
use Sky\Baseline\Fields;
use Sky\Baseline\Modules\Breadcrumbs;
use Sky\Baseline\Modules\Forms;
use Sky\Baseline\Modules\Redirects;
use Sky\Baseline\Modules\Schema;
use Sky\Baseline\Organization;
use Sky\Baseline\Plugin;
use Sky\Baseline\Seo\Breadcrumbs as Chain;
use Sky\Baseline\Seo\Context;

defined( 'ABSPATH' ) || exit;

/**
 * Значение конфига по пути через точку; без аргументов — весь конфиг.
 *
 * @param string|null $key      Путь, например 'schema.type'.
 * @param mixed       $fallback Значение по умолчанию.
 * @return mixed
 */
function sky_config( ?string $key = null, $fallback = null ) {
	return null === $key ? Config::all() : Config::get( $key, $fallback );
}

/**
 * Контакты и реквизиты: sky_opt( 'sky_phone' ). Customizer поверх organization.* из конфига.
 * Ключи без префикса sky_ читаются как обычный theme_mod.
 *
 * @param string $key      Ключ.
 * @param mixed  $fallback Значение, если пусто.
 * @return mixed
 */
function sky_opt( $key, $fallback = '' ) {
	$key = (string) $key;
	if ( ! str_starts_with( $key, 'sky_' ) ) {
		return get_theme_mod( $key, $fallback );
	}
	$value = Organization::get( substr( $key, 4 ) );
	return ( '' === $value || null === $value || [] === $value ) ? $fallback : $value;
}

/**
 * Поле записи строкой (Meta Box или ACF). Никогда не падает без плагинов полей.
 *
 * @param string   $key Ключ поля.
 * @param int|null $id  ID записи.
 */
function sky_field( $key, $id = null ): string {
	return Fields::string( (string) $key, $id ? (int) $id : null );
}

/**
 * Поле как есть — для групп, клонов, картинок.
 *
 * @param string   $key Ключ поля.
 * @param int|null $id  ID записи.
 * @return mixed
 */
function sky_field_raw( $key, $id = null ) {
	return Fields::raw( (string) $key, $id ? (int) $id : null );
}

/**
 * Шаблоны Title (фильтр sky_title_templates).
 */
function sky_title_templates(): array {
	return Context::templates();
}

/**
 * Подстановка токенов %site%, %tagline%, %city%, %city_in%, %phone% в строку.
 *
 * @param string $tpl   Шаблон.
 * @param array  $extra Дополнительные токены.
 */
function sky_render( $tpl, array $extra = [] ): string {
	return Context::render( (string) $tpl, $extra );
}

/**
 * Крошки массивом: [ [ 'name' => …, 'url' => … ], … ]. Пусто, если модуль breadcrumbs выключен.
 */
function sky_breadcrumbs_items(): array {
	return Plugin::active( 'breadcrumbs' ) ? Chain::items() : [];
}

/**
 * Тип записи, к которому привязана таксономия.
 *
 * @param string $taxonomy Таксономия.
 */
function sky_tax_post_type( $taxonomy ): string {
	return Chain::tax_post_type( (string) $taxonomy );
}

/**
 * Вывод крошек.
 *
 * @param array $args class — CSS-класс корня (по умолчанию crumbs).
 */
function sky_breadcrumbs( array $args = [] ): void {
	if ( Plugin::active( 'breadcrumbs' ) ) {
		echo Breadcrumbs::render( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- экранируется в render().
	}
}

/**
 * Вывод формы заявки.
 *
 * @param array $args title, source, id (якорь), button.
 */
function sky_lead_form( $args = [] ): void {
	if ( Plugin::active( 'forms' ) ) {
		echo Forms::render( (array) $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- экранируется в render().
	}
}

/**
 * Добавить вопросы в FAQPage текущей страницы.
 *
 * @param array $items [ [ 'question' => …, 'answer' => … ], … ] (или ключи q/a).
 */
function sky_schema_add_faq( array $items ): void {
	Schema::add_faq( $items );
}

/**
 * Добавить узел в граф JSON-LD текущей страницы.
 *
 * @param array $node Узел schema.org.
 */
function sky_schema_add_node( array $node ): void {
	Schema::add_node( $node );
}

/**
 * Карта редиректов из конфига (фильтр sky_redirect_map).
 */
function sky_redirect_map(): array {
	return Redirects::map();
}

/**
 * Префиксы редиректов из конфига (фильтр sky_redirect_prefixes).
 */
function sky_redirect_prefixes(): array {
	return Redirects::prefixes();
}

/**
 * 301 на путь сайта или полный URL и завершение запроса.
 *
 * @param string $target Цель.
 * @param string $query  Строка запроса.
 */
function sky_do_redirect( $target, $query = '' ): void {
	Redirects::go( (string) $target, (string) $query );
}
