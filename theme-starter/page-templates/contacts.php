<?php
/**
 * Template Name: Контакты
 *
 * Страница /kontakty/ (макет Contacts): карточки контактов, форма с темой обращения, место под карту.
 * Контакты — из organization.*; пустые — заглушки без ссылок (DESIGN.md §8). Форма — #form:
 * на неё ведут «Получить консультацию» со страниц, где своей формы нет.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_phone   = sky_starter_org( 'phone' );
$sky_tel     = sky_starter_tel( $sky_phone );
$sky_email   = sky_starter_org( 'email' );
$sky_address = trim( implode( ', ', array_filter( [ sky_starter_org( 'city' ), sky_starter_org( 'street' ) ] ) ) );
$sky_chats   = array_filter(
	[
		'Telegram' => sky_starter_org( 'telegram' ),
		'WhatsApp' => sky_starter_org( 'whatsapp' ),
	]
);

get_header();
?>
<div id="main" class="page">
	<?php
	sky_component(
		'page-hero',
		[
			'title' => 'Свяжитесь с нами',
			'lead'  => 'Позвоните, напишите в мессенджер или оставьте заявку — ответим в течение [XX] минут в рабочее время.',
		]
	);
	?>

	<section class="section section--first" aria-label="Контакты и форма заявки">
		<div class="sky-container contacts">
			<ul class="contacts__cards">
				<li class="contact-card">
					<p class="contact-card__label">Телефон</p>
					<p class="contact-card__value contact-card__value--lg">
						<?php if ( $sky_tel ) : ?>
							<a href="<?php echo esc_url( $sky_tel ); ?>"><?php echo esc_html( $sky_phone ); ?></a>
						<?php else : ?>
							[ТЕЛЕФОН]
						<?php endif; ?>
					</p>
					<p class="contact-card__note">Пн–Пт 9:00–18:00 · [ЧАСОВОЙ ПОЯС]</p>
				</li>
				<li class="contact-card">
					<p class="contact-card__label">Мессенджеры</p>
					<?php if ( $sky_chats ) : ?>
						<div class="contact-card__actions">
							<?php foreach ( $sky_chats as $sky_label => $sky_url ) : ?>
								<a class="btn btn--sm btn--secondary" href="<?php echo esc_url( $sky_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $sky_label ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<p class="contact-card__value">Telegram · WhatsApp: [КОНТАКТ]</p>
					<?php endif; ?>
				</li>
				<li class="contact-card">
					<p class="contact-card__label">Почта</p>
					<p class="contact-card__value">
						<?php if ( is_email( $sky_email ) ) : ?>
							<a href="<?php echo esc_url( 'mailto:' . $sky_email ); ?>"><?php echo esc_html( $sky_email ); ?></a>
						<?php else : ?>
							[EMAIL]
						<?php endif; ?>
					</p>
				</li>
				<li class="contact-card">
					<p class="contact-card__label">Офис</p>
					<p class="contact-card__value"><?php echo esc_html( '' !== $sky_address ? $sky_address : '[АДРЕС ОФИСА]' ); ?></p>
					<p class="contact-card__note">Приём по предварительной записи. Основная работа — дистанционно.</p>
				</li>
			</ul>

			<div class="contacts__form">
				<?php
				sky_component(
					'lead-form',
					[
						'id'                => 'form',
						'title'             => 'Оставить заявку',
						'title_tag'         => 'h2',
						'topics'            => [ 'Получение лицензии', 'Юрист на аутсорсе', 'Документы', 'Юридический ИИ', 'Другое' ],
						'topic_label'       => 'Сообщение (необязательно)',
						'topic_placeholder' => '',
						'message_type'      => 'textarea',
						'button'            => 'Отправить заявку',
						'source'            => 'Контакты',
						'class'             => 'request--outlined',
					]
				);
				?>
			</div>
		</div>

		<div class="sky-container">
			<div class="contacts__map">[Карта проезда: Яндекс Карты]</div>
		</div>
	</section>
</div>
<?php
get_footer();
