<?php
/**
 * Главная: «Четыре услуги — одна команда». Лицензирование — блок --brand с направлениями из таксономии,
 * остальные три — строки со стрелкой (DESIGN.md §5 «Услуги на главной»).
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_regs = array_slice( sky_starter_regulators(), 0, 4 );
$sky_rows = [
	[ '/yurist-na-autsorse/', 'Юрист на аутсорсе', 'Внешний юридический отдел за фиксированную абонентскую плату: договоры, споры, проверки.', false ],
	[ '/dokumenty/', 'Подготовка документов', 'Договоры, локальные акты и пакеты для лицензирующих органов — по вашим реквизитам.', false ],
	[ '/yuridicheskiy-ii/', 'Юридический ИИ', 'Автоматизация проверки договоров и работы с документами. Результат проверяет юрист.', true ],
];
?>
<section class="home-section home-services" id="services" aria-labelledby="services-title">
	<div class="sky-container">
		<h2 class="home-section__title" id="services-title">Четыре услуги — одна команда</h2>

		<div class="home-services__grid">
			<div class="service-main is-dark">
				<span class="service-main__num" aria-hidden="true">01</span>
				<h3 class="service-main__title">Лицензирование любых видов</h3>
				<p class="service-main__text">Получение, переоформление, продление и восстановление лицензий. Работаем с проверками и устраняем замечания регулятора.</p>
				<?php if ( $sky_regs ) : ?>
					<ul class="service-main__chips" aria-label="Направления">
						<?php foreach ( $sky_regs as $sky_reg ) : ?>
							<li><a href="<?php echo esc_url( get_term_link( $sky_reg ) ); ?>"><?php echo esc_html( $sky_reg->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<a class="btn btn--light service-main__btn" href="<?php echo esc_url( get_post_type_archive_link( 'license' ) ); ?>">Все виды лицензий</a>
			</div>

			<ul class="service-rows">
				<?php foreach ( $sky_rows as $sky_i => [ $sky_path, $sky_title, $sky_text, $sky_soon ] ) : ?>
					<li>
						<a class="service-row" href="<?php echo esc_url( home_url( $sky_path ) ); ?>">
							<span class="service-row__num" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $sky_i + 2 ) ); ?></span>
							<span class="service-row__body">
								<span class="service-row__title">
									<?php echo esc_html( $sky_title ); ?>
									<?php if ( $sky_soon ) : ?>
										<span class="badge">Скоро</span>
									<?php endif; ?>
								</span>
								<span class="service-row__text"><?php echo esc_html( $sky_text ); ?></span>
							</span>
							<span class="service-row__arrow" aria-hidden="true">→</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
