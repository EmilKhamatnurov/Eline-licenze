# Компоненты темы

```php
sky_component( 'hero', [ 'title' => 'Лицензирование', 'subtitle' => '…' ] );
$html = sky_get_component( 'faq', [ 'items' => $faq ] );
```

Каждый компонент — это файл `theme-starter/template-parts/components/<имя>.php`:

- дефолты аргументов объявлены в начале файла через `wp_parse_args()`;
- экранирование выполняется на выходе внутри компонента;
- компонент без данных ничего не выводит: пустой `items` — нет секции.

Аргументы любого компонента можно поправить фильтрами `sky_component_args` (`$args`, `$name`) и `sky_component_args_{name}`.

Новый компонент — новый файл в том же каталоге с тем же каркасом.

| Компонент | Аргументы | Примечание |
|---|---|---|
| `breadcrumbs` | `class` | Обёртка над `sky_breadcrumbs()` |
| `hero` | `title`, `subtitle`, `facts` (`[label, value]`), `image_id`, `button`, `button_url`, `phone`, `breadcrumbs` | H1 страницы. Картинка грузится с `fetchpriority=high`. |
| `steps` | `items` (`[title, text, term]`), `class` | Вертикальная лента с номерами; заголовок секции — в шаблоне |
| `price` | `items` (`[name, note, price, term]`), `button`, `button_url`, `class` | Прайс-строки с кнопкой «Заказать»; пустая цена — «от [ЦЕНА] ₽», срок — «[СРОК]». Название строки подставляется в форму (`data-lead-topic`) |
| `docs-list` | `title`, `items` (строки, `[name, note]` или текст с переводами строк), `class` | |
| `faq` | `items` (`[question, answer]` или `[q, a]`), `id`, `schema`, `class` | Аккордеон: `<button aria-expanded>`, первый открыт, без JS всё раскрыто. При `schema = true` вызывает `sky_schema_add_faq()` |
| `checklist` | `items` (строки или текст), `class` | Список с галочками — требования |
| `doc-tabs` | `id`, `groups` (`[label, items]`), `label`, `class` | Вкладки «юрлицо / ИП»: `role=tablist`, ← → Home End; без JS — списки с заголовками |
| `tariffs` | `items` (`[name, price_text, features]`), `featured` (1–3), `button`, `button_url`, `class` | Рекомендуемый — на `--brand-dark` с белой кнопкой |
| `cases-grid` | `title`, `posts` (WP_Post или ID) или `query` (аргументы WP_Query), `more` (`[text, url]`), `class` | По умолчанию 3 записи типа `case`. Миниатюры загружаются одним запросом. |
| `reviews` | `title`, `items` (`[author, text, rating, date, source, url]`), `class` | Без разметки Review: отзывы о себе на своём сайте поисковики в сниппет не берут |
| `cta` | `title`, `text`, `form`, `form_args`, `source`, `phone`, `id` | Нижний блок на `--brand` с формой `lead-form` (якорь `#form`) |
| `lead-form` | `variant` (`full`/`compact`), `id`, `title`, `text`, `title_tag`, `button`, `button_class`, `topic`, `phone_label`, `source`, `dark`, `class` | Форма заявки — см. ниже |

## Шаблон из компонентов

Пример — `theme-starter/single-service.php`:

```php
$faq = (array) sky_field_raw( 'faq', get_queried_object_id() );
sky_schema_add_faq( $faq );           // до get_header(): FAQPage в <head>

get_header();
while ( have_posts() ) :
	the_post();
	sky_component( 'hero', [ 'subtitle' => get_the_excerpt() ] );
	sky_component( 'steps', [ 'items' => (array) sky_field_raw( 'steps' ) ] );
	sky_component( 'faq', [ 'items' => $faq, 'schema' => false ] );
	sky_component( 'cta', [ 'source' => get_the_title() ] );
endwhile;
get_footer();
```

Для нового CPT скопируйте `single-service.php` и `archive-service.php` под его имя (`single-license.php`, `archive-license.php`) и опишите поля в `cpt.fields` конфига.

## Стили

Компоненты используют только переменные из `tokens.css`. Чтобы сменить бренд, правьте токены, а не `components.css`. Классы БЭМ-подобные: `sky-<компонент>__<элемент>`.

## Каркас: шапка, подвал, меню

Шапку и подвал Kadence заменяют `template-parts/layout/{topbar,header,footer}.php` через хуки `kadence_header` / `kadence_footer` (`inc/layout.php`). Стили — `assets/css/layout.css`, мобильное меню — `assets/js/header.js`.

| Место меню | Где выводится | Пока не назначено |
|---|---|---|
| `primary` | Шапка: основное меню | 6 пунктов из DESIGN.md §7 |
| `footer_licenses` | Подвал: «Лицензии» | Алкоголь, Медицина и фарма, Образование, Все виды |
| `footer_services` | Подвал: «Услуги» | Аутсорс, Документы, ИИ |

Меню назначаются в «Внешний вид → Меню». Глубина — 1 уровень. Активный пункт подсвечивается по классам `current-menu-item` / `current-menu-ancestor`.

Контакты — `sky_starter_org( 'phone' )`: Customizer «Контакты организации», затем `organization.*` конфига. Telegram, WhatsApp и текст верхней полосы — там же в Customizer (`sky_telegram`, `sky_whatsapp`, `sky_topbar_text`) или `organization.telegram` / `organization.whatsapp` в конфиге. Пустые значения выводятся заглушками `[ТЕЛЕФОН]`, `[ООО «НАЗВАНИЕ»]`, `ИНН [XXXXXXXXXX]` без ссылок. Кнопки «Получить консультацию» ведут на `#form` (фильтр `sky_starter_form_anchor`).

## Кнопки и поля (`assets/css/ui.css`)

```html
<a class="btn" href="#form">Получить консультацию</a>          <!-- primary -->
<button class="btn btn--secondary" type="button">Подробнее</button>
<a class="btn btn--light" href="…">Все виды лицензий</a>       <!-- на тёмном фоне -->
<button class="btn btn--gold" type="submit">…</button>          <!-- только hero главной -->
<!-- размеры: btn--sm (48 px, шапка), btn--block (на всю ширину) -->

<div class="field field--error">
	<label class="field__label" for="f-phone">Телефон</label>
	<input class="field__input" id="f-phone" type="tel" autocomplete="tel" aria-invalid="true" aria-describedby="f-phone-err">
	<p class="field__error" id="f-phone-err">Введите телефон полностью</p>
</div>
<p class="form-consent">Нажимая кнопку, вы соглашаетесь с <a href="…">политикой…</a></p>
```

Форма `sky_lead_form()` из baseline получает те же стили без правки разметки. Цвета кнопок заданы переменными `--btn-*`, поэтому `button:focus` Kadence их не перекрашивает. `.icon-btn` (`--tint`, `--brand`) — квадратная кнопка 44×44 с `aria-label`.

## Форма заявки (`lead-form`)

Один компонент для всех мест:

```php
// Hero главной: телефон + кнопка в строку, на тёмном фоне, золотая кнопка
sky_component( 'lead-form', [ 'variant' => 'compact', 'id' => 'hero-form', 'dark' => true, 'button_class' => 'btn--gold', 'phone_label' => 'Телефон для консультации' ] );

// Нижний блок и внутренние страницы: карточка на --paper (имя, телефон, «Что нужно»)
sky_component( 'lead-form', [ 'title' => 'Обсудим вашу задачу' ] );
```

`id` должен быть уникален на странице: кнопки «Получить консультацию» ведут на `#form`. Переход по такой ссылке мышью или с клавиатуры ставит фокус в первое поле формы; на сенсорном экране — только с клавиатуры, чтобы не выдвигать экранную клавиатуру.

Согласие стоит в сетке полей перед кнопкой: сначала отмечают, потом отправляют. В `compact` оно в разметке тоже перед кнопкой (порядок Tab), а на экране шире 480 px — строкой под полем и кнопкой. Nonce выводится без `id`: на странице бывает несколько форм.

**Как работает отправка.** Форма отправляет POST на `admin-post.php?action=sky_lead` — обработчик baseline: nonce, honeypot, time-trap (быстрее 3 с — бот), лимит по IP, согласие, сохранение в «Заявки», письмо, Telegram, действие `sky_lead_saved`. Тема (`inc/lead-form.php`) встаёт перед ним и:

- проверяет поля: имя от 2 символов (кроме `compact`), телефон — 10 цифр после +7 (принимает 8…, +7…, 9…), согласие;
- приводит телефон к `+7 (912) 345-67-89`; в `compact` без имени пишет «Не указано»;
- для AJAX (`sky_ajax=1`) отвечает JSON `{ status, message, errors }`; без JS — редирект `?lead=<статус>&lead_form=<id>&lead_fields=<поля>#<id>`, форма показывает те же ошибки.

**Состояния.** Ошибка поля — `.field--error`, `aria-invalid`, текст с иконкой, фокус на первом ошибочном поле. Отправка — кнопка заблокирована, «Отправляем…», `aria-busy`. Успех — сообщение `forms.messages.ok` вместо формы, фокус на нём, цель Метрики `analytics.goals.lead`. Общие ошибки сервера (`expired`, `limit`, сбой сервера — `server`) и обрыв связи (`network`) — блок над полями. `error` («проверьте имя и телефон») — только про поля, при ответе 5xx не показывается.

**Куда уходит заявка** (`project/sky-config.php`, раздел `forms`):

- всегда — «Заявки» в админке;
- почта — `forms.email_to` (пусто — e-mail администратора);
- Telegram — `forms.telegram.enabled = true`, токен и чат в `wp-config.php`: `SKY_TG_TOKEN`, `SKY_TG_CHAT_ID`;
- CRM — обработчик на `add_action( 'sky_lead_saved', fn( $lead_id, $data ) => … , 10, 2 )`.

Тексты ошибок — `sky_starter_lead_messages()`, общие для PHP и JS.

## Лицензии: тип записи, направления, поля

Тип `license` и таксономия `regulator` («Направления») описаны в `project/sky-config.php` (раздел `cpt`), адреса — DESIGN.md §7:

| Адрес | Что это |
|---|---|
| `/licenzirovanie/` | Каталог — архив типа `license` |
| `/licenzirovanie/<regulator>/` | Направление |
| `/licenzirovanie/<regulator>/<slug>/` | Лицензия; направление в адрес подставляет `inc/license.php` (первый термин, как в крошках) |

Лицензия без направления получает «Другие лицензии» (`drugie`). Адрес с чужим направлением — 301 на настоящий.

Данные для шаблонов — одним массивом:

```php
$l = sky_starter_license();          // текущая лицензия или sky_starter_license( $id )
$l['h1'];                            // поле «Заголовок H1», пусто — название
$l['price_label'];                   // «от 50 000 ₽» или «от [ЦЕНА] ₽»
$l['term_label'];                    // «от 30 дней» или «от [СРОК] дней»
$l['requirements'];                  // массив строк; так же docs_legal, docs_ip
$l['steps'];                         // [ [ title, term, text ], … ]
$l['tariffs'];                       // [ [ name, price, price_label, features[] ], … ] — не больше трёх
$l['tariff_featured'];               // 1–3, рекомендуемый тариф
$l['faq'];                           // [ [ question, answer ], … ] — подходит компоненту faq
$l['related'];                       // ID смежных лицензий
```

Поля-группы (этапы, тарифы, вопросы) на бесплатном Meta Box — textarea: пункты через пустую строку, строки пункта — подполя по порядку. В тарифе строка цены не должна быть пустой: число или `[ЦЕНА]`.

**Демо-данные** — 3 направления и 3 лицензии, весь текст в скобках, цены и сроки не заполнены:

```bash
wp eval-file bin/seed-licenses.php          # создать или обновить
wp eval-file bin/seed-licenses.php delete   # удалить демо-лицензии
```

В Local команду `wp` запускайте из **Open site shell**.

## Страница лицензии (`single-license.php`)

Порядок блоков — DESIGN.md §5; все данные — `sky_starter_license()`, блок без данных не выводится. Части — `template-parts/license/`: `hero` (крошки, H1, плитки, кнопки, форма расчёта `#calc`), `aside` и `catalog-nav` (направления из таксономии `regulator`, только с лицензиями, текущее выделено). Стили — `assets/css/license.css` (подключается только на лицензиях), интерактив — `assets/js/ui.js`.

| Ширина | Отличия |
|---|---|
| до 768 | две плитки, без кнопок в hero, чипы-якоря, панель «Позвонить / Заказать» |
| 768–1023 | третья плитка «Консультация бесплатно», кнопки в hero, чипы-якоря |
| от 1024 | боковой каталог вместо чипов |

Панель действий на телефоне (`template-parts/layout/mobile-bar.php`) выводится на всех страницах: «Позвонить» и «Написать» (Telegram/WhatsApp, иначе форма), на лицензии — «Заказать». Меняется фильтром `sky_starter_mobile_bar`.

## Каталог лицензий и разделы направлений

`archive-license.php` (`/licenzirovanie/`) и `taxonomy-regulator.php` (`/licenzirovanie/<regulator>/`) выводят одну часть — `template-parts/license/catalog.php`, отличие только в выбранном направлении. Стили — `assets/css/catalog.css`, поведение — `assets/js/catalog.js`.

- **Карточки.** Выводятся все лицензии одной страницей (`posts_per_page = -1`, `/page/2/` — 301 на первую); не подходящие под фильтр — с атрибутом `hidden`.
- **Направления.** Чипы — ссылки на разделы (`sky_starter_regulators()`: только с лицензиями, по алфавиту, «Другие лицензии» в конце). С JS — фильтр на месте, адрес раздела через `history.pushState`, H1, подзаголовок, Title и крошки — из `data-` атрибутов чипа (`sky_starter_catalog_view()`). Ctrl/Cmd-клик открывает раздел в новой вкладке.
- **Поиск.** GET-форма `?q=`: по всем словам запроса в названии и направлении, без учёта регистра и «ё». Без JS — кнопка «Найти» и фильтр на сервере; с JS — при вводе, `?q=` обновляется через `replaceState`. Страницы с `?q=` — `noindex` (`robots.noindex_query_args`), canonical — без параметров.
- **Счётчик** «Найдено лицензий: N»; экранному диктору — через `aria-live` после паузы в наборе.
- **Пустое состояние** — форма заявки (`lead-form`, `id="catalog-form"`), поле «Что нужно» заполняется поисковым запросом.
- **H1 и подзаголовок раздела.** H1 — «Лицензии: <направление>», подзаголовок — описание термина (Лицензии → Направления → описание), без описания — общая фраза. Раздел без лицензий — `noindex, follow`.

## Главная (`front-page.php`)

Части — `template-parts/home/`: `hero`, `services`, `catalog`, `ai`, `trust`; остальное — компоненты. Стили — `assets/css/home.css` (только на главной).

| Блок | Откуда данные |
|---|---|
| Hero | Тексты макета; форма — `lead-form` (`compact`, `#hero-form`); бланк — компонент `license-blank` |
| Услуги | Направления в карточке «Лицензирование» — первые четыре из `sky_starter_regulators()`; строки 02–04 — ссылки на страницы разделов |
| Виды лицензий | Вкладки — направления, строки — лицензии из записей с ценой и сроком из полей (тот же источник, что каталог) |
| Квиз | Модуль квиза (ниже) |
| Этапы | `steps` с `variant => row` |
| Почему мы | `advantages` |
| ИИ, доверие | Тексты макета; в «Нам доверяют» только заглушки |
| Вопросы | `faq` (`faq--large`), FAQPage — в `<head>` |
| Заявка | `cta` с `contacts => true`: телефон, e-mail, адрес, мессенджеры из `organization.*` или заглушки |

| Компонент | Аргументы | Примечание |
|---|---|---|
| `license-blank` | `class` | Бланк лицензии на HTML/CSS: рамка, гильош, поля, печать. На телефоне — сокращённый. Подпись «Иллюстрация. Не является документом.» |
| `advantages` | `items` (`[title, text]`), `class` | Номера 01–06, две колонки |
| `quiz` | `id`, `title`, `text`, `class` | Модуль квиза |

## Модуль квиза

`inc/quiz.php` (вопросы и данные) + `template-parts/components/quiz.php` + `assets/js/quiz.js` + `assets/css/quiz.css`. Вывод — `sky_component( 'quiz' )`; скрипт и стили компонент подключает сам (на главной стили — в `<head>`).

- Шаги: вид деятельности → форма бизнеса → результат и форма заявки; прогресс-полоса, «← Назад», «Пройти заново»; фокус переходит на вопрос шага.
- Варианты «вида деятельности» — фильтр `sky_starter_quiz`: `[ подпись, slug направления ]`. Вариант показывается, только если в направлении есть лицензии; в результате — эти лицензии со ссылками, ценой и сроком из полей. Вариант со slug `''` («Другое / не знаю») — всегда.
- Ответы попадают в заявку полем «Что нужно» (`message`): «Подбор лицензии: …; форма бизнеса: …; подходит: …». Источник заявки — «Главная · подбор лицензии».
- Без JS шагов нет: видна форма заявки с полем «Что нужно».

## Внутренние страницы

Страницы WordPress с шаблонами из `page-templates/` (выбираются в редакторе: «Шаблон»). Создать или обновить: `wp eval-file bin/seed-pages.php` — также назначает «Главную» и «Статьи» (страница записей) и заводит рубрики статей. Тексты — из макетов, в шаблонах; цены, сроки, имена — заглушки. Стили — `assets/css/pages.css`.

| Адрес | Шаблон | Блоки |
|---|---|---|
| `/yurist-na-autsorse/` | `outsourcing.php` | `page-hero` с формой, `features`, `compare`, `tariffs` с переключателем «тип компании», `steps` (`cards`), `faq`, `cta` |
| `/dokumenty/` | `documents.php` | `page-hero`, `tabs` + `price` по категориям, проверка документов (форма), `steps` (`cards`), `cta` |
| `/yuridicheskiy-ii/` | `legal-ai.php` | `page-hero` (`decor => rings`, плашка «Скоро»), `tabs` со сценариями (`template-parts/ai/case.php`), `steps` (`cards`), `features`, `cta` |
| `/o-kompanii/` | `about.php` | `page-hero`, `features`, `media-cards` (команда), `tabs` + `text-cards` (кейсы, отзывы), `requisites`, `cta-band` |
| `/kontakty/` | `contacts.php` | `page-hero`, карточки контактов, форма `#form` с темой обращения, место под карту |
| `/stati/`, рубрики | `home.php`, `category.php` → `template-parts/articles/list.php` | `page-hero`, чипы рубрик (ссылки), карточки записей, `cta-band` |

Новые компоненты:

| Компонент | Аргументы | Примечание |
|---|---|---|
| `page-hero` | `title`, `lead`, `badge`, `buttons` (`[текст, ссылка]`), `form` (аргументы `lead-form`), `decor`, `class` | Тёмный первый экран с крошками; первая кнопка белая, остальные — контур |
| `features` | `items` (`[title, text]`), `min`, `class` | Пункты с линией сверху |
| `compare` | `columns`, `rows`, `highlight`, `caption`, `class` | Таблица сравнения, прокрутка в своём контейнере |
| `tabs` | `id`, `label`, `panels` (`[label, html]`), `heading`, `class` | Вкладки-чипы (`ui.js`), без JS — все панели с заголовками |
| `text-cards` | `items` (`[tag, title, text]`), `class` | Карточки с меткой |
| `media-cards` | `items` (`[media, title, text]`), `min`, `height`, `class` | Пунктирные карточки с местом под фото (заглушки) |
| `cta-band` | `title`, `text`, `buttons`, `class` | Полоса-призыв на `--brand` |
| `requisites` | `class` | Реквизиты из `organization.*` (`legal_name`, `inn`, `kpp`, `ogrn`, `zip`/`city`/`street`, `bank`), пустые — заглушки |

Доработки: `steps` — вариант `cards`; `tariffs` — `who` у тарифа и `segments` (переключатель подсвечивает тариф, кнопки «Выбрать тариф» подставляют тариф в форму); `lead-form` — `topic_label`, `topic_placeholder`, `message_type` (`textarea`), `topics` (выпадающий список «Тема обращения», сервер добавляет тему в комментарий заявки).

Ссылки `#form` на странице без формы (`header.js`) ведут на форму в «Контактах» (`/kontakty/#form`).
