<?php
/**
 * Компонент «Первый экран внутренней страницы» — тёмный, с крошками (макеты Outsourcing, Documents,
 * LegalAI, About, Contacts, Articles). Стили — .page-hero в components.css.
 *
 *   sky_component( 'page-hero', [ 'title' => '…', 'lead' => '…', 'buttons' => [ [ 'Текст', '#form' ] ] ] );
 *
 * Аргументы:
 *   title   — H1 (по умолчанию заголовок записи);
 *   lead    — подзаголовок;
 *   badge   — плашка над H1 (только «Скоро» у юридического ИИ, DESIGN.md §3);
 *   buttons — [ [ текст, ссылка ], … ]: первая — белая, остальные — контур;
 *   form    — аргументы lead-form для карточки справа или false;
 *   decor   — rings: тонкие окружности справа (макет LegalAI);
 *   class   — дополнительный класс.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'title'   => '',
		'lead'    => '',
		'badge'   => '',
		'buttons' => [],
		'form'    => false,
		'decor'   => '',
		'class'   => '',
	]
);

$sky_title = '' !== $args['title'] ? $args['title'] : wp_strip_all_tags( (string) get_the_title() );
$sky_form  = is_array( $args['form'] ) ? $args['form'] : null;
?>
<section class="page-hero is-dark<?php echo $args['decor'] ? ' page-hero--' . esc_attr( $args['decor'] ) : ''; ?> <?php echo esc_attr( $args['class'] ); ?>">
	<?php if ( 'rings' === $args['decor'] ) : ?>
		<svg class="page-hero__rings" aria-hidden="true" focusable="false" width="800" height="800" viewBox="0 0 800 800"><g fill="none" stroke="currentColor" stroke-width="2"><circle cx="400" cy="400" r="380"/><circle cx="400" cy="400" r="320"/><circle cx="400" cy="400" r="260"/><circle cx="400" cy="400" r="200"/><circle cx="400" cy="400" r="140"/></g></svg>
	<?php endif; ?>
	<div class="sky-container page-hero__container">
		<div class="page-hero__crumbs">
			<?php sky_component( 'breadcrumbs' ); ?>
		</div>

		<div class="page-hero__grid<?php echo $sky_form ? ' page-hero__grid--form' : ''; ?>">
			<div class="page-hero__main">
				<?php if ( $args['badge'] ) : ?>
					<p class="badge badge--gold"><?php echo esc_html( $args['badge'] ); ?></p>
				<?php endif; ?>
				<h1 class="page-hero__title"><?php echo esc_html( $sky_title ); ?></h1>
				<?php if ( $args['lead'] ) : ?>
					<p class="page-hero__lead"><?php echo esc_html( $args['lead'] ); ?></p>
				<?php endif; ?>
				<?php if ( $args['buttons'] ) : ?>
					<div class="page-hero__actions">
						<?php foreach ( (array) $args['buttons'] as $sky_i => [ $sky_label, $sky_url ] ) : ?>
							<a class="btn <?php echo 0 === $sky_i ? 'btn--light' : 'btn--outline-light'; ?>" href="<?php echo esc_url( $sky_url ); ?>"><?php echo esc_html( $sky_label ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $sky_form ) : ?>
				<div class="page-hero__form">
					<?php sky_component( 'lead-form', array_merge( [ 'class' => 'request--raised' ], $sky_form ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
