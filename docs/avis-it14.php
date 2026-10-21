<?php
/**
 * IT-14 : avis clients (natif WooCommerce, sans extension).
 * - Avis et notes activés, note obligatoire.
 * - Seuls les clients ayant acheté le produit peuvent laisser un avis (US-14.2),
 *   badge « Propriétaire vérifié ».
 * - Publication immédiate (pas de modération), avis ouverts sur tous les produits.
 * Affiche ensuite les comptes clients pouvant tester (commande terminée ou en cours).
 * Relançable.
 * Local : ddev wp eval-file wp-content/themes/chartier-child/docs/avis-it14.php
 * Prod  : wp eval-file wp-content/themes/chartier-child/docs/avis-it14.php
 */
$reglages = array(
	'woocommerce_enable_reviews'                    => 'yes',
	'woocommerce_review_rating_verification_label'  => 'yes',
	'woocommerce_review_rating_verification_required' => 'yes',
	'woocommerce_enable_review_rating'              => 'yes',
	'woocommerce_review_rating_required'            => 'yes',
);
foreach ( $reglages as $option => $valeur ) {
	echo $option . ' : ' . get_option( $option ) . ' → ' . $valeur . "\n";
	update_option( $option, $valeur );
}

// Publication immédiate : pas de modération manuelle ni « auteur déjà approuvé ».
update_option( 'comment_moderation', 0 );
update_option( 'comment_previously_approved', 0 );
echo "Modération : désactivée (publication immédiate)\n";

// Avis ouverts sur tous les produits.
$ouverts = 0;
foreach ( get_posts( array( 'post_type' => 'product', 'numberposts' => -1, 'post_status' => 'publish' ) ) as $p ) {
	if ( 'open' !== $p->comment_status ) {
		wp_update_post( array( 'ID' => $p->ID, 'comment_status' => 'open' ) );
		++$ouverts;
	}
}
echo "Produits ouverts aux avis : $ouverts mis à jour\n\n";

// Comptes de test : clients avec une commande terminée ou en cours.
echo "Comptes clients pouvant laisser un avis (commande terminée / en cours) :\n";
$commandes = wc_get_orders( array( 'type' => 'shop_order', 'status' => array( 'completed', 'processing' ), 'limit' => -1 ) );
$vus = array();
foreach ( $commandes as $o ) {
	$uid = $o->get_customer_id();
	$nom = $uid ? get_userdata( $uid )->user_login : 'invité (' . $o->get_billing_email() . ')';
	$produits = array();
	foreach ( $o->get_items() as $item ) {
		$produits[] = $item->get_name();
	}
	echo '- n°' . $o->get_id() . ' (' . wc_get_order_status_name( $o->get_status() ) . ') · ' . $nom . ' · ' . implode( ', ', $produits ) . "\n";
}
echo "\nUn invité (sans compte) ne peut pas laisser d'avis : WooCommerce exige un compte connecté ayant acheté.\n";
