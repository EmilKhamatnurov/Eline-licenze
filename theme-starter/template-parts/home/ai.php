<?php
/**
 * Главная: блок «Юридический ИИ» (DESIGN.md §5): тёмная секция, плашка «Скоро», строки с линиями.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_items = [
	[ 'Проверка договоров', 'Ищет рискованные условия и расхождения с вашими стандартами.' ],
	[ 'Работа с документами', 'Составляет типовые документы и сводит данные из вложений.' ],
	[ 'База знаний компании', 'Быстрые ответы по вашим внутренним регламентам и практике.' ],
	[ 'Безопасность данных', 'Обсудим требования к конфиденциальности при выборе инструментов.' ],
];
?>
<section class="home-section home-ai is-dark" id="ai" aria-labelledby="ai-title">
	<div class="sky-container home-ai__inner">
		<div class="home-ai__intro">
			<p class="badge badge--gold">Скоро</p>
			<h2 class="home-section__title" id="ai-title">Юридический ИИ в вашей компании</h2>
			<p class="home-ai__text">Подберём и внедрим ИИ-инструменты под вашу юридическую работу: проверка договоров, поиск рисков, типовые документы. Результат всегда проверяет юрист.</p>
			<a class="btn btn--light home-ai__btn" href="<?php echo esc_url( home_url( '/yuridicheskiy-ii/' ) ); ?>">Обсудить внедрение</a>
		</div>
		<ul class="home-ai__list">
			<?php foreach ( $sky_items as [ $sky_title, $sky_text ] ) : ?>
				<li class="home-ai__item">
					<h3 class="home-ai__item-title"><?php echo esc_html( $sky_title ); ?></h3>
					<p class="home-ai__item-text"><?php echo esc_html( $sky_text ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
