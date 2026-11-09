<?php
/**
 * Компонент «Сетка кейсов» (или любых записей).
 *
 * Аргументы:
 *   title — заголовок секции;
 *   posts — массив WP_Post или ID; если пусто — берётся query;
 *   query — аргументы WP_Query (по умолчанию 3 последних записи типа case);
 *   more  — [ 'text' => 'Все кейсы', 'url' => … ];
 *   class — доп. класс секции.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'title' => 'Кейсы',
		'posts' => [],
		'query' => [],
		'more'  => [],
		'class' => '',
	]
);

if ( $args['posts'] ) {
	$sky_query        = new WP_Query();
	$sky_query->posts = array_values( array_filter( array_map( 'get_post', (array) $args['posts'] ) ) );
	$sky_posts        = $sky_query->posts;
} else {
	$sky_query = new WP_Query(
		wp_parse_args(
			(array) $args['query'],
			[
				'post_type'           => 'case',
				'posts_per_page'      => 3,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'post__not_in'        => is_singular() ? [ get_queried_object_id() ] : [],
			]
		)
	);
	$sky_posts = $sky_query->posts;
}
if ( ! $sky_posts ) {
	return;
}

// Миниатюры одним запросом, а не по одному на карточку.
update_post_thumbnail_cache( $sky_query );
?>
<section class="sky-section sky-cases <?php echo esc_attr( $args['class'] ); ?>">
	<div class="sky-container">
		<?php if ( $args['title'] ) : ?>
			<h2 class="sky-section__title"><?php echo esc_html( $args['title'] ); ?></h2>
		<?php endif; ?>
		<div class="sky-cards">
			<?php foreach ( $sky_posts as $sky_post ) : ?>
				<article class="sky-card">
					<?php if ( has_post_thumbnail( $sky_post ) ) : ?>
						<a class="sky-card__image" href="<?php echo esc_url( get_permalink( $sky_post ) ); ?>" tabindex="-1" aria-hidden="true">
							<?php echo get_the_post_thumbnail( $sky_post, 'medium_large', [ 'loading' => 'lazy' ] ); ?>
						</a>
					<?php endif; ?>
					<div class="sky-card__body">
						<h3 class="sky-card__title"><a href="<?php echo esc_url( get_permalink( $sky_post ) ); ?>"><?php echo esc_html( get_the_title( $sky_post ) ); ?></a></h3>
						<?php if ( has_excerpt( $sky_post ) ) : ?>
							<p class="sky-card__text"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt( $sky_post ) ) ); ?></p>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<?php if ( ! empty( $args['more']['url'] ) ) : ?>
			<p><a class="sky-btn sky-btn--ghost" href="<?php echo esc_url( $args['more']['url'] ); ?>"><?php echo esc_html( $args['more']['text'] ?? 'Смотреть все' ); ?></a></p>
		<?php endif; ?>
	</div>
</section>
