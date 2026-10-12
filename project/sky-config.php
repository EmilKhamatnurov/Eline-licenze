<?php
/**
 * Конфиг проекта. Скопируйте в project/sky-config.php (bin/init.sh делает это сам).
 *
 * Здесь достаточно указать только то, что отличается от значений по умолчанию
 * (baseline/config/defaults.php): ассоциативные массивы сливаются, списки заменяются целиком.
 *
 * Секреты (токен Telegram) лучше задавать в wp-config.php константами SKY_TG_TOKEN и SKY_TG_CHAT_ID.
 *
 * @package Sky\Baseline
 */

defined( 'ABSPATH' ) || exit;

return [

	// Включение модулей. false — модуль не вешает ни одного хука.
	'modules'      => [
		'settings'    => true,  // Customizer → «Контакты организации».
		'meta'        => true,  // <title> по шаблонам, meta description, SEO-поля в записях.
		'robots'      => true,  // noindex для служебных страниц и по полю seo_noindex.
		'canonical'   => true,  // canonical для архивов, таксономий, блога; 301 с /page/1/.
		'breadcrumbs' => true,  // sky_breadcrumbs() и BreadcrumbList.
		'schema'      => true,  // JSON-LD: организация, крошки, FAQPage.
		'redirects'   => true,  // Карта 301, склейка //, нижний регистр.
		'forms'       => true,  // sky_lead_form(), заявки в админке, письмо, Telegram.
		'cpt'         => true,  // Типы записей, таксономии, поля из раздела 'cpt'.
		'cleanup'     => true,  // Чистка <head>, xmlrpc.
		'opengraph'   => true,  // og:* и twitter:card.
		'sitemap'     => true,  // Чистка wp-sitemap.xml.
		'analytics'   => true,  // Яндекс Метрика и цели (нужен analytics.metrika_id).
	],

	// Если активен Yoast, Rank Math, AIOSEO, SEOPress или The SEO Framework —
	// эти модули выключаются автоматически, в админке будет уведомление.
	'seo_plugins'  => [
		'auto_disable' => true,
		'modules'      => [ 'meta', 'schema', 'opengraph', 'canonical' ],
	],

	'fields'       => [
		// Порядок = приоритет. Используется первый установленный плагин.
		'providers'  => [ 'meta-box', 'acf' ],
		// Мета-бокс «SEO» (seo_title, seo_description, seo_noindex) во всех публичных типах записей.
		'seo_fields' => true,
	],

	// Контакты и реквизиты. Непустые поля Customizer перекрывают эти значения.
	// В шаблонах: sky_opt( 'sky_phone' ), sky_opt( 'sky_inn' ) и т. д.
	// Пустые поля — до получения данных клиента: тема выводит заглушки [ТЕЛЕФОН], ИНН [XXXXXXXXXX]…
	'organization' => [
		'name'       => '', // Пусто — название сайта.
		'legal_name' => '',
		'phone'      => '',
		'email'      => '',
		'street'     => '',
		'city'       => '',
		'region'     => '',
		'zip'        => '',
		'country'    => 'RU',
		'hours'      => 'Mo-Fr 09:00-18:00', // Несколько интервалов — через «;» или массивом.
		'lat'        => '',
		'lng'        => '',
		// Мессенджеры для шапки (тема): ссылка https://t.me/… и https://wa.me/7…
		'telegram'   => '',
		'whatsapp'   => '',
		// Реквизиты повышают доверие в YMYL-тематиках (право, медицина, финансы).
		'inn'        => '',
		'ogrn'       => '', // 15 цифр — выводится как ОГРНИП.
		'kpp'        => '',
		'logo'       => '', // ID вложения или URL; пусто — логотип темы.
		// Профили для sameAs: Яндекс Карты, VK, Telegram-канал, 2ГИС…
		'same_as'    => [
			// 'https://yandex.ru/maps/org/0000000000/',
			// 'https://vk.com/example',
		],
	],

	// Город в Title. %city% — именительный, %city_in% — «в Уфе».
	'city'         => [
		// Компания работает дистанционно по всей России — город в Title не выводится.
		'enabled'       => false, // false — оба токена пустые, лишние пробелы и разделители убираются.
		'nominative'    => 'Уфа', // Пусто — organization.city.
		'prepositional' => 'Уфе',
		'preposition'   => 'в',   // «во» для «во Владимире».
	],

	'title'        => [
		// Токены: %site% %tagline% %city% %city_in% %phone%, а также
		// %title% (запись, блог, автор, дата), %archive% (архив типа), %term% (термин), %query% (поиск).
		// Ключи: front, home (страница блога), page, post, <post_type>, archive, archive_<post_type>,
		// tax, tax_<taxonomy>, author, date, search, 404.
		'templates'    => [
			'license'         => '%title% — лицензия под ключ | %site%',
			'archive_license' => 'Лицензирование под ключ | %site%',
			// Только направления лицензий: рубрики блога остаются с нейтральным дефолтом «%term% | %site%».
			'tax_regulator'   => 'Лицензии: %term% | %site%',
		],
		// Добавляется к Title страниц 2, 3… перед « | %site%».
		'paged_suffix' => ' — страница %page%',
	],

	'meta'         => [
		'description_max'       => 300, // Длиннее — обрезка по слову с «…».
		// Если у архива или термина нет описания. Токены те же, что в Title.
		// Ключи: archive, archive_<post_type>, tax, tax_<taxonomy> — берётся самый точный.
		'description_templates' => [
			'archive_license' => 'Виды лицензий: требования, документы, стоимость и сроки оформления.',
			'tax_regulator'   => 'Лицензии в направлении «%term%»: требования, документы, стоимость и сроки оформления.',
		],
	],

	'robots'       => [
		// Какие выдачи закрыть: search, author, date, attachment, 404.
		'noindex'            => [ 'search', 'author', 'date', 'attachment', '404' ],
		// URL с этими GET-параметрами получают noindex (?lead= закрыт модулем forms всегда).
		'noindex_query_args' => [ 'q' ], // ?q= — поиск по каталогу лицензий.
		'max_image_preview'  => 'large',
	],

	'canonical'    => [
		'archives'          => true, // canonical на архивах, терминах, блоге, страницах пагинации.
		'redirect_page_one' => true, // /razdel/page/1/ → /razdel/ (301).
	],

	'breadcrumbs'  => [
		'home_label'       => 'Главная',
		'search_label'     => 'Поиск: %s',
		'404_label'        => 'Страница не найдена',
		// Таксономия, термин которой встаёт в крошки записи: тип записи => таксономия.
		'primary_taxonomy' => [
			'license' => 'regulator',
		],
		// Страница-хаб вместо архива типа: тип записи => ID или путь страницы.
		// То же можно задать ключом 'hub' в cpt.post_types.
		'hubs'             => [],
	],

	'schema'       => [
		// LocalBusiness, LegalService, MedicalBusiness, Dentist, AutoRepair, ProfessionalService…
		'type'        => 'LegalService',
		// Строки становятся { "@type": "City" }, массивы передаются как есть.
		'area_served' => [ 'Уфа', 'Республика Башкортостан' ],
		'price_range' => '₽₽',
		// Любые дополнительные свойства узла организации (перекрывают вычисленные).
		'extra'       => [
			// 'founder' => [ '@type' => 'Person', 'name' => 'Иван Иванов' ],
		],
	],

	'redirects'    => [
		// Старый путь (со слэшем в начале и в конце или как есть, например /page.html) => путь или полный URL.
		// Срабатывает только если по старому адресу 404.
		'map'              => [
			// '/uslugi/staraya-usluga/' => '/uslugi/novaya-usluga/',
			// '/old-page.html'          => '/kontakty/',
			// '/promo/'                 => 'https://example.com/',
		],
		// Перенос раздела: '/services/' => '/uslugi/' превращает /services/foo/ в /uslugi/foo/.
		'prefixes'         => [],
		'lowercase'        => true, // /Uslugi/ → /uslugi/ (%XX-кодировку не трогает).
		'collapse_slashes' => true, // //uslugi//a/ → /uslugi/a/.
	],

	'forms'        => [
		'email_to'         => '', // Через запятую; пусто — e-mail администратора.
		'subject'          => 'Заявка с сайта: %site%',
		// false — без nonce (если страницы отдаются из полного кэша дольше 12 часов).
		'nonce'            => true,
		'min_fill_seconds' => 3, // Отправка быстрее — считается ботом.
		'rate_limit'       => 5, // Заявок с одного IP за 10 минут; 0 — без лимита.
		'messages'         => [
			'ok'      => 'Заявка отправлена. Перезвоним в рабочее время.',
			'consent' => 'Нужно согласие на обработку персональных данных.',
			'error'   => 'Не удалось отправить. Проверьте имя и телефон.',
			'expired' => 'Страница устарела. Обновите её и отправьте заявку ещё раз.', // Устаревший nonce.
			'limit'   => 'Слишком много заявок подряд. Попробуйте позже или позвоните нам.', // forms.rate_limit.
		],
		'telegram'         => [
			'enabled' => false,
			'token'   => '', // Лучше константа SKY_TG_TOKEN в wp-config.php.
			'chat_id' => '', // Или константа SKY_TG_CHAT_ID.
			'timeout' => 5,  // Секунд; ошибка Telegram не мешает сохранению заявки.
		],
	],

	'cpt'          => [
		// Ключ — имя типа (до 20 символов, латиница).
		'post_types' => [
			// Адреса (DESIGN.md §7): /licenzirovanie/ — каталог (архив типа),
			// /licenzirovanie/<regulator>/ — направление, /licenzirovanie/<regulator>/<slug>/ — лицензия.
			// %regulator% подставляет тема (inc/license.php) — первый термин, как в крошках.
			'license' => [
				'label'    => 'Лицензирование',
				'singular' => 'Лицензия',
				'slug'     => 'licenzirovanie/%regulator%',
				'archive'  => 'licenzirovanie',
				'icon'     => 'dashicons-awards',
				'supports' => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
				'order'    => 'menu_order',
				'labels'   => [
					'menu_name' => 'Лицензии',
					'all_items' => 'Все лицензии',
				],
			],
		],
		'taxonomies' => [
			'regulator' => [
				'label'        => 'Направления',
				'singular'     => 'Направление',
				'post_types'   => [ 'license' ],
				'slug'         => 'licenzirovanie',
				'hierarchical' => true, // Галочки в редакторе.
				'args'         => [
					// Адрес без вложенности: иначе /licenzirovanie/<regulator>/<slug>/ читался бы как подраздел.
					'rewrite'      => [
						'slug'         => 'licenzirovanie',
						'with_front'   => false,
						'hierarchical' => false,
					],
					// Лицензия без направления получает «Другие лицензии» — у адреса всегда есть раздел.
					'default_term' => [
						'name' => 'Другие лицензии',
						'slug' => 'drugie',
					],
				],
			],
		],
		// Мета-боксы в формате Meta Box. С ACF конвертируются автоматически.
		// Поле group — платное: Meta Box Group (или AIO), для клонируемой группы в ACF — repeater из ACF PRO.
		// Без них группа становится textarea: пункты через пустую строку, строки пункта — подполя
		// по порядку. sky_field_raw() в обоих случаях отдаёт массив.
		// Поля — DESIGN.md §7. Название — заголовок записи, регулятор — таксономия, SEO-поля — модуль meta.
		'fields'     => [
			[
				'id'         => 'license_main',
				'title'      => 'Лицензия: основное',
				'post_types' => [ 'license' ],
				'fields'     => [
					[
						'id'   => 'h1',
						'name' => 'Заголовок страницы (H1)',
						'type' => 'text',
						'desc' => 'Например: «Лицензия на медицинскую деятельность под ключ». Пусто — название записи.',
					],
					[
						'id'   => 'activity',
						'name' => 'Вид деятельности',
						'type' => 'text',
					],
					[
						'id'   => 'price_from',
						'name' => 'Цена от, ₽',
						'type' => 'number',
						'min'  => 0,
						'desc' => 'Только число. Пусто — на сайте «[ЦЕНА]».',
					],
					[
						'id'   => 'term_from',
						'name' => 'Срок от, дней',
						'type' => 'number',
						'min'  => 0,
						'desc' => 'Только число. Пусто — на сайте «[СРОК]».',
					],
					[
						'id'   => 'intro',
						'name' => 'Когда лицензия обязательна',
						'type' => 'textarea',
						'rows' => 5,
						'desc' => 'Абзацы — через пустую строку.',
					],
					[
						'id'   => 'requirements',
						'name' => 'Требования (по одному на строку)',
						'type' => 'textarea',
						'rows' => 6,
					],
					[
						'id'   => 'docs_legal',
						'name' => 'Документы для юрлица (по одному на строку)',
						'type' => 'textarea',
						'rows' => 6,
					],
					[
						'id'   => 'docs_ip',
						'name' => 'Документы для ИП (по одному на строку)',
						'type' => 'textarea',
						'rows' => 6,
					],
				],
			],
			[
				'id'         => 'license_blocks',
				'title'      => 'Лицензия: этапы, тарифы, вопросы',
				'post_types' => [ 'license' ],
				'fields'     => [
					[
						'id'     => 'steps',
						'name'   => 'Этапы',
						'type'   => 'group',
						'clone'  => true,
						'fields' => [
							[
								'id'   => 'title',
								'name' => 'Название',
								'type' => 'text',
							],
							[
								'id'   => 'term',
								'name' => 'Срок',
								'type' => 'text',
							],
							[
								'id'   => 'text',
								'name' => 'Описание',
								'type' => 'textarea',
							],
						],
					],
					[
						'id'     => 'tariffs',
						'name'   => 'Тарифы (три)',
						'type'   => 'group',
						'clone'  => true,
						'fields' => [
							[
								'id'   => 'name',
								'name' => 'Название',
								'type' => 'text',
							],
							[
								'id'   => 'price',
								'name' => 'Цена (число или [ЦЕНА] — строка не должна быть пустой)',
								'type' => 'text',
							],
							[
								'id'   => 'features',
								'name' => 'Что входит (по одному на строку)',
								'type' => 'textarea',
							],
						],
					],
					[
						'id'      => 'tariff_featured',
						'name'    => 'Рекомендуемый тариф',
						'type'    => 'select',
						'options' => [
							'1' => 'Первый',
							'2' => 'Второй',
							'3' => 'Третий',
						],
						'std'     => '2',
					],
					[
						'id'     => 'faq',
						'name'   => 'Вопросы и ответы',
						'type'   => 'group',
						'clone'  => true,
						'fields' => [
							[
								'id'   => 'question',
								'name' => 'Вопрос',
								'type' => 'text',
							],
							[
								'id'   => 'answer',
								'name' => 'Ответ',
								'type' => 'textarea',
							],
						],
					],
					[
						'id'         => 'related',
						'name'       => 'Смежные лицензии',
						'type'       => 'post',
						'post_type'  => [ 'license' ],
						'field_type' => 'select_advanced',
						'multiple'   => true,
					],
				],
			],
		],
	],

	'analytics'    => [
		'metrika_id'      => '', // Номер счётчика; пусто — Метрика не выводится.
		'webvisor'        => true,
		'exclude_admins'  => true, // Не считать визиты администраторов.
		// Имена целей (создайте их в Метрике как «JavaScript-событие»).
		'goals'           => [
			'lead'      => 'lead',
			'tel'       => 'tel_click',
			'email'     => 'email_click',
			'messenger' => 'messenger_click',
		],
		// Клики по ссылкам на эти домены и по tg:, whatsapp:, viber: — цель messenger.
		'messenger_hosts' => [ 't.me', 'telegram.me', 'wa.me', 'api.whatsapp.com', 'max.ru', 'vk.me' ],
	],

	'sitemap'      => [
		'users'              => false, // /wp-sitemap-users-1.xml.
		'exclude_post_types' => [],    // sky_lead исключён всегда.
		'exclude_taxonomies' => [ 'post_format' ],
		'exclude_noindex'    => true,  // Записи с seo_noindex не попадают в карту.
	],

	// true — отключить.
	'cleanup'      => [
		'emoji'            => true,
		'oembed_discovery' => true,
		'rsd'              => true,
		'wlwmanifest'      => true,
		'rest_link'        => true, // <link rel="https://api.w.org/"> и заголовок Link (REST API работает).
		'shortlink'        => true,
		'generator'        => true,
		'xmlrpc'           => true, // Методы xmlrpc и заголовок X-Pingback.
	],

	'opengraph'    => [
		'default_image' => '', // ID вложения или URL — если у записи нет миниатюры.
		'locale'        => 'ru_RU',
	],
];
