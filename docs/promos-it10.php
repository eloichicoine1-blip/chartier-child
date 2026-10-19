<?php
/**
 * IT-10 : active les codes promo et crée les codes de test (relançable sans doublon).
 * Lancer en local : ddev wp eval-file wp-content/themes/chartier-child/docs/promos-it10.php
 * En prod : wp eval-file wp-content/themes/chartier-child/docs/promos-it10.php
 */
update_option( 'woocommerce_enable_coupons', 'yes' );
update_option( 'woocommerce_calc_discounts_sequentially', 'no' );

$ids = array();
foreach ( wc_get_products( array( 'limit' => -1, 'parent' => 0 ) ) as $p ) {
	$ids[ $p->get_name() ] = $p->get_id();
}
$baba        = $ids['Baba au rhum'] ?? 0;
$pot         = $ids['Pot-au-feu'] ?? 0;
$profiteroles = $ids['Profiteroles, sauce chocolat'] ?? 0;

$codes = array(
	// US-10.1 : montant fixe sur un produit
	'BABA2'      => array( 'type' => 'fixed_product', 'amount' => 2, 'products' => array( $baba ), 'desc' => '2 € de remise sur le baba au rhum' ),
	// US-10.1 : pourcentage sur un autre produit
	'POTAUFEU20' => array( 'type' => 'percent', 'amount' => 20, 'products' => array( $pot ), 'desc' => '20 % sur le pot-au-feu' ),
	// US-10.3 : code expiré (date passée)
	'ETE2026'    => array( 'type' => 'percent', 'amount' => 10, 'products' => array(), 'expires' => '2026-08-31', 'desc' => 'Code expiré (fin de l’été) pour le test' ),
	// US-10.3 : code non applicable si le panier ne contient pas de profiteroles
	'PROFITEROLES' => array( 'type' => 'fixed_product', 'amount' => 1, 'products' => array( $profiteroles ), 'desc' => '1 € sur les profiteroles (non applicable sans profiteroles)' ),
);

foreach ( $codes as $code => $c ) {
	$id     = wc_get_coupon_id_by_code( $code );
	$coupon = new WC_Coupon( $id ? $id : 0 );
	$coupon->set_code( $code );
	$coupon->set_description( $c['desc'] );
	$coupon->set_discount_type( $c['type'] );
	$coupon->set_amount( $c['amount'] );
	$coupon->set_product_ids( array_filter( $c['products'] ) );
	$coupon->set_individual_use( false );
	$coupon->set_date_expires( $c['expires'] ?? null );
	$coupon->save();
	echo ( $id ? 'Mis à jour ' : 'Créé ' ) . $code . ' (n°' . $coupon->get_id() . ') → produits ' . implode( ',', $coupon->get_product_ids() ) . "\n";
}
echo 'Codes promo activés : ' . get_option( 'woocommerce_enable_coupons' ) . "\n";
