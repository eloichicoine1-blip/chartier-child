<?php
/**
 * Page dédiée d'une collection (IT-12, US-12.3) : /collection/<terme>/.
 *
 * Hiérarchie des templates : taxonomy-{taxonomie}.php. Pour une taxonomie
 * de produits, WooCommerce cherche ce fichier avant archive-product.php.
 * La grille reprend le gabarit de la boutique (cartes produit, tri,
 * pagination déjà habillés), comme WooCommerce le fait pour ses catégories
 * (taxonomy-product_cat.php). Le titre et la description viennent du terme,
 * les tuiles « Nos collections » de functions.php §16.
 *
 * @package chartier-child
 */

defined( 'ABSPATH' ) || exit;

wc_get_template( 'archive-product.php' );
