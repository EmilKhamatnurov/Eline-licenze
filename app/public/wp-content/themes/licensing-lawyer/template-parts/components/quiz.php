<?php
/**
 * Компонент «Квиз: подбор лицензии» — модуль, данные в inc/quiz.php.
 *
 *   sky_component( 'quiz' );
 *   sky_component( 'quiz', [ 'title' => '…', 'text' => '…', 'id' => 'quiz' ] );
 *
 * Шаги: вид деятельности → форма бизнеса → результат (лицензии направления из записей) и форма заявки.
 * Прогресс-полоса, «← Назад», «Пройти заново». Ответы уходят в заявку полем «Что нужно» (message).
 * Без JS шагов нет: видна форма заявки с полем «Что нужно».
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'id'    => 'quiz',
		'title' => 'Какая лицензия нужна именно вам?',
		'text'  => 'Три коротких вопроса. В ответ — предварительный перечень требований, ориентировочная стоимость и срок.',
		'class' => '',
	]
);

$sky_config = sky_starter_quiz_config();
$sky_data   = sky_starter_quiz_data();
$sky_id     = sanitize_html_class( $args['id'] );

// Без данных каталога единственным ответом квиза становится «Другое». Сразу предлагаем консультацию.
$sky_has_licenses = array_filter( $sky_data['activities'], static fn( $activity ) => ! empty( $activity['licenses'] ) );
if ( ! $sky_has_licenses ) :
	?>
	<section class="quiz-section quiz-section--consult" id="<?php echo esc_attr( $sky_id ); ?>" aria-labelledby="<?php echo esc_attr( $sky_id ); ?>-title">
		<div class="sky-container quiz-consult">
			<div class="quiz-consult__intro">
				<p class="quiz-consult__eyebrow">Начнём с вашего вопроса</p>
				<h2 id="<?php echo esc_attr( $sky_id ); ?>-title">Не знаете, какая лицензия нужна?</h2>
				<p>Расскажите о своей деятельности — поможем разобраться в требованиях и следующих шагах.</p>
				<a class="quiz-consult__link" href="<?php echo esc_url( sky_starter_form_anchor() ); ?>">Обсудить задачу <span aria-hidden="true">↗</span></a>
			</div>
			<div class="quiz-consult__aside">
				<p class="quiz-consult__aside-label">На консультации</p>
				<ol><li>Разберём вашу ситуацию</li><li>Подскажем, какие документы нужны</li><li>Обсудим стоимость и сроки</li></ol>
			</div>
		</div>
	</section>
	<?php
	return;
endif;

wp_enqueue_script( 'sky-quiz' );
wp_enqueue_style( 'sky-quiz' );
?>
<section class="quiz-section <?php echo esc_attr( $args['class'] ); ?>" id="<?php echo esc_attr( $sky_id ); ?>" aria-labelledby="<?php echo esc_attr( $sky_id ); ?>-title">
	<div class="sky-container quiz-section__inner">
		<div class="quiz-section__intro">
			<h2 class="quiz-section__title" id="<?php echo esc_attr( $sky_id ); ?>-title"><?php echo esc_html( $args['title'] ); ?></h2>
			<?php if ( $args['text'] ) : ?>
				<p class="quiz-section__text"><?php echo esc_html( $args['text'] ); ?></p>
			<?php endif; ?>
		</div>

		<div class="quiz" data-quiz>
			<div class="quiz__ui" data-quiz-ui hidden>
				<div class="quiz__progress">
					<div class="quiz__progress-labels" aria-hidden="true">
						<span data-quiz-label>Вопрос 1 из 3</span>
						<span data-quiz-percent>33%</span>
					</div>
					<div class="quiz__bar" role="progressbar" aria-label="Прогресс подбора" aria-valuemin="0" aria-valuemax="100" aria-valuenow="33" aria-valuetext="Вопрос 1 из 3">
						<span class="quiz__bar-fill"></span>
					</div>
				</div>

				<div class="quiz__step" data-quiz-step="1">
					<h3 class="quiz__question" tabindex="-1"><?php echo esc_html( $sky_config['activity_title'] ); ?></h3>
					<div class="quiz__options">
						<?php foreach ( $sky_data['activities'] as $sky_activity ) : ?>
							<button class="quiz__option" type="button" data-quiz-activity="<?php echo esc_attr( $sky_activity['id'] ); ?>"><?php echo esc_html( $sky_activity['label'] ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="quiz__step" data-quiz-step="2" hidden>
					<h3 class="quiz__question" tabindex="-1"><?php echo esc_html( $sky_config['form_title'] ); ?></h3>
					<div class="quiz__options">
						<?php foreach ( $sky_data['forms'] as $sky_form ) : ?>
							<button class="quiz__option" type="button" data-quiz-form="<?php echo esc_attr( $sky_form ); ?>"><?php echo esc_html( $sky_form ); ?></button>
						<?php endforeach; ?>
					</div>
					<button class="quiz__link" type="button" data-quiz-back>← Назад</button>
				</div>

				<div class="quiz__step" data-quiz-step="3" hidden>
					<h3 class="quiz__question" tabindex="-1"><?php echo esc_html( $sky_config['result_title'] ); ?></h3>
					<?php foreach ( $sky_data['activities'] as $sky_activity ) : ?>
						<div class="quiz__result" data-quiz-result="<?php echo esc_attr( $sky_activity['id'] ); ?>" hidden>
							<?php if ( $sky_activity['licenses'] ) : ?>
								<ul class="quiz__licenses">
									<?php foreach ( $sky_activity['licenses'] as $sky_license ) : ?>
										<li>
											<a class="quiz__license" href="<?php echo esc_url( $sky_license['url'] ); ?>"><?php echo esc_html( $sky_license['name'] ); ?></a>
											<span class="quiz__meta">Ориентировочно: <?php echo esc_html( $sky_license['meta'] ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
								<p class="quiz__note"><?php echo esc_html( $sky_data['note'] ); ?></p>
							<?php else : ?>
								<p class="quiz__note"><?php echo esc_html( $sky_data['fallback'] ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
					<div data-quiz-slot></div>
					<button class="quiz__link" type="button" data-quiz-restart>Пройти заново</button>
				</div>
			</div>

			<p class="quiz__fallback" data-quiz-fallback>Опишите, чем занимается ваш бизнес, — подберём лицензию и назовём требования, цену и срок.</p>

			<div class="quiz__form" data-quiz-form-wrap>
				<?php
				sky_component(
					'lead-form',
					[
						'id'     => $sky_id . '-form',
						'button' => 'Получить расчёт и список документов',
						'source' => 'Главная · подбор лицензии',
						'class'  => 'request--plain',
					]
				);
				?>
			</div>
		</div>
	</div>
</section>
