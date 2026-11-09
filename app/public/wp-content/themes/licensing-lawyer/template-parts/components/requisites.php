<?php
/**
 * Компонент «Реквизиты» — из organization.* (Customizer «Контакты организации» или конфиг);
 * пустые значения — заглушки (DESIGN.md §8). Банковские реквизиты — ключи organization.bank / account.
 *
 * Аргументы:
 *   class — дополнительный класс.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? [], [ 'class' => '' ] );

$sky_or   = static fn( string $key, string $placeholder ): string => '' !== sky_starter_org( $key ) ? sky_starter_org( $key ) : $placeholder;
$sky_ogrn = preg_replace( '/\D+/', '', sky_starter_org( 'ogrn' ) );
$sky_addr = trim( implode( ', ', array_filter( [ sky_starter_org( 'zip' ), sky_starter_org( 'city' ), sky_starter_org( 'street' ) ] ) ) );

$sky_rows = [
	'Полное наименование'                          => $sky_or( 'legal_name', '[ООО «НАЗВАНИЕ»]' ),
	'ИНН / КПП'                                    => $sky_or( 'inn', '[XXXXXXXXXX]' ) . ' / ' . $sky_or( 'kpp', '[XXXXXXXXX]' ),
	15 === strlen( $sky_ogrn ) ? 'ОГРНИП' : 'ОГРН' => '' !== $sky_ogrn ? $sky_ogrn : '[XXXXXXXXXXXXX]',
	'Юридический адрес'                            => '' !== $sky_addr ? $sky_addr : '[АДРЕС]',
	'Расчётный счёт и банк'                        => $sky_or( 'bank', '[РЕКВИЗИТЫ БАНКА]' ),
];
?>
<dl class="requisites <?php echo esc_attr( $args['class'] ); ?>">
	<?php foreach ( $sky_rows as $sky_label => $sky_value ) : ?>
		<div class="requisites__row">
			<dt><?php echo esc_html( $sky_label ); ?></dt>
			<dd><?php echo esc_html( $sky_value ); ?></dd>
		</div>
	<?php endforeach; ?>
</dl>
