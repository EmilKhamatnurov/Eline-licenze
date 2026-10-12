<?php
/**
 * Компонент «Отзывы».
 * Без микроразметки Review: отзывы о себе на своём сайте поисковики не показывают в сниппете,
 * а размеченные без проверяемого источника рискуют санкциями.
 *
 * Аргументы:
 *   title — заголовок секции;
 *   items — [ [ 'author' => …, 'text' => …, 'rating' => 1–5, 'date' => …, 'source' => …, 'url' => … ], … ];
 *   class — доп. класс секции.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'title' => 'Отзывы клиентов',
		'items' => [],
		'class' => 'sky-section--surface',
	]
);

$sky_items = array_filter( (array) $args['items'], static fn( $i ) => is_array( $i ) && ! empty( $i['text'] ) );
if ( ! $sky_items ) {
	return;
}
?>
<section class="sky-section sky-reviews <?php echo esc_attr( $args['class'] ); ?>">
	<div class="sky-container">
		<?php if ( $args['title'] ) : ?>
			<h2 class="sky-section__title"><?php echo esc_html( $args['title'] ); ?></h2>
		<?php endif; ?>
		<div class="sky-cards">
			<?php foreach ( $sky_items as $sky_review ) : ?>
				<?php $sky_rating = min( 5, max( 0, (int) ( $sky_review['rating'] ?? 0 ) ) ); ?>
				<figure class="sky-card sky-review">
					<div class="sky-card__body">
						<?php if ( $sky_rating ) : ?>
							<div class="sky-review__rating" aria-label="<?php echo esc_attr( sprintf( 'Оценка %d из 5', $sky_rating ) ); ?>">
								<?php echo esc_html( str_repeat( '★', $sky_rating ) . str_repeat( '☆', 5 - $sky_rating ) ); ?>
							</div>
						<?php endif; ?>
						<blockquote class="sky-card__text"><?php echo wp_kses_post( wpautop( (string) $sky_review['text'] ) ); ?></blockquote>
						<figcaption>
							<cite class="sky-review__author"><?php echo esc_html( $sky_review['author'] ?? '' ); ?></cite>
							<?php if ( ! empty( $sky_review['date'] ) || ! empty( $sky_review['source'] ) ) : ?>
								<div class="sky-review__meta">
									<?php echo esc_html( $sky_review['date'] ?? '' ); ?>
									<?php if ( ! empty( $sky_review['source'] ) ) : ?>
										<?php if ( ! empty( $sky_review['url'] ) ) : ?>
											· <a href="<?php echo esc_url( $sky_review['url'] ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $sky_review['source'] ); ?></a>
										<?php else : ?>
											· <?php echo esc_html( $sky_review['source'] ); ?>
										<?php endif; ?>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</figcaption>
					</div>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
