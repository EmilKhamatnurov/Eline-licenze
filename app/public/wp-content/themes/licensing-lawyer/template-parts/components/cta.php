<?php
/**
 * Компонент «Призыв к действию» — нижний блок с формой заявки на --brand (макеты License, Main).
 *
 * Аргументы:
 *   title     — заголовок;
 *   text      — текст рядом с формой;
 *   form      — выводить форму заявки (компонент lead-form);
 *   form_args — аргументы lead-form: button, topic, title…;
 *   source    — метка источника заявки (по умолчанию заголовок страницы);
 *   phone     — телефон (по умолчанию из baseline; false — не показывать);
 *   contacts  — вместо телефона блок контактов: телефон, e-mail, адрес, мессенджеры (пустые — заглушки);
 *   id        — якорь формы (#form);
 *   class     — модификатор секции, например sky-cta--home.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? [],
	[
		'title'     => 'Обсудим вашу задачу',
		'text'      => 'Оставьте телефон — юрист перезвонит, задаст пару вопросов и назовёт срок и стоимость.',
		'form'      => true,
		'form_args' => [],
		'source'    => '',
		'phone'     => sky_starter_org( 'phone' ),
		'contacts'  => false,
		'id'        => 'form',
		'class'     => '',
	]
);

$sky_phone_href = $args['phone'] ? sky_starter_tel( (string) $args['phone'] ) : '';
?>
<section class="sky-cta is-dark <?php echo esc_attr( $args['class'] ); ?>">
	<div class="sky-container sky-cta__grid">
		<div class="sky-cta__content">
			<?php if ( $args['title'] ) : ?>
				<h2 class="sky-cta__title"><?php echo esc_html( $args['title'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $args['text'] ) : ?>
				<p class="sky-cta__text"><?php echo esc_html( $args['text'] ); ?></p>
			<?php endif; ?>
			<?php if ( $args['contacts'] ) : ?>
				<?php get_template_part( 'template-parts/layout/contacts' ); ?>
			<?php elseif ( $sky_phone_href ) : ?>
				<p class="sky-cta__phone"><a href="<?php echo esc_url( $sky_phone_href ); ?>"><?php echo esc_html( $args['phone'] ); ?></a></p>
			<?php endif; ?>
		</div>
		<?php if ( $args['form'] ) : ?>
			<div class="sky-cta__form">
				<?php
				sky_component(
					'lead-form',
					array_merge(
						[
							'source' => $args['source'],
							'id'     => $args['id'],
						],
						(array) $args['form_args']
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
