<?php
/**
 * Статьи (макет Articles): темы — рубрики (ссылки), карточки записей, полоса-призыв.
 * Общая часть для home.php (страница записей /stati/) и category.php (рубрика).
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_current = is_category() ? get_queried_object() : null;
$sky_blog    = (int) get_option( 'page_for_posts' );
$sky_all_url = $sky_blog ? (string) get_permalink( $sky_blog ) : home_url( '/' );
$sky_topics  = get_categories(
	[
		'hide_empty' => true,
		'exclude'    => [ (int) get_option( 'default_category' ) ], // «Без рубрики» — не тема.
	]
);

/**
 * Минуты чтения: 180 слов в минуту, не меньше одной.
 *
 * @param int $post_id Запись.
 */
$sky_read = static function ( int $post_id ): int {
	$words = count( preg_split( '/\s+/u', trim( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) ), -1, PREG_SPLIT_NO_EMPTY ) );
	return max( 1, (int) ceil( $words / 180 ) );
};

sky_component(
	'page-hero',
	[
		'title' => $sky_current instanceof WP_Term ? 'Статьи: ' . $sky_current->name : 'Статьи и новости',
		'lead'  => $sky_current instanceof WP_Term && '' !== trim( (string) $sky_current->description )
			? wp_strip_all_tags( $sky_current->description )
			: 'Разбираем требования регуляторов и изменения в законодательстве простым языком.',
	]
);
?>
<section class="section section--first" aria-label="Статьи">
	<div class="sky-container">
		<?php if ( $sky_topics ) : ?>
			<nav class="filter-chips" aria-label="Темы">
				<ul class="filter-chips__list">
					<li><a class="filter-chips__link" href="<?php echo esc_url( $sky_all_url ); ?>"<?php echo $sky_current ? '' : ' aria-current="page"'; ?>>Все</a></li>
					<?php foreach ( $sky_topics as $sky_topic ) : ?>
						<li><a class="filter-chips__link" href="<?php echo esc_url( get_category_link( $sky_topic ) ); ?>"<?php echo $sky_current instanceof WP_Term && $sky_current->term_id === $sky_topic->term_id ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $sky_topic->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<ul class="post-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					$sky_cat = get_the_category();
					$sky_cat = $sky_cat ? $sky_cat[0] : null;
					?>
					<li class="post-card">
						<div class="post-card__cover">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'medium_large', [ 'alt' => '' ] ); ?>
							<?php else : ?>
								<span>[Обложка]</span>
							<?php endif; ?>
						</div>
						<?php if ( $sky_cat && (int) get_option( 'default_category' ) !== $sky_cat->term_id ) : ?>
							<span class="post-card__topic"><?php echo esc_html( $sky_cat->name ); ?></span>
						<?php endif; ?>
						<h2 class="post-card__title"><a class="post-card__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<span class="post-card__meta">
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time> · <?php echo (int) $sky_read( get_the_ID() ); ?> мин чтения
						</span>
					</li>
				<?php endwhile; ?>
			</ul>
			<?php
			the_posts_pagination(
				[
					'mid_size'  => 1,
					'prev_text' => '← Назад',
					'next_text' => 'Дальше →',
					'class'     => 'post-pagination',
				]
			);
			?>
		<?php else : ?>
			<p class="section__note">Статей пока нет.</p>
		<?php endif; ?>
	</div>
</section>
<?php
sky_component(
	'cta-band',
	[
		'title'   => 'Нужна консультация по вашему случаю?',
		'buttons' => [ [ 'Задать вопрос юристу', home_url( '/kontakty/#form' ) ] ],
	]
);
