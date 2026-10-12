<?php
/**
 * Главная: «Нам доверяют» — места под фото руководителя, видеоотзыв и логотипы клиентов.
 * Только заглушки: реальные имена, отзывы и цифры даёт клиент (DESIGN.md §8).
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="home-section home-trust" aria-labelledby="trust-title">
	<div class="sky-container">
		<h2 class="home-section__title" id="trust-title">Нам доверяют</h2>
		<?php
		sky_component(
			'media-cards',
			[
				'items' => [
					[
						'media' => '[Фото руководителя]',
						'title' => '[ИМЯ ФАМИЛИЯ], руководитель',
						'text'  => '[Опыт, регалии, работа в лицензирующих органах]',
					],
					[
						'media' => '[Видеоотзыв клиента]',
						'title' => '[КЛИЕНТ, ОТРАСЛЬ]',
						'text'  => '[Какую лицензию получили и за какой срок]',
					],
					[
						'media' => '[Логотипы клиентов]',
						'title' => '[XX] лицензий получено',
						'text'  => '[Заменить реальными данными компании]',
					],
				],
			]
		);
		?>
	</div>
</section>
