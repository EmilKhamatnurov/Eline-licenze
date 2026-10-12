<?php
/**
 * Компонент «Первый экран».
 *
 * Аргументы:
 *   title       — H1 (по умолчанию заголовок текущей записи/архива);
 *   subtitle    — подзаголовок;
 *   facts       — [ [ 'label' => 'Срок', 'value' => '45 дней' ], … ];
 *   image_id    — ID вложения;
 *   button      — текст кнопки (пусто — без кнопки);
 *   button_url  — ссылка кнопки (#form);
 *   phone       — телефон (по умолчанию из baseline; false — не показывать);
 *   breadcrumbs — выводить крошки над H1.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'title'       => is_singular() ? get_the_title() : wp_strip_all_tags( get_the_archive_title() ),
		'subtitle'    => '',
		'facts'       => [],
		'image_id'    => 0,
		'button'      => 'Получить консультацию',
		'button_url'  => '#form',
		'phone'       => function_exists( 'sky_opt' ) ? (string) sky_opt( 'sky_phone' ) : '',
		'breadcrumbs' => true,
	]
);

$sky_phone_href = $args['phone'] ? 'tel:' . preg_replace( '/[^\d+]/', '', (string) $args['phone'] ) : '';
?>
<section class="sky-hero">
	<div class="sky-container">
		<?php
		if ( $args['breadcrumbs'] ) {
			sky_component( 'breadcrumbs' );
		}
		?>
		<div class="sky-hero__grid">
			<div class="sky-hero__content">
				<h1 class="sky-hero__title"><?php echo esc_html( $args['title'] ); ?></h1>

				<?php if ( $args['subtitle'] ) : ?>
					<p class="sky-hero__subtitle"><?php echo esc_html( $args['subtitle'] ); ?></p>
				<?php endif; ?>

				<?php if ( $args['facts'] ) : ?>
					<ul class="sky-hero__facts">
						<?php foreach ( (array) $args['facts'] as $sky_fact ) : ?>
							<?php
							if ( empty( $sky_fact['value'] ) ) {
								continue;
							}
							?>
							<li class="sky-hero__fact">
								<?php echo esc_html( $sky_fact['label'] ?? '' ); ?>
								<strong><?php echo esc_html( $sky_fact['value'] ); ?></strong>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<div class="sky-hero__actions">
					<?php if ( $args['button'] ) : ?>
						<a class="sky-btn" href="<?php echo esc_url( $args['button_url'] ); ?>"><?php echo esc_html( $args['button'] ); ?></a>
					<?php endif; ?>
					<?php if ( $sky_phone_href ) : ?>
						<a class="sky-hero__phone" href="<?php echo esc_url( $sky_phone_href, [ 'tel' ] ); ?>"><?php echo esc_html( $args['phone'] ); ?></a>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( $args['image_id'] ) : ?>
				<div class="sky-hero__image">
					<?php
					// Первый экран: картинка — кандидат в LCP, грузим сразу.
					echo wp_get_attachment_image(
						(int) $args['image_id'],
						'large',
						false,
						[
							'loading'       => 'eager',
							'fetchpriority' => 'high',
						]
					);
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
