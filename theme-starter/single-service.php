<?php
/**
 * Пример: страница услуги, собранная из компонентов.
 * Поля price_from, term, steps, docs, faq описаны в project/sky-config.example.php.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'SKY_BASELINE_VERSION' ) ) {
	require get_template_directory() . '/single.php';
	return;
}

$sky_id  = get_queried_object_id();
$sky_faq = (array) sky_field_raw( 'faq', $sky_id );

// До get_header(): FAQPage попадёт в основной граф JSON-LD в <head>.
sky_schema_add_faq( $sky_faq );

get_header();

if ( function_exists( 'Kadence\kadence' ) ) {
	Kadence\kadence()->print_styles( 'kadence-content' );
}

while ( have_posts() ) :
	the_post();
	$sky_price = sky_field( 'price_from' );

	sky_component(
		'hero',
		[
			'subtitle' => has_excerpt() ? get_the_excerpt() : '',
			'image_id' => (int) get_post_thumbnail_id(),
			'facts'    => [
				[
					'label' => 'Стоимость',
					'value' => is_numeric( $sky_price ) ? 'от ' . number_format_i18n( (float) $sky_price ) . ' ₽' : $sky_price,
				],
				[
					'label' => 'Срок',
					'value' => sky_field( 'term' ),
				],
			],
		]
	);
	?>
	<section class="sky-section">
		<div class="sky-container sky-container--narrow entry-content">
			<?php the_content(); ?>
		</div>
	</section>
	<?php
	sky_component( 'steps', [ 'items' => (array) sky_field_raw( 'steps' ) ] );
	sky_component( 'docs-list', [ 'items' => sky_field( 'docs' ) ] );
	sky_component(
		'faq',
		[
			'items'  => $sky_faq,
			'schema' => false,
		]
	);
	sky_component(
		'cases-grid',
		[
			'title' => 'Другие услуги',
			'query' => [ 'post_type' => 'service' ],
		]
	);
	sky_component( 'cta', [ 'source' => get_the_title() ] );
endwhile;

get_footer();
