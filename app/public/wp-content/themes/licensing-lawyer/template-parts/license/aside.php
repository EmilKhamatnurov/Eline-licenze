<?php
/**
 * Боковая колонка лицензии (от 1024 px): каталог направлений и плашка «Не уверены…».
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;
?>
<aside class="license__aside" aria-label="Каталог и консультация">
	<?php get_template_part( 'template-parts/license/catalog-nav', null, [ 'current' => $args['current'] ?? null ] ); ?>

	<div class="license-help">
		<p class="license-help__title">Не уверены, что нужна лицензия?</p>
		<p class="license-help__text">Проверим бесплатно и скажем, нужна ли она вашему виду деятельности.</p>
		<a class="btn btn--sm btn--block" href="<?php echo esc_url( sky_starter_form_anchor() ); ?>">Задать вопрос юристу</a>
	</div>
</aside>
