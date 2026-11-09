<?php
/**
 * Страница записей /stati/ — разметка в template-parts/articles/list.php.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

get_header();
echo '<div id="main" class="page">';
get_template_part( 'template-parts/articles/list' );
echo '</div>';
get_footer();
