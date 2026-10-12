<?php
/**
 * Пример: архив услуг и разделы услуг (taxonomy-service_cat.php подключает этот файл).
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'SKY_BASELINE_VERSION' ) ) {
	require get_template_directory() . '/index.php';
	return;
}

get_header();

if ( function_exists( 'Kadence\kadence' ) ) {
	Kadence\kadence()->print_styles( 'kadence-content' );
}

if ( is_tax() ) {
	$sky_title = single_term_title( '', false );
	$sky_intro = term_description();
} else {
	$sky_title = post_type_archive_title( '', false );
	$sky_intro = get_the_post_type_description();
}

sky_component(
	'hero',
	[
		'title'    => $sky_title,
		'subtitle' => wp_strip_all_tags( (string) $sky_intro ),
	]
);

global $wp_query;
if ( have_posts() ) {
	sky_component(
		'cases-grid',
		[
			'title' => '',
			'posts' => $wp_query->posts,
		]
	);
	?>
	<div class="sky-container">
		<?php the_posts_pagination(); ?>
	</div>
	<?php
}

sky_component( 'cta', [ 'source' => $sky_title ] );

get_footer();
