# CHANGELOG

## Не выпущено

По результатам проверки на стенде (LocalWP, Kadence 1.5.2, бесплатный Meta Box 5.15).

### Исправления

- **Группы полей без платного расширения.** Поле `group` есть только в Meta Box Group / AIO (repeater — в ACF PRO). В бесплатном Meta Box этапы и FAQ услуги выводились как `<input type="group">`, а сохранение записи превращало их в JSON-строки: на странице пропадали этапы и FAQPage. Теперь без расширения группа становится textarea (пункты через пустую строку), `sky_field_raw()` возвращает тот же массив. Значения, сохранённые настоящей группой, показываются в textarea текстом и не теряются.
- Дефолтный шаблон Title `tax` был продающим (`%term% %city_in% — цены`) и действовал на рубрики блога: «Новости в Уфе — цены». Теперь `%term% | %site%`; продающий шаблон в примере конфига перенесён в `tax_service_cat`.
- Шаблоны description поддерживают `tax_<taxonomy>` и `archive_<post_type>`; в примере конфига они заданы только для услуг.
- Автоматический description склеивал заголовок со списком («Когда обращаться контрагент не платит…»). Если отрывка нет, текст собирается из контента без `the_content`: шорткоды не выполняются, заголовки и пункты списков отделяются точкой.
- Форма: превышение `forms.rate_limit` показывало «Проверьте имя и телефон». Новые сообщения `forms.messages.limit` и `forms.messages.expired` (устаревший nonce).

- **Редиректы: кириллические ключи карты не срабатывали.** Браузер присылает путь в `%XX`, а ключ `/услуги/ремонт/` с ним не совпадал. Ключи теперь приводятся к кодированному виду.
- **Редиректы: цель, равная источнику** (`'/a/' => '/a/'`), давала бесконечный 301 на 404. Такая запись теперь пропускается.
- Заявки (`sky_lead`) исключены из экспорта WordPress (`can_export => false`): в них телефоны и IP.

### Изменения поведения

- `analytics.webvisor` по умолчанию `false` (было `true`). Включайте явно, если есть согласие на cookie и политика.

### Новое

- Модуль `robots_txt`: `Clean-param` для Яндекса и необязательный `Disallow` в виртуальном `robots.txt` (`robots_txt.*`). Включён по умолчанию; физический `robots.txt` в корне сайта его перекрывает.
- `tests/run.php`: проверки редиректов, групп полей, токенов Title и robots.txt без WordPress. Запускается в CI (`composer test`).

- `cpt.post_types.<pt>.order`: `menu_order` или `title` — порядок записей в архиве типа и его таксономиях. При `menu_order` в редакторе включается поле «Порядок». В примере конфига услуги сортируются по `menu_order`.

## 2.0.0 — 2026-10-04

Стартер вместо закоммиченного сайта: ядро baseline, дочерняя тема Kadence, конфиг проекта, скрипты.

### Репозиторий

- Новая структура: `baseline/`, `theme-starter/`, `project/`, `bin/`, `docs/`.
- Ядро WordPress, плагины, `wp-config.php`, `conf/` и логи LocalWP убраны из индекса (на диске остались). История не переписывалась: **соли из `wp-config.php` остались в истории git — смените их на всех сайтах, где они использовались.**
- Пустой gitlink `themes/test` удалён из индекса.

### Ядро baseline

- Модули с интерфейсом `Module::register()`, пространство имён `Sky\Baseline`, автозагрузчик без Composer.
- Один конфиг `sky-config.php` (константа `SKY_CONFIG_PATH`, фильтр `sky_config_path`, по умолчанию `WP_CONTENT_DIR/sky-config.php`). Дефолты в `baseline/config/defaults.php`, слияние рекурсивное, списки заменяются целиком. Фильтр `sky_config`.
- Новые модули: `cpt`, `analytics`, `sitemap`, `cleanup`, `opengraph`, а также `robots` и `canonical` — выделены из `meta.php`.
- Защита от дублей: если активен Yoast, Rank Math, AIOSEO, SEOPress или The SEO Framework, модули `meta`, `schema`, `opengraph`, `canonical` выключаются, в админке выводится уведомление. `canonical` добавлен к списку из ТЗ: эти плагины тоже выводят canonical на архивах.
- Контакты и реквизиты берутся из `organization.*` конфига. Непустое значение в Customizer перекрывает конфиг: theme_mod привязан к теме, и при смене темы контакты больше не теряются.
- Мета-бокс «SEO» (`seo_title`, `seo_description`, `seo_noindex`) теперь регистрируется в Meta Box или ACF. Раньше поля читались, но нигде не регистрировались.
- Форма: шорткод `[sky_lead_form]`, time-trap (`forms.min_fill_seconds`), лимит заявок с одного IP (`forms.rate_limit`), отправка в Telegram (токен лучше задать константой `SKY_TG_TOKEN`), действие `sky_lead_saved`, мета `lead_url`. На страницах с `?lead=` — noindex.
- Метрика: счётчик, цель `lead` на `?lead=ok` (параметр затем убирается из адреса, обновление страницы не задваивает цель), цели на `tel:`, `mailto:` и мессенджеры, `window.skyGoal()` для темы.
- Записи с `seo_noindex` не попадают в `wp-sitemap.xml`, пользователи и `sky_lead` из карты исключены.

### Исправления

- `%city%` — именительный падеж, новый `%city_in%` — «в Уфе» (предлог настраивается). Пустой токен не оставляет двойных пробелов и висящих разделителей.
- `@id` у BreadcrumbList строится от URL текущего запроса. Раньше через `get_permalink()`, то есть на архивах это была ссылка на чужую запись.
- Тип организации (`schema.type`), `areaServed`, `sameAs`, ИНН, ОГРН/ОГРНИП, КПП, `priceRange` берутся из конфига. Пустые поля в JSON-LD больше не выводятся.
- Внешний URL в карте редиректов вёл в `/wp-admin/`: `wp_safe_redirect` не пускает на чужой хост. Теперь редирект идёт на заданный адрес.
- Перевод в нижний регистр портил `%XX`: `%D0%BF` превращался в `%d0%bf`, и каждый кириллический URL получал лишний 301.
- `//uslugi//a/` не склеивался: `parse_url` принимал начало пути за хост.
- `/Uslugi/` не редиректился: правила ЧПУ чувствительны к регистру, поэтому был 404, а на 404 нормализация не запускалась.
- Карта и префиксы сопоставляются с уже нормализованным путём: `/Old/Page/` — один хоп вместо двух. Префиксы проверяются от длинного к короткому.
- FAQ из компонента в контенте попадал в граф дважды: `get_the_excerpt()` для description прогоняет `the_content` ещё в `<head>`. Вопросы теперь дедуплицируются.
- Регистрация CPT перенесена на `init:0`: ACF собирает группы полей на `init:5`, и группа «SEO» с `post_types => '*'` не видела CPT.
- Страница блога (`is_home` при статической главной): Title, description, canonical, крошка «Блог» у записей.
- Title не экранировался: ядро выводит результат `pre_get_document_title` как есть.
- JSON-LD выводился с `JSON_UNESCAPED_SLASHES` без `JSON_HEX_TAG`: строка `</script>` в заголовке или ответе FAQ ломала страницу.
- Honeypot `company` браузеры автозаполняли, и живая заявка молча отбрасывалась. Поле переименовано в `sky_hp`.
- Несколько форм на странице давали одинаковые `id` полей.
- Телефон в форме проверяется на длину: не меньше 10 цифр.
- Конфликт `nofollow` (сайт закрыт от индексации) и `follow` в robots.

### Публичный API

Сохранены с прежними сигнатурами: `sky_opt()`, `sky_field()`, `sky_breadcrumbs()`, `sky_breadcrumbs_items()`, `sky_lead_form()`, `sky_title_templates()`, `sky_render()`, `sky_tax_post_type()`, `sky_redirect_map()`, `sky_redirect_prefixes()`, `sky_do_redirect()`. Фильтры `sky_title_templates`, `sky_schema_graph`, `sky_primary_taxonomy` работают как раньше.

Изменения поведения:

- `sky_redirect_map()` и `sky_redirect_prefixes()` читают `redirects.map` и `redirects.prefixes` из конфига (фильтры `sky_redirect_map`, `sky_redirect_prefixes`). Раньше карту правили прямо в коде ядра.
- `sky_breadcrumbs_items()` возвращает `[]`, если модуль `breadcrumbs` выключен. `sky_lead_form()` ничего не выводит, если выключен модуль `forms`.
- `sky_breadcrumbs()` принимает необязательный `$args` (`class`).
- `sky_opt( 'sky_*' )`: при пустом theme_mod возвращает `organization.*` из конфига, и только потом `$fallback`.
- `sky_primary_taxonomy`: начальное значение фильтра теперь берётся из `breadcrumbs.primary_taxonomy.<post_type>`.
- `sky_lead_form()`: новые аргументы `id` (якорь) и `button`.

Новое: `sky_config()`, `sky_field_raw()`, `sky_schema_add_faq()`, `sky_schema_add_node()`. Фильтры `sky_config`, `sky_config_path`, `sky_document_title`, `sky_meta_description`, `sky_canonical_url`, `sky_breadcrumbs_items`, `sky_schema_organization`, `sky_opengraph_tags`. Действие `sky_lead_saved`.

Удалены из публичного API: `sky_tokens()`, `sky_document_title()`, `sky_meta_description()`, `sky_archive_canonical()`, `sky_output_schema()`, `sky_handle_lead()`. Это были обработчики хуков, их логика теперь в методах модулей. Если тема вызывала их напрямую, замените:

- `sky_tokens()` → `sky_render()`;
- `sky_document_title()` → `wp_get_document_title()`.

### Дочерняя тема

- `theme-starter/`: токены в `assets/css/tokens.css`, на них же перенаправлены палитра и шрифты Kadence. Компоненты подключаются через `sky_component()`. Шаблоны-примеры `service`.
- Микроразметка Kadence отключена (`kadence_microdata`). Элемент «Крошки» в заголовке Kadence рендерит компонент baseline.

### Инструменты

- `bin/init.sh`, `bin/link-local.sh`. `bin/check-redirects.sh` принимает базовый URL аргументом, проверяет ожидаемый финальный адрес и завершается с ненулевым кодом при проблемах.
- `phpcs.xml` (WordPress-Extra + PHPCompatibilityWP 8.1+), GitHub Actions.
