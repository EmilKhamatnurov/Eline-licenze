<?php
/**
 * Компонент «Полоса-призыв» — заголовок, текст и кнопки на --brand (макеты About, Articles, Licenses).
 *
 * Аргументы:
 *   title   — заголовок;
 *   text    — текст под заголовком;
 *   buttons — [ [ текст, ссылка ], … ]: первая — белая, остальные — контур;
 *   class   — дополнительный класс.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'title'   => '',
		'text'    => '',
		'buttons' => [],
		'class'   => '',
	]
);
?>
<section class="cta-band is-dark <?php echo esc_attr( $args['class'] ); ?>">
	<div class="sky-container cta-band__inner">
		<div class="cta-band__text">
			<h2 class="cta-band__title"><?php echo esc_html( $args['title'] ); ?></h2>
			<?php if ( $args['text'] ) : ?>
				<p class="cta-band__lead"><?php echo esc_html( $args['text'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $args['buttons'] ) : ?>
			<div class="cta-band__actions">
				<?php foreach ( (array) $args['buttons'] as $sky_i => [ $sky_label, $sky_url ] ) : ?>
					<a class="btn <?php echo 0 === $sky_i ? 'btn--light' : 'btn--outline-light'; ?>" href="<?php echo esc_url( $sky_url ); ?>"><?php echo esc_html( $sky_label ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
