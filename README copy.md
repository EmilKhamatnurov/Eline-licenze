# 7sky WP SEO Starter

Стартер для многостраничных SEO-сайтов на WordPress с приоритетом на Яндекс.

Состоит из трёх частей:

- **baseline** — mu-plugin с SEO-ядром. Под проект не правится и обновляется на всех сайтах одинаково.
- **theme-starter** — дочерняя тема Kadence: дизайн-токены, компоненты, шаблоны-примеры.
- **project/sky-config.php** — единственный файл, который заполняется под проект.

**Требования:** PHP 8.1+, WordPress 6.5+ (проверено на 7.1), Kadence 1.5+ (проверено на 1.5.2), Meta Box 5.x или ACF 6.x (бесплатных версий достаточно, см. «Поля»). Других зависимостей в рантайме нет, Composer нужен только для PHPCS.

## Быстрый старт

1. **Сайт.** Создайте сайт в LocalWP. Установите и активируйте Kadence и Meta Box (или ACF). В «Настройки → Постоянные ссылки» выберите «Название записи».
2. **Стартер.** Склонируйте репозиторий куда угодно (не внутрь сайта) и запустите:
   ```bash
   bin/init.sh advokat-ufa "Адвокат Уфа" ~/Local\ Sites/advokat-ufa
   ```
   Скрипт создаст `project/sky-config.php`, переименует тему и подключит baseline, тему и конфиг к сайту симлинками.
3. **Конфиг.** Заполните `project/sky-config.php`: организация и реквизиты, город и падеж, тип Schema, шаблоны Title, CPT и поля, ID Метрики. Секреты — в `wp-config.php` сайта:
   ```php
   define( 'SKY_TG_TOKEN', '123:ABC' );
   define( 'SKY_TG_CHAT_ID', '-100123' );
   ```
4. **Тема.** Активируйте тему `advokat-ufa`. Бренд задаётся в `theme-starter/assets/css/tokens.css`, страницы собираются из компонентов (см. [docs/components.md](docs/components.md)).
5. **Проверка.** Пройдите [чек-лист запуска](#чек-лист-запуска) и прогоните редиректы:
   ```bash
   cp bin/urls.example.txt bin/urls.txt   # перечислите старые адреса
   bin/check-redirects.sh http://advokat-ufa.local
   ```

Если симлинки недоступны (Windows без режима разработчика, хостинг), скопируйте файлы вручную:

- `baseline/` → `wp-content/mu-plugins/sky-baseline/`
- `baseline/sky-loader.php` → `wp-content/mu-plugins/sky-loader.php`
- `theme-starter/` → `wp-content/themes/<slug>/`
- `project/sky-config.php` → `wp-content/sky-config.php`

## Структура

```
baseline/                  mu-plugin (ядро)
  sky-baseline.php         точка входа: автозагрузчик, bootstrap
  sky-loader.php           копируется в mu-plugins/ (WP не заходит в подкаталоги сам)
  config/defaults.php      значения по умолчанию
  inc/functions.php        публичные функции для темы
  src/                     Sky\Baseline\…: Config, Fields, Organization, Seo\Context, Seo\Breadcrumbs, Modules\*
theme-starter/             дочерняя тема Kadence
  assets/css/tokens.css    дизайн-токены (бренд = этот файл)
  assets/css/components.css
  inc/components.php       sky_component()
  inc/kadence.php          крошки и микроразметка Kadence → baseline
  template-parts/components/
  single-service.php, archive-service.php, taxonomy-service_cat.php
project/
  sky-config.example.php   пример конфига с комментарием к каждому ключу
  sky-config.php           конфиг проекта (в .gitignore)
bin/
  init.sh, link-local.sh, check-redirects.sh, urls.example.txt
docs/components.md
```

## Конфиг

Путь к конфигу определяется так:

1. константа `SKY_CONFIG_PATH`;
2. затем фильтр `sky_config_path` (из другого mu-plugin);
3. по умолчанию `wp-content/sky-config.php`.

Файл возвращает массив и перекрывает [дефолты](baseline/config/defaults.php). Ассоциативные массивы сливаются рекурсивно, списки (`robots.noindex`, `same_as`…) заменяются целиком. Указывать нужно только то, что отличается от дефолтов. Если файла нет, работают дефолты, а в админке выводится предупреждение.

Чтение из кода: `sky_config( 'schema.type' )`. Итоговый массив можно поправить фильтром `sky_config` — из mu-plugin или темы (тема загружается до регистрации модулей).

Полный пример с комментарием к каждому ключу — [project/sky-config.example.php](project/sky-config.example.php).

## Модули

Каждый модуль включается флагом `modules.<имя>`. Выключенный модуль не вешает ни одного хука.

| Модуль | Что делает | Ключи конфига |
|---|---|---|
| `settings` | Секция Customizer «Контакты организации». Пустое поле — значение из конфига (показано как placeholder). | `organization.*` |
| `meta` | `<title>` по шаблонам, meta description, мета-бокс «SEO» (`seo_title`, `seo_description`, `seo_noindex`) | `title.templates`, `title.paged_suffix`, `city.*`, `meta.description_max`, `meta.description_templates`, `fields.seo_fields` |
| `robots` | `noindex, follow` для поиска, авторов, дат, вложений, 404, записей с `seo_noindex`, URL с заданными GET-параметрами | `robots.noindex`, `robots.noindex_query_args`, `robots.max_image_preview` |
| `canonical` | canonical на архивах, терминах, странице блога и страницах пагинации (для записей его выводит ядро); 301 `/page/1/` → `/` | `canonical.archives`, `canonical.redirect_page_one` |
| `breadcrumbs` | `sky_breadcrumbs()`, BreadcrumbList: архив или хаб типа, первичный термин с родителями, родительские страницы, блог | `breadcrumbs.*`, `cpt.post_types.<pt>.hub` |
| `schema` | JSON-LD: организация (тип, адрес, гео, часы, ИНН/ОГРН/КПП, `sameAs`, `areaServed`), BreadcrumbList с `@id` от текущего URL, FAQPage, узлы темы | `schema.type`, `schema.area_served`, `schema.price_range`, `schema.extra`, `organization.*` |
| `redirects` | 301 по карте и префиксам (только для 404), склейка `//`, нижний регистр без порчи `%XX`. Внешние цели разрешены. | `redirects.map`, `redirects.prefixes`, `redirects.lowercase`, `redirects.collapse_slashes` |
| `forms` | Форма заявки, CPT «Заявки», nonce, honeypot, time-trap, лимит по IP, согласие 152-ФЗ, письмо, Telegram, noindex на `?lead=` | `forms.*` |
| `cpt` | Типы записей, таксономии и мета-боксы из конфига (Meta Box; для ACF конвертируются). Порядок записей в архиве и разделах (`order`: `menu_order` или `title`). Правила ЧПУ сбрасываются сами при изменении конфига. | `cpt.post_types`, `cpt.taxonomies`, `cpt.fields` |
| `cleanup` | Убирает emoji, oEmbed discovery, RSD, WLW, REST-link, shortlink, generator, xmlrpc. Каждый пункт — свой флаг. | `cleanup.*` |
| `opengraph` | og:title/description/url/image/type/locale, twitter:card. Тексты те же, что в Title и description. | `opengraph.default_image`, `opengraph.locale` |
| `sitemap` | Из `wp-sitemap.xml` убраны пользователи, `sky_lead`, `post_format` и записи с `seo_noindex` | `sitemap.*` |
| `robots_txt` | Дополнения к виртуальному `robots.txt`: `Clean-param` для Яндекса (utm, yclid и др.) и при необходимости `Disallow`. Физический `robots.txt` в корне перекрывает модуль. | `robots_txt.*` |
| `analytics` | Яндекс Метрика (если задан ID), цели на заявку, `tel:`, `mailto:`, мессенджеры. Администраторы не считаются. Вебвизор выключен по умолчанию (`analytics.webvisor`). | `analytics.*` |

**SEO-плагины.** Если активен Yoast, Rank Math, AIOSEO, SEOPress или The SEO Framework, модули из `seo_plugins.modules` (по умолчанию `meta`, `schema`, `opengraph`, `canonical`) выключаются, в админке появляется уведомление. Отключается это флагом `seo_plugins.auto_disable`.

**Поля.** Используется первый установленный плагин из `fields.providers` (`meta-box`, `acf`). Если нет ни одного, `sky_field()` читает обычную post meta и не падает.

**Группы полей** (`type => 'group'`, например этапы и FAQ услуги) есть только в платных расширениях: Meta Box Group (или Meta Box AIO), а для клонируемой группы в ACF — repeater из ACF PRO. Без них ядро заменяет группу на textarea: пункты разделяются пустой строкой, строки пункта — подполя по порядку, последнее подполе может занимать несколько строк:

```
Сколько стоит консультация?
Первая консультация бесплатная.

Можно ли оплатить частями?
Да, по этапам.
```

`sky_field_raw()` в обоих режимах возвращает одинаковый массив, шаблоны менять не нужно. Если расширение поставить позже, значения, сохранённые текстом, продолжат читаться; если убрать — значения групп покажутся в textarea текстом.

### Шаблоны Title

Токены:

- `%site%`, `%tagline%`, `%phone%`;
- `%city%` («Уфа»), `%city_in%` («в Уфе»);
- `%title%` — запись, страница блога, автор, дата;
- `%archive%`, `%term%`, `%query%`.

Ключи шаблонов: `front`, `home`, `page`, `post`, `<post_type>`, `archive`, `archive_<post_type>`, `tax`, `tax_<taxonomy>`, `author`, `date`, `search`, `404`.

Дефолт `tax` нейтральный (`%term% | %site%`), потому что действует и на рубрики блога. Продающий шаблон задавайте для конкретной таксономии: `tax_service_cat`. Шаблоны description (`meta.description_templates`) выбираются так же: `tax_<taxonomy>` → `tax`, `archive_<post_type>` → `archive`.

Если у записи нет отрывка, description собирается из начала текста без выполнения шорткодов; заголовки и пункты списков отделяются точкой.

Если город выключен (`city.enabled = false`), токены пустые, а лишние пробелы и висящие разделители убираются. Поле записи `seo_title` перекрывает шаблон.

### FAQPage

Вызов `sky_schema_add_faq( [ [ 'question' => …, 'answer' => … ] ] )` добавляет вопросы в один узел FAQPage текущей страницы. Он не привязан к CPT.

- Вызов **до `get_header()`** — узел попадёт в основной граф в `<head>`. Так сделано в `single-service.php`.
- Вызов из компонента в теле страницы — узлы, добавленные после `wp_head`, ядро выведет вторым блоком JSON-LD в `wp_footer` с тем же `@id`.

В ответах допускаются `a`, `p`, `br`, списки, `b`/`strong`, `i`/`em`.

## Хелперы и фильтры

| Функция | Назначение |
|---|---|
| `sky_config( $key = null, $fallback = null )` | Значение конфига по пути через точку |
| `sky_opt( 'sky_phone', $fallback )` | Контакты и реквизиты: Customizer, затем `organization.*`, затем `$fallback` |
| `sky_field( $key, $id = null )` | Поле записи строкой (Meta Box или ACF) |
| `sky_field_raw( $key, $id = null )` | Поле как есть: группы, клоны, картинки |
| `sky_breadcrumbs( [ 'class' => 'crumbs' ] )`, `sky_breadcrumbs_items()` | Крошки HTML или массивом |
| `sky_lead_form( [ 'title', 'source', 'id', 'button' ] )` | Форма заявки; также шорткод `[sky_lead_form]` |
| `sky_schema_add_faq( $items )`, `sky_schema_add_node( $node )` | Узлы JSON-LD |
| `sky_render( $tpl, $tokens )` | Подстановка токенов в строку |
| `sky_title_templates()`, `sky_tax_post_type( $tax )` | Шаблоны Title; тип записи таксономии |
| `sky_redirect_map()`, `sky_redirect_prefixes()`, `sky_do_redirect( $to )` | Редиректы |
| `sky_component( $name, $args )`, `sky_get_component()` | Компоненты темы ([docs/components.md](docs/components.md)) |

**Фильтры:**

- конфиг: `sky_config`, `sky_config_path`;
- Title и description: `sky_title_templates`, `sky_document_title`, `sky_meta_description`;
- canonical и крошки: `sky_canonical_url`, `sky_breadcrumbs_items`, `sky_primary_taxonomy`;
- Schema и OG: `sky_schema_graph`, `sky_schema_organization`, `sky_opengraph_tags`;
- редиректы: `sky_redirect_map`, `sky_redirect_prefixes`;
- компоненты: `sky_component_args`, `sky_component_args_{name}`.

**Действие:** `sky_lead_saved( $lead_id, $data )` — заявка сохранена (для CRM).

**Цели из темы:** `window.skyGoal && window.skyGoal( 'имя_цели' )` — функция есть, только если счётчик выведен (у администраторов его нет).

## Тема

- **Токены.** Все цвета, шрифты, размеры, отступы и радиусы — в `tokens.css`. Палитра (`--global-palette1…9`) и шрифты Kadence перенаправлены на токены, поэтому настройки цветов в Customizer Kadence ни на что не влияют. `theme.json` дочерней темы даёт редактору шкалы шрифтов и отступов, которые тоже ссылаются на токены. Брейкпоинты в CSS-переменных — только справочно: в `@media` переменные не работают.
- **Kadence.** Микроразметка темы (`itemscope`) выключена фильтром `kadence_microdata`. Элемент «Крошки» в заголовке записи или архива Kadence рендерит компонент baseline: включение и позиция настраиваются в Customizer Kadence, а разметка и данные — наши. Проверено по коду Kadence 1.5.2.
- **CSS без сборщика.** Подключается через `wp_enqueue_style`, версия по `filemtime`. Сборщик не нужен, пока стилей немного. Если понадобится (PostCSS, вложенность, минификация), добавьте Vite, который собирает файлы в `assets/css/` с теми же именами, — PHP менять не придётся.

## Чек-лист запуска

**Конфиг и контент**

- [ ] `organization.*` заполнены: название, юрлицо, телефон, адрес, часы, ИНН, ОГРН. Для YMYL-тематик это обязательно — данные должны совпадать со страницей «Контакты» и реквизитами в подвале.
- [ ] `city.*`: падеж проверен в Title архива и термина.
- [ ] `schema.type` соответствует бизнесу (`LegalService`, `MedicalBusiness`…), `area_served` и `same_as` (Яндекс Карты, VK, 2ГИС) заполнены.
- [ ] Страница «Политика конфиденциальности» назначена в «Настройки → Конфиденциальность»: на неё ссылается согласие в форме. Текст соответствует 152-ФЗ.
- [ ] Для кириллических заголовков установлен транслитератор слагов (например, Cyr-To-Lat). Иначе URL будут в `%D0%…`.

**Индексация**

- [ ] «Настройки → Чтение»: снята галочка «Попросить поисковые системы не индексировать сайт».
- [ ] `robots.txt`: открыт, указан `Sitemap: https://site.ru/wp-sitemap.xml`, служебные разделы закрыты, есть `Clean-param` (проверить в Вебмастере → Инструменты → Анализ robots.txt). Физического `robots.txt` в корне нет — он перекрывает виртуальный.
- [ ] `wp-sitemap.xml` открывается, в нём нет пользователей, заявок и закрытых записей.
- [ ] Главное зеркало (https, с www или без) склеено 301 на сервере.
- [ ] Старые URL перечислены в `bin/urls.txt`, `bin/check-redirects.sh https://site.ru` проходит с кодом 0.

**Яндекс**

- [ ] Сайт добавлен в Вебмастер, права подтверждены, указан регион, загружен sitemap.
- [ ] Метрика: `analytics.metrika_id` задан. Цели `lead`, `tel_click`, `email_click`, `messenger_click` созданы как «JavaScript-событие» и срабатывают (проверка: `?_ym_debug=1`).
- [ ] Микроразметка проверена [валидатором Вебмастера](https://webmaster.yandex.ru/tools/microtest/) на главной, записи, архиве и странице с FAQ: без ошибок, по одному узлу организации и крошек.
- [ ] В Яндекс Бизнесе карточка организации совпадает с `organization.*`.

**Форма и безопасность**

- [ ] Тестовая заявка доходит в «Заявки», на почту и в Telegram. После отправки адрес `?lead=ok#form`, цель засчитана.
- [ ] Соли в `wp-config.php` уникальны для сайта ([генератор](https://api.wordpress.org/secret-key/1.1/salt/)).
- [ ] Если страницы кэшируются полностью дольше 12 часов: `forms.nonce = false` (останутся honeypot, time-trap и лимит по IP).
- [ ] На сайте нет SEO-плагина. Если он нужен, проверьте уведомление в админке о выключенных модулях.

## Разработка

```bash
composer install          # PHPCS + WordPress Coding Standards + PHPCompatibilityWP (только dev)
vendor/bin/phpcs          # правила — phpcs.xml
vendor/bin/phpcbf         # автоисправление
```

```bash
php tests/run.php         # редиректы, группы полей, токены Title, robots.txt — без WordPress
```

GitHub Actions на PHP 8.1 и 8.3 запускает `php -l`, `bash -n` для скриптов, `tests/run.php` и PHPCS. Тесты проверяют чистую логику на заглушках WordPress и не заменяют чек-лист запуска на живом сайте.

**Обновление baseline на сайтах:** замените каталог `mu-plugins/sky-baseline` (или подтяните репозиторий, если подключено симлинком). Конфиг проекта и тема не затрагиваются. Изменения — в [CHANGELOG.md](CHANGELOG.md).
