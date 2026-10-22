<?php
/**
 * Itération W : diagnostic du plat star (lecture seule, ne modifie rien).
 * Affiche les ventes de tous les plats, le plat star retenu et les 3 dernières commandes
 * (une commande n'est comptée dans les ventes qu'une fois en attente, en cours ou terminée).
 * Local : ddev wp eval-file wp-content/themes/chartier-child/docs/plat-star-itW.php
 * Prod  : wp eval-file wp-content/themes/chartier-child/docs/plat-star-itW.php
 */
delete_transient( 'chartier_star_product' ); // Recalcul immédiat pour le diagnostic.
echo "Ventes par plat (méta total_sales) :\n";
$plats = wc_get_products( array( 'limit' => -1, 'status' => 'publish' ) );
usort( $plats, fn( $a, $b ) => $b->get_total_sales() <=> $a->get_total_sales() );
foreach ( $plats as $p ) {
	echo '- ' . $p->get_name() . ' : ' . (int) $p->get_total_sales() . ( $p->is_in_stock() ? '' : ' (épuisé, écarté)' ) . "\n";
}
$star = wc_get_product( chartier_star_product_id() );
echo "\nPlat star : " . ( $star ? $star->get_name() : 'aucun (pas encore de vente)' ) . "\n";

echo "\n3 dernières commandes :\n";
foreach ( wc_get_orders( array( 'limit' => 3, 'orderby' => 'date', 'order' => 'DESC' ) ) as $o ) {
	$lignes = array();
	foreach ( $o->get_items() as $item ) {
		$lignes[] = $item->get_name() . ' × ' . $item->get_quantity();
	}
	echo '- n°' . $o->get_id() . ' (' . wc_get_order_status_name( $o->get_status() ) . ', ventes comptées : ' . ( $o->get_data_store()->get_recorded_sales( $o ) ? 'oui' : 'non' ) . ') : ' . implode( ', ', $lignes ) . "\n";
}
