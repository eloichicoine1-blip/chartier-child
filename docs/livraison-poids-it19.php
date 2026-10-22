<?php
/**
 * IT-19 : tarifs de livraison selon le poids. Relançable.
 * 1. Règle les 3 tranches (modifiables ensuite dans WooCommerce › Réglages ›
 *    Expédition › Options d'expédition).
 * 2. Vérifie que « Livraison à Paris » est bien un forfait (flat_rate) : c'est lui qui est recalculé.
 * 3. Liste les plats sans poids (ils compteraient pour 0 kg).
 * Local : ddev wp eval-file wp-content/themes/chartier-child/docs/livraison-poids-it19.php
 * Prod  : wp eval-file wp-content/themes/chartier-child/docs/livraison-poids-it19.php
 */
foreach ( chartier_weight_tiers_defaults() as $cle => $valeur ) {
	if ( false === get_option( $cle, false ) ) {
		update_option( $cle, $valeur ); // Ne remplace pas un tarif déjà modifié par l'admin.
	}
}
WC_Cache_Helper::get_transient_version( 'shipping', true );
echo "Tranches :\n";
foreach ( chartier_weight_tiers() as $i => $t ) {
	$max = is_infinite( $t['max'] ) ? 'au-delà' : 'jusqu\'à ' . $t['max'] . ' kg';
	echo '- tranche ' . ( $i + 1 ) . ' (' . $max . ') : ' . number_format( $t['price'], 2, ',', '' ) . " € TTC\n";
}

echo "\nModes d'expédition recalculés selon le poids :\n";
$zones   = WC_Shipping_Zones::get_zones();
$zones[] = array( 'zone_name' => 'Reste du monde', 'shipping_methods' => ( new WC_Shipping_Zone( 0 ) )->get_shipping_methods() );
$trouve  = false;
foreach ( $zones as $zone ) {
	foreach ( $zone['shipping_methods'] as $m ) {
		if ( 'flat_rate' === $m->id && 'yes' === $m->enabled ) {
			$trouve = true;
			echo '- ' . $zone['zone_name'] . ' › ' . $m->get_title() . ' (TVA : ' . $m->get_option( 'tax_status' ) . ")\n";
		}
	}
}
if ( ! $trouve ) {
	echo "- AUCUN forfait actif : la livraison ne serait pas recalculée !\n";
}

echo "\nPlats ou options sans poids :\n";
$sans = 0;
foreach ( wc_get_products( array( 'limit' => -1, 'status' => 'publish' ) ) as $p ) {
	$liste = $p->is_type( 'variable' ) ? array_map( 'wc_get_product', $p->get_children() ) : array( $p );
	foreach ( $liste as $v ) {
		if ( $v && ! (float) $v->get_weight() ) {
			echo '- ' . $v->get_name() . "\n";
			$sans++;
		}
	}
}
echo $sans ? "→ $sans sans poids : à compléter dans la fiche du plat (onglet Expédition).\n" : "- aucun, tous les plats ont un poids\n";
echo "\nUnité de poids de la boutique : " . get_option( 'woocommerce_weight_unit' ) . "\n";
