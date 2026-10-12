<?php
/**
 * Главная (макеты Main, MainMobile).
 * Hero → услуги → виды лицензий (из записей) → квиз → этапы → почему мы → юридический ИИ →
 * нам доверяют → вопросы → заявка. Тексты — из макета; цифры, отзывы и имена — заглушки (DESIGN.md §8).
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_steps = [
	[
		'title' => 'Консультация',
		'text'  => 'Разбираем вашу ситуацию и определяем, какая лицензия нужна.',
	],
	[
		'title' => 'Договор',
		'text'  => 'Фиксируем стоимость, сроки и условия результата.',
	],
	[
		'title' => 'Документы',
		'text'  => 'Готовим весь пакет и проверяем соответствие требованиям.',
	],
	[
		'title' => 'Подача',
		'text'  => 'Подаём заявление и сопровождаем проверку регулятора.',
	],
	[
		'title' => 'Лицензия',
		'text'  => 'Передаём лицензию и инструкцию, как её не потерять.',
	],
];

$sky_reasons = [
	[
		'title' => 'Один исполнитель на весь путь',
		'text'  => 'От выбора лицензии до работы с проверками — без передачи между подрядчиками.',
	],
	[
		'title' => 'Работаем по всей России',
		'text'  => 'Дистанционно, через электронные сервисы: вам не нужно ехать в офис.',
	],
	[
		'title' => 'Цена и срок — в договоре',
		'text'  => 'Вы знаете итоговую стоимость до начала работы. [УТОЧНИТЬ условия гарантии]',
	],
	[
		'title' => 'Юристы с профильным опытом',
		'text'  => '[Описать опыт команды и работу в лицензирующих органах]',
	],
	[
		'title' => 'Поддержка после получения',
		'text'  => 'Продление, переоформление, ответы на запросы регулятора.',
	],
	[
		'title' => 'Лицензия + постоянный юрист',
		'text'  => 'Подключайте юриста на аутсорсе на тех же условиях и с общей историей дела.',
	],
];

$sky_faq = [
	[ 'Сколько стоит получение лицензии?', 'Стоимость зависит от вида лицензии, региона и готовности вашей компании. Точную цену фиксируем в договоре после бесплатной консультации. [УТОЧНИТЬ вилку цен]' ],
	[ 'Как долго оформляется лицензия?', 'Сроки определяются законом и регулятором, а также качеством подготовки документов. Ориентиры по каждому виду — на странице лицензии. [УТОЧНИТЬ]' ],
	[ 'Нужно ли приезжать в ваш офис?', 'Нет. Мы работаем дистанционно по всей России: документы подписываются электронно, подача — через официальные сервисы.' ],
	[ 'Что, если регулятор откажет?', 'Разберём причину, устраним замечания и подадим повторно. Условия указываем в договоре. [УТОЧНИТЬ политику]' ],
	[ 'Можно ли заказать только документы?', 'Да. Подготовим пакет документов или проверим ваш, а подачу вы выполните самостоятельно.' ],
];
$sky_faq = array_map(
	static fn( $f ) => [
		'question' => $f[0],
		'answer'   => $f[1],
	],
	$sky_faq
);

// FAQPage — до get_header(), чтобы JSON-LD попал в <head>.
if ( function_exists( 'sky_schema_add_faq' ) ) {
	sky_schema_add_faq( $sky_faq );
}

get_header();
?>
<div id="main" class="home">
	<?php
	get_template_part( 'template-parts/home/hero' );
	get_template_part( 'template-parts/home/services' );
	get_template_part( 'template-parts/home/catalog' );
	sky_component( 'quiz' );
	?>

	<section class="home-section home-process" aria-labelledby="process-title">
		<div class="sky-container">
			<h2 class="home-section__title" id="process-title">От заявки до лицензии — пять шагов</h2>
			<?php
			sky_component(
				'steps',
				[
					'items'   => $sky_steps,
					'variant' => 'row',
				]
			);
			?>
		</div>
	</section>

	<section class="home-section home-why" aria-labelledby="why-title">
		<div class="sky-container home-why__inner">
			<h2 class="home-section__title home-why__title" id="why-title">Почему работают с нами</h2>
			<?php sky_component( 'advantages', [ 'items' => $sky_reasons ] ); ?>
		</div>
	</section>

	<?php
	get_template_part( 'template-parts/home/ai' );
	get_template_part( 'template-parts/home/trust' );
	?>

	<section class="home-section home-faq" id="faq" aria-labelledby="faq-title">
		<div class="sky-container home-faq__inner">
			<h2 class="home-section__title" id="faq-title">Частые вопросы</h2>
			<?php
			sky_component(
				'faq',
				[
					'items'  => $sky_faq,
					'schema' => false,
					'class'  => 'faq--large',
				]
			);
			?>
		</div>
	</section>

	<?php
	sky_component(
		'cta',
		[
			'title'    => 'Расскажите о задаче — вернёмся с планом и ценой',
			'text'     => 'Консультация бесплатная и ни к чему не обязывает.',
			'source'   => 'Главная · форма внизу',
			'contacts' => true,
			'class'    => 'sky-cta--home',
		]
	);
	?>
</div>
<?php
get_footer();
