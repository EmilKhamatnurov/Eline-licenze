<?php
/**
 * Боковой каталог: направления из таксономии regulator (только с лицензиями), текущее выделено.
 * «Другие лицензии» — в конце списка.
 *
 * @package Sky\Starter
 */

defined( 'ABSPATH' ) || exit;

$sky_current = $args['current'] ?? null;
$sky_terms   = sky_starter_regulators();
if ( ! $sky_terms ) {
	return;
}
?>
<p class="catalog-nav__label" id="catalog-nav-label">Каталог лицензий</p>
<nav class="catalog-nav" aria-labelledby="catalog-nav-label">
	<ul class="catalog-nav__list">
		<?php
		foreach ( $sky_terms as $sky_term ) :
			$sky_on = $sky_current instanceof WP_Term && $sky_current->term_id === $sky_term->term_id;
			?>
			<li>
				<a class="catalog-nav__link<?php echo $sky_on ? ' is-current' : ''; ?>" href="<?php echo esc_url( get_term_link( $sky_term ) ); ?>"<?php echo $sky_on ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $sky_term->name ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
