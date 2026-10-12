<?php
/**
 * Thème enfant Chartier (Storefront).
 * Storefront charge déjà style.css du thème enfant.
 *
 * @package chartier-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Polices de la charte : Poppins (textes) et Gloock (titres).
 */
function chartier_enqueue_fonts() {
	wp_enqueue_style(
		'chartier-fonts',
		'https://fonts.googleapis.com/css2?family=Gloock&family=Poppins:wght@400;500;600;700&display=swap',
		array(),
		null
	);
}
add_action( 'wp_enqueue_scripts', 'chartier_enqueue_fonts' );
