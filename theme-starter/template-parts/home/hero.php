<?php
/**
 * Главная: первый экран — H1, текст, форма «телефон + кнопка», три факта, бланк лицензии.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_facts = [
	[ 'Цена и срок', 'фиксируем в договоре' ],
	[ 'Разбор ситуации', 'бесплатно' ],
	[ 'Перезвоним', 'за [XX] минут' ],
];
?>
<section class="home-hero is-dark">
	<div class="sky-container home-hero__inner">
		<div class="home-hero__main">
			<h1 class="home-hero__title t-display">Лицензии под ключ. Юрист — на постоянной основе.</h1>
			<p class="home-hero__lead">Оформляем любые лицензии, готовим документы и ведём вашу компанию как внешний юридический отдел. Дистанционно, по всей России.</p>

			<div class="home-hero__form">
				<?php
				sky_component(
					'lead-form',
					[
						'variant'      => 'compact',
						'id'           => 'hero-form',
						'dark'         => true,
						'button_class' => 'btn--gold',
						'phone_label'  => 'Телефон для консультации',
						'source'       => 'Главная · первый экран',
					]
				);
				?>
			</div>

			<ul class="home-facts">
				<?php foreach ( $sky_facts as [ $sky_title, $sky_text ] ) : ?>
					<li class="home-facts__item">
						<svg class="home-facts__icon" aria-hidden="true" focusable="false" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
						<span><strong><?php echo esc_html( $sky_title ); ?></strong> <span class="home-facts__text"><?php echo esc_html( $sky_text ); ?></span></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="home-hero__blank">
			<?php sky_component( 'license-blank' ); ?>
		</div>
	</div>
</section>
