<?php
/**
 * Template Name: О компании
 *
 * Страница /o-kompanii/ (макет About). Команда, кейсы, отзывы — только заглушки: реальные данные даёт
 * клиент (DESIGN.md §8). Реквизиты — из organization.* (компонент requisites).
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_case   = [
	'tag'   => 'Кейс',
	'title' => '[Вид лицензии] для [тип клиента]',
	'text'  => '[Задача клиента, что сделали, за какой срок получили результат.]',
];
$sky_review = [
	'tag'   => 'Отзыв',
	'title' => '[КЛИЕНТ, ДОЛЖНОСТЬ]',
	'text'  => '[Текст отзыва. Лучше с видео или скан письма.]',
];
$sky_member = [
	'media' => '[Фото]',
	'title' => '[ИМЯ ФАМИЛИЯ]',
	'text'  => '[Должность, специализация]',
];

get_header();
?>
<div id="main" class="page">
	<?php
	sky_component(
		'page-hero',
		[
			'title' => 'Юристы, которые отвечают за результат',
			'lead'  => '[Короткий рассказ о компании: когда основана, чем занимается, чем отличается. Заполнить реальным текстом.]',
		]
	);
	?>

	<section class="section section--first" aria-labelledby="principles-title">
		<div class="sky-container">
			<h2 class="section__title" id="principles-title">Как мы работаем</h2>
			<?php
			sky_component(
				'features',
				[
					'items' => [
						[
							'title' => 'Результат в договоре',
							'text'  => 'Стоимость, сроки и условия фиксируем до начала работы.',
						],
						[
							'title' => 'Понятным языком',
							'text'  => 'Объясняем юридические вопросы без лишнего официоза.',
						],
						[
							'title' => 'Один ответственный',
							'text'  => 'За каждым клиентом закреплён юрист, который знает вашу ситуацию.',
						],
						[
							'title' => 'Работа дистанционно',
							'text'  => 'Документы, подписание и подача — онлайн, по всей России.',
						],
					],
				]
			);
			?>
		</div>
	</section>

	<section class="section section--tight" aria-labelledby="team-title">
		<div class="sky-container">
			<h2 class="section__title" id="team-title">Команда</h2>
			<?php
			sky_component(
				'media-cards',
				[
					'items'  => array_fill( 0, 4, $sky_member ),
					'min'    => 240,
					'height' => 200,
				]
			);
			?>
		</div>
	</section>

	<section class="section section--tint" aria-labelledby="cases-title">
		<div class="sky-container">
			<h2 class="section__title" id="cases-title">Кейсы и отзывы</h2>
			<?php
			sky_component(
				'tabs',
				[
					'id'     => 'about-proof',
					'label'  => 'Раздел',
					'panels' => [
						[
							'label' => 'Кейсы',
							'html'  => sky_get_component(
								'text-cards',
								[
									'items' => [
										$sky_case,
										$sky_case,
										array_merge(
											$sky_case,
											[
												'title' => '[Сопровождение проверки]',
												'text'  => '[Описание ситуации и итога. Заполнить реальным кейсом.]',
											]
										),
									],
								]
							),
						],
						[
							'label' => 'Отзывы',
							'html'  => sky_get_component( 'text-cards', [ 'items' => array_fill( 0, 3, $sky_review ) ] ),
						],
					],
				]
			);
			?>
		</div>
	</section>

	<section class="section" aria-labelledby="requisites-title">
		<div class="sky-container about-requisites">
			<div class="about-requisites__intro">
				<h2 class="section__title" id="requisites-title">Реквизиты</h2>
				<p class="section__lead">Полные данные для договора и выставления счетов.</p>
			</div>
			<?php sky_component( 'requisites' ); ?>
		</div>
	</section>

	<?php
	sky_component(
		'cta-band',
		[
			'title'   => 'Познакомимся? Обсудим вашу задачу',
			'buttons' => [ [ 'Связаться', home_url( '/kontakty/' ) ] ],
		]
	);
	?>
</div>
<?php
get_footer();
