<?php
/**
 * Первый экран лицензии: крошки, H1, текст, плитки «Стоимость / Срок / Консультация», кнопки, форма расчёта.
 * Макеты: License (десктоп), LicenseMobile (на телефоне — две плитки, без кнопок, форма под текстом).
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_l = (array) ( $args['license'] ?? [] );
?>
<section class="page-hero license-hero is-dark">
	<div class="sky-container">
		<div class="page-hero__crumbs">
			<?php sky_component( 'breadcrumbs' ); ?>
		</div>

		<div class="license-hero__grid">
			<div class="license-hero__main">
				<h1 class="page-hero__title"><?php echo esc_html( $sky_l['h1'] ); ?></h1>
				<?php if ( '' !== $sky_l['lead'] ) : ?>
					<p class="page-hero__lead"><?php echo esc_html( $sky_l['lead'] ); ?></p>
				<?php endif; ?>

				<dl class="license-facts">
					<div class="license-facts__item">
						<dt>Стоимость</dt>
						<dd><?php echo esc_html( $sky_l['price_label'] ); ?></dd>
					</div>
					<div class="license-facts__item">
						<dt>Срок</dt>
						<dd><?php echo esc_html( $sky_l['term_label'] ); ?></dd>
					</div>
					<div class="license-facts__item license-facts__item--extra">
						<dt>Консультация</dt>
						<dd>бесплатно</dd>
					</div>
				</dl>

				<div class="license-hero__actions">
					<a class="btn btn--light" href="#calc">Заказать лицензию</a>
					<?php if ( $sky_l['tariffs'] ) : ?>
						<a class="btn btn--outline-light" href="#tariffs">Сравнить тарифы</a>
					<?php endif; ?>
				</div>
			</div>

			<div class="license-hero__form">
				<?php
				sky_component(
					'lead-form',
					[
						'id'        => 'calc',
						'title'     => 'Узнайте стоимость для вашего случая',
						'title_tag' => 'p',
						'topic'     => false,
						'button'    => 'Получить расчёт',
						'source'    => $sky_l['title'] . ' · расчёт стоимости',
						'class'     => 'request--raised',
					]
				);
				?>
			</div>
		</div>
	</div>
</section>
