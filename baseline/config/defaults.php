<?php
/**
 * Значения по умолчанию. Конфиг проекта перекрывает их глубоким слиянием.
 * Описание каждого ключа — в project/sky-config.example.php.
 *
 * @package Sky\Baseline
 */

defined( 'ABSPATH' ) || exit;

return [
	'modules'      => [
		'settings'    => true,
		'meta'        => true,
		'robots'      => true,
		'canonical'   => true,
		'breadcrumbs' => true,
		'schema'      => true,
		'redirects'   => true,
		'forms'       => true,
		'cpt'         => true,
		'cleanup'     => true,
		'opengraph'   => true,
		'sitemap'     => true,
		'robots_txt'  => true,
		'analytics'   => true,
	],

	'seo_plugins'  => [
		'auto_disable' => true,
		'modules'      => [ 'meta', 'schema', 'opengraph', 'canonical' ],
	],

	'fields'       => [
		'providers'  => [ 'meta-box', 'acf' ],
		'seo_fields' => true,
	],

	'organization' => [
		'name'       => '',
		'legal_name' => '',
		'phone'      => '',
		'email'      => '',
		'street'     => '',
		'city'       => '',
		'region'     => '',
		'zip'        => '',
		'country'    => 'RU',
		'hours'      => '',
		'lat'        => '',
		'lng'        => '',
		'inn'        => '',
		'ogrn'       => '',
		'kpp'        => '',
		'logo'       => '',
		'same_as'    => [],
	],

	'city'         => [
		'enabled'       => true,
		'nominative'    => '',
		'prepositional' => '',
		'preposition'   => 'в',
	],

	'title'        => [
		'templates'    => [
			'front'   => '%site% — %tagline%',
			'home'    => '%title% | %site%',
			'page'    => '%title% | %site%',
			'post'    => '%title% | %site%',
			'archive' => '%archive% %city_in% | %site%',
			'tax'     => '%term% | %site%',
			'author'  => '%title% | %site%',
			'date'    => '%title% | %site%',
			'search'  => 'Поиск: %query% | %site%',
			'404'     => 'Страница не найдена | %site%',
		],
		'paged_suffix' => ' — страница %page%',
	],

	'meta'         => [
		'description_max'       => 300,
		'description_templates' => [
			'archive' => '',
			'tax'     => '',
		],
	],

	'robots'       => [
		'noindex'            => [ 'search', 'author', 'date', 'attachment', '404' ],
		'noindex_query_args' => [],
		'max_image_preview'  => 'large',
	],

	'canonical'    => [
		'archives'          => true,
		'redirect_page_one' => true,
	],

	'breadcrumbs'  => [
		'home_label'       => 'Главная',
		'search_label'     => 'Поиск: %s',
		'404_label'        => 'Страница не найдена',
		'primary_taxonomy' => [],
		'hubs'             => [],
	],

	'schema'       => [
		'type'        => 'LocalBusiness',
		'area_served' => [],
		'price_range' => '',
		'extra'       => [],
	],

	'redirects'    => [
		'map'              => [],
		'prefixes'         => [],
		'lowercase'        => true,
		'collapse_slashes' => true,
	],

	'forms'        => [
		'email_to'         => '',
		'subject'          => 'Заявка с сайта: %site%',
		'nonce'            => true,
		'min_fill_seconds' => 3,
		'rate_limit'       => 5,
		'messages'         => [
			'ok'      => 'Заявка отправлена. Перезвоним в рабочее время.',
			'consent' => 'Нужно согласие на обработку персональных данных.',
			'error'   => 'Не удалось отправить. Проверьте имя и телефон.',
			'expired' => 'Страница устарела. Обновите её и отправьте заявку ещё раз.',
			'limit'   => 'Слишком много заявок подряд. Попробуйте позже или позвоните нам.',
		],
		'telegram'         => [
			'enabled' => false,
			'token'   => '',
			'chat_id' => '',
			'timeout' => 5,
		],
	],

	'cpt'          => [
		'post_types' => [],
		'taxonomies' => [],
		'fields'     => [],
	],

	'analytics'    => [
		'metrika_id'      => '',
		'webvisor'        => false,
		'exclude_admins'  => true,
		'goals'           => [
			'lead'      => 'lead',
			'tel'       => 'tel_click',
			'email'     => 'email_click',
			'messenger' => 'messenger_click',
		],
		'messenger_hosts' => [ 't.me', 'telegram.me', 'wa.me', 'api.whatsapp.com', 'max.ru', 'vk.me' ],
	],

	'sitemap'      => [
		'users'              => false,
		'exclude_post_types' => [],
		'exclude_taxonomies' => [ 'post_format' ],
		'exclude_noindex'    => true,
	],

	'robots_txt'   => [
		'disallow'    => [],
		'clean_param' => [ 'utm_source&utm_medium&utm_campaign&utm_content&utm_term&yclid&gclid&fbclid&_openstat' ],
	],

	'cleanup'      => [
		'emoji'            => true,
		'oembed_discovery' => true,
		'rsd'              => true,
		'wlwmanifest'      => true,
		'rest_link'        => true,
		'shortlink'        => true,
		'generator'        => true,
		'xmlrpc'           => true,
	],

	'opengraph'    => [
		'default_image' => '',
		'locale'        => 'ru_RU',
	],
];
