<?php
/**
 * IT-13 : ventes croisées « Pour compléter votre repas ».
 * Chaque plat propose une entrée et un dessert (champ « Ventes croisées »).
 * Relançable : la liste de chaque plat est remplacée, pas complétée.
 * Local : ddev wp eval-file wp-content/themes/chartier-child/docs/ventes-croisees-it13.php
 * Prod  : wp eval-file wp-content/themes/chartier-child/docs/ventes-croisees-it13.php
 */
$associations = array(
	'CH-P01' => array( 'CH-E01', 'CH-D01' ), // Pièce du boucher → Œuf mayo + Mousse au chocolat
	'CH-P02' => array( 'CH-E02', 'CH-D03' ), // Bœuf bourguignon → Poireaux vinaigrette + Baba au rhum
	'CH-P03' => array( 'CH-E01', 'CH-D02' ), // Volaille rôtie → Œuf mayo + Profiteroles
	'CH-P04' => array( 'CH-E02', 'CH-D03' ), // Pot-au-feu → Poireaux vinaigrette + Baba au rhum
	'CH-P05' => array( 'CH-E02', 'CH-D01' ), // Spaghetti aux légumes → Poireaux vinaigrette + Mousse au chocolat
);

foreach ( $associations as $sku => $suggestions ) {
	$id = wc_get_product_id_by_sku( $sku );
	if ( ! $id ) {
		WP_CLI::warning( "UGS $sku introuvable, plat ignoré." );
		continue;
	}
	$ids = array();
	foreach ( $suggestions as $s ) {
		$sid = wc_get_product_id_by_sku( $s );
		if ( $sid ) {
			$ids[] = $sid;
		} else {
			WP_CLI::warning( "UGS $s introuvable." );
		}
	}
	$product = wc_get_product( $id );
	$product->set_cross_sell_ids( $ids );
	$product->save();
	echo $product->get_name() . ' → ' . implode( ' + ', array_map( 'get_the_title', $ids ) ) . "\n";
}
echo "\nVentes croisées enregistrées.\n";
