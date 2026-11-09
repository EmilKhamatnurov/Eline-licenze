<?php
/**
 * Загрузчик mu-plugin. Копируется в wp-content/mu-plugins/ рядом с каталогом sky-baseline/
 * (WordPress не заходит в подкаталоги mu-plugins сам).
 *
 * @package Sky\Baseline
 */

$baseline = __DIR__ . '/sky-baseline/sky-baseline.php';
if ( ! is_file( $baseline ) ) {
	$baseline = dirname( __DIR__, 4 ) . '/baseline/sky-baseline.php';
}
require $baseline;
