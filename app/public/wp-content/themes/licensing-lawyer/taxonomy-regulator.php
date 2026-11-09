<?php
/**
 * Раздел направления /licenzirovanie/<regulator>/ — тот же каталог с выбранным направлением.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_term = get_queried_object();

get_header();
get_template_part( 'template-parts/license/catalog', null, [ 'term' => $sky_term instanceof WP_Term ? $sky_term : null ] );
get_footer();
