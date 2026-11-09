<?php
/**
 * Страница лицензии (DESIGN.md §5 «Страница лицензии»; макеты License, LicenseMobile).
 * Порядок: крошки → H1, плитки, форма → каталог слева и контент: когда обязательна → требования →
 * документы (юрлицо / ИП) → этапы → тарифы → вопросы → смежные лицензии → нижняя форма.
 * Все блоки — из полей записи (sky_starter_license()); пустое поле — блока нет.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_l = sky_starter_license( get_queried_object_id() );

// FAQPage — до get_header(), чтобы JSON-LD попал в <head>.
if ( $sky_l['faq'] && function_exists( 'sky_schema_add_faq' ) ) {
	sky_schema_add_faq( $sky_l['faq'] );
}

$sky_docs = [
	[
		'label' => 'Юридическое лицо',
		'items' => $sky_l['docs_legal'],
	],
	[
		'label' => 'Индивидуальный предприниматель',
		'items' => $sky_l['docs_ip'],
	],
];

// Якоря для чипов на телефоне — только для блоков, которые есть.
$sky_jump = array_filter(
	[
		'req'     => $sky_l['requirements'] ? 'Требования' : '',
		'docs'    => ( $sky_l['docs_legal'] || $sky_l['docs_ip'] ) ? 'Документы' : '',
		'stages'  => $sky_l['steps'] ? 'Этапы' : '',
		'tariffs' => $sky_l['tariffs'] ? 'Цены' : '',
		'faq'     => $sky_l['faq'] ? 'Вопросы' : '',
	]
);

$sky_related = $sky_l['related'] ? get_posts(
	[
		'post_type'      => 'license',
		'post__in'       => $sky_l['related'],
		'orderby'        => 'post__in',
		'posts_per_page' => 12,
	]
) : [];

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div id="main" class="license">
		<?php get_template_part( 'template-parts/license/hero', null, [ 'license' => $sky_l ] ); ?>

		<?php if ( $sky_jump ) : ?>
			<nav class="license-jump" aria-label="Разделы страницы">
				<ul class="sky-container license-jump__list">
					<?php foreach ( $sky_jump as $sky_anchor => $sky_label ) : ?>
						<li><a class="license-jump__link" href="#<?php echo esc_attr( $sky_anchor ); ?>"><?php echo esc_html( $sky_label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>

		<div class="sky-container license__body">
			<?php get_template_part( 'template-parts/license/aside', null, [ 'current' => $sky_l['regulator'] ] ); ?>

			<div class="license__content">
				<?php if ( '' !== trim( $sky_l['intro'] ) ) : ?>
					<section class="license-section" id="when" aria-labelledby="when-title">
						<h2 class="license-section__title" id="when-title">Когда лицензия обязательна</h2>
						<div class="license-section__text"><?php echo wp_kses_post( wpautop( esc_html( $sky_l['intro'] ) ) ); ?></div>
					</section>
				<?php endif; ?>

				<?php if ( $sky_l['requirements'] ) : ?>
					<section class="license-section" id="req" aria-labelledby="req-title">
						<h2 class="license-section__title" id="req-title">Требования к соискателю</h2>
						<?php sky_component( 'checklist', [ 'items' => $sky_l['requirements'] ] ); ?>
					</section>
				<?php endif; ?>

				<?php if ( $sky_l['docs_legal'] || $sky_l['docs_ip'] ) : ?>
					<section class="license-section" id="docs" aria-labelledby="docs-title">
						<h2 class="license-section__title" id="docs-title">Документы</h2>
						<?php
						sky_component(
							'doc-tabs',
							[
								'id'     => 'docs',
								'groups' => $sky_docs,
							]
						);
						?>
					</section>
				<?php endif; ?>

				<?php if ( $sky_l['steps'] ) : ?>
					<section class="license-section" id="stages" aria-labelledby="stages-title">
						<h2 class="license-section__title" id="stages-title">Этапы получения</h2>
						<?php sky_component( 'steps', [ 'items' => $sky_l['steps'] ] ); ?>
					</section>
				<?php endif; ?>

				<?php if ( $sky_l['tariffs'] ) : ?>
					<section class="license-section" id="tariffs" aria-labelledby="tariffs-title">
						<h2 class="license-section__title" id="tariffs-title">Стоимость и тарифы</h2>
						<p class="license-section__note">Цена зависит от региона и состояния вашей компании. Окончательная сумма фиксируется в договоре.</p>
						<?php
						sky_component(
							'tariffs',
							[
								'items'    => $sky_l['tariffs'],
								'featured' => $sky_l['tariff_featured'],
							]
						);
						?>
						<p class="license-section__footnote">Государственная пошлина оплачивается отдельно. [УТОЧНИТЬ размер на дату публикации]</p>
					</section>
				<?php endif; ?>

				<?php if ( $sky_l['faq'] ) : ?>
					<section class="license-section" id="faq" aria-labelledby="faq-title">
						<h2 class="license-section__title" id="faq-title">Вопросы по этой лицензии</h2>
						<?php
						sky_component(
							'faq',
							[
								'items'  => $sky_l['faq'],
								'schema' => false,
							]
						);
						?>
					</section>
				<?php endif; ?>

				<?php if ( $sky_related ) : ?>
					<section class="license-section" aria-labelledby="related-title">
						<h2 class="license-section__title license-section__title--sm" id="related-title">Смежные лицензии</h2>
						<ul class="chips">
							<?php foreach ( $sky_related as $sky_post ) : ?>
								<li><a class="chips__link" href="<?php echo esc_url( get_permalink( $sky_post ) ); ?>"><?php echo esc_html( get_the_title( $sky_post ) ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>
			</div>
		</div>

		<?php
		sky_component(
			'cta',
			[
				'title'     => 'Получим лицензию, пока вы занимаетесь бизнесом',
				'text'      => 'Оставьте заявку — юрист позвонит в течение [XX] минут и назовёт точную цену.',
				'source'    => $sky_l['title'] . ' · форма внизу',
				// Поле «Что нужно» обязательно: в него «Заказать» из тарифов подставляет выбранный тариф.
				'form_args' => [
					'topic_placeholder' => 'Например, тариф или вопрос по документам',
					'button'            => 'Отправить заявку',
				],
			]
		);
		?>
	</div>
	<?php
endwhile;

get_footer();
