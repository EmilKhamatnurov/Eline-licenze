<?php
/**
 * Template Name: Документы
 *
 * Страница /dokumenty/ (макет Documents). Цены и сроки — «[ЦЕНА]», «[СРОК]» (DESIGN.md §8).
 * Подстраницы категорий (/dokumenty/<категория>/) пока не делаем — категории здесь вкладками.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_categories = [
	'Договоры'           => [
		[ 'Договор поставки', 'Для регулярных поставок товаров' ],
		[ 'Договор оказания услуг', 'С учётом специфики вашей деятельности' ],
		[ 'Договор аренды', 'Помещения, оборудование' ],
		[ 'Договор подряда', 'С приложениями и актами' ],
	],
	'Для лицензирования' => [
		[ 'Пакет документов для лицензии', 'Под выбранный вид лицензии и ваш регион' ],
		[ 'Заявление в лицензирующий орган', 'С проверкой комплектности' ],
		[ 'Ответ на замечания регулятора', 'Если пришёл отказ или запрос' ],
	],
	'Локальные акты'     => [
		[ 'Положения и регламенты', 'Внутренние правила компании' ],
		[ 'Должностные инструкции', 'Под структуру вашей организации' ],
		[ 'Политика обработки персональных данных', 'Для сайта и внутреннего использования' ],
	],
	'Для проверок'       => [
		[ 'Подготовка к проверке', 'Чек-лист и комплект документов' ],
		[ 'Ответ на предписание', 'Письменная позиция компании' ],
		[ 'Претензия / ответ на претензию', 'Досудебное урегулирование' ],
	],
];

$sky_panels = [];
foreach ( $sky_categories as $sky_label => $sky_rows ) {
	$sky_panels[] = [
		'label' => $sky_label,
		'html'  => sky_get_component(
			'price',
			[
				'items' => array_map(
					static fn( $r ) => [
						'name' => $r[0],
						'note' => $r[1],
					],
					$sky_rows
				),
			]
		),
	];
}

get_header();
?>
<div id="main" class="page">
	<?php
	sky_component(
		'page-hero',
		[
			'title'   => 'Юридические документы под вашу компанию',
			'lead'    => 'Договоры, локальные акты и пакеты для лицензирования и проверок. Составляем по вашим реквизитам и проверяем юристом, а не подставляем шаблон.',
			'buttons' => [ [ 'Выбрать документ', '#catalog' ] ],
		]
	);
	?>

	<section class="section section--first" id="catalog" aria-labelledby="catalog-title">
		<div class="sky-container">
			<h2 class="section__title" id="catalog-title">Что мы готовим</h2>
			<?php
			sky_component(
				'tabs',
				[
					'id'     => 'docs-cat',
					'label'  => 'Категории документов',
					'panels' => $sky_panels,
				]
			);
			?>
			<p class="section__note">Нужного нет в списке? <a href="<?php echo esc_url( sky_starter_form_anchor() ); ?>">Опишите задачу</a> — подготовим индивидуально.</p>
		</div>
	</section>

	<section class="section section--flush-top" aria-labelledby="check-title">
		<div class="sky-container">
			<div class="doc-check">
				<div class="doc-check__text">
					<h2 class="doc-check__title" id="check-title">Уже есть документы? Проверим</h2>
					<p class="doc-check__lead">Пришлите договор или пакет для лицензирования — найдём риски и скажем, что исправить.</p>
					<p class="doc-check__note">Оставьте телефон и опишите документ. Сам файл юрист попросит после звонка.</p>
				</div>
				<div class="doc-check__form">
					<?php
					sky_component(
						'lead-form',
						[
							'id'                => 'check',
							'topic_label'       => 'Какой документ проверить',
							'topic_placeholder' => 'Например, договор аренды на 12 страниц',
							'button'            => 'Отправить на проверку',
							'source'            => 'Документы · проверка документов',
							'class'             => 'request--plain',
						]
					);
					?>
				</div>
			</div>
		</div>
	</section>

	<section class="section section--flush-top" aria-labelledby="work-title">
		<div class="sky-container">
			<h2 class="section__title" id="work-title">Как мы работаем с документами</h2>
			<?php
			sky_component(
				'steps',
				[
					'variant' => 'cards',
					'items'   => [
						[
							'title' => 'Задача',
							'text'  => 'Вы описываете ситуацию и присылаете исходные данные.',
						],
						[
							'title' => 'Составление',
							'text'  => 'Юрист готовит документ под ваши реквизиты и цели.',
						],
						[
							'title' => 'Согласование',
							'text'  => 'Вносим правки до результата, который вас устраивает. [УТОЧНИТЬ число правок]',
						],
						[
							'title' => 'Передача',
							'text'  => 'Отдаём документ в нужном формате и объясняем, как им пользоваться.',
						],
					],
				]
			);
			?>
		</div>
	</section>

	<?php
	sky_component(
		'cta',
		[
			'title'     => 'Какой документ вам нужен?',
			'text'      => 'Назовите задачу — назовём цену и срок.',
			'source'    => 'Документы · форма внизу',
			'form_args' => [
				'topic_label'       => 'Какой документ нужен (необязательно)',
				'topic_placeholder' => 'Например, договор поставки',
				'button'            => 'Отправить заявку',
			],
		]
	);
	?>
</div>
<?php
get_footer();
