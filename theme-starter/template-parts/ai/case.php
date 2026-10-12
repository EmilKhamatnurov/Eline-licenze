<?php
/**
 * Сценарий юридического ИИ (вкладка на странице «Юридический ИИ»): описание и иллюстрация
 * «вход → результат ИИ → решение юриста». Иллюстрация подписана: это не реальный интерфейс.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_case = (array) ( $args['case'] ?? [] );
?>
<div class="ai-case">
	<div class="ai-case__about">
		<h4 class="ai-case__title"><?php echo esc_html( $sky_case['title'] ); ?></h4>
		<p class="ai-case__text"><?php echo esc_html( $sky_case['text'] ); ?></p>
		<ul class="ai-case__points">
			<?php foreach ( (array) $sky_case['points'] as $sky_point ) : ?>
				<li><?php echo esc_html( $sky_point ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<figure class="ai-case__demo">
		<figcaption class="ai-case__caption">Иллюстрация сценария · не реальный интерфейс</figcaption>
		<div class="ai-case__box">
			<p class="ai-case__box-label">Вход</p>
			<p class="ai-case__box-text"><?php echo esc_html( $sky_case['input'] ); ?></p>
		</div>
		<div class="ai-case__box ai-case__box--result">
			<p class="ai-case__box-label">Результат ИИ</p>
			<p class="ai-case__box-text"><?php echo esc_html( $sky_case['output'] ); ?></p>
		</div>
		<p class="ai-case__next">Далее: проверка и решение юриста</p>
	</figure>
</div>
