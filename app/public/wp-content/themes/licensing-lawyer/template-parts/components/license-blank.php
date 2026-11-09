<?php
/**
 * Компонент «Бланк лицензии» — иллюстрация в hero (DESIGN.md §5): бумага, золотая рамка, гильош,
 * поля, печать. Только HTML и CSS, без изображений. Не использует гербы и названия органов —
 * все надписи заглушки. Подпись «Иллюстрация. Не является документом.» обязательна.
 *
 * На телефоне — сокращённый вид (макет MainMobile): заголовок, номер, вид деятельности и печать.
 *
 * Аргументы:
 *   class — дополнительный класс.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? [], [ 'class' => '' ] );
?>
<figure class="blank <?php echo esc_attr( $args['class'] ); ?>">
	<div class="blank__sheet" role="img" aria-label="Иллюстрация: бланк лицензии с печатью. Пример оформления, не является документом">
		<div class="blank__frame">
			<div class="blank__inner">
				<p class="blank__issuer">[ОРГАН, ВЫДАВШИЙ ЛИЦЕНЗИЮ]</p>
				<p class="blank__title">ЛИЦЕНЗИЯ</p>
				<p class="blank__number">№ [XX-XXXXXX] от [ДАТА]</p>
				<div class="blank__body">
					<dl class="blank__fields">
						<div class="blank__field blank__field--extra">
							<dt>Лицензиат</dt>
							<dd>[ООО «КЛИЕНТ»]</dd>
						</div>
						<div class="blank__field">
							<dt>Вид деятельности</dt>
							<dd>[ВИД ЛИЦЕНЗИРУЕМОЙ ДЕЯТЕЛЬНОСТИ]</dd>
						</div>
						<div class="blank__field blank__field--extra">
							<dt>Срок действия</dt>
							<dd>[СРОК]</dd>
						</div>
					</dl>
					<span class="blank__sign">подпись</span>
					<span class="blank__seal">§</span>
				</div>
			</div>
		</div>
	</div>
	<figcaption class="blank__caption">Иллюстрация. Не является документом.</figcaption>
</figure>
