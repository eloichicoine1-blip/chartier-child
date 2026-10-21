<?php
/**
 * IT-17 : factures PDF. Relançable.
 * 1. TVA nécessaire à une facture conforme.
 * 2. Réglages de l'extension « PDF Invoices & Packing Slips » (logo, coordonnées
 *    fictives, numérotation, modèle « chartier » du thème enfant).
 * Prérequis prod : wp plugin install woocommerce-pdf-invoices-packing-slips --activate
 *                  wp language plugin install woocommerce-pdf-invoices-packing-slips fr_FR
 * Restauration à emporter / livrée : TVA à 10 %. Les prix de la carte restent
 * saisis TTC (ils ne changent pas) ; WooCommerce en déduit le HT et la TVA.
 * Local : ddev wp eval-file wp-content/themes/chartier-child/docs/factures-it17.php
 * Prod  : wp eval-file wp-content/themes/chartier-child/docs/factures-it17.php
 */
echo 'Avant : calc_taxes=' . get_option( 'woocommerce_calc_taxes' ) . ', prix TTC=' . get_option( 'woocommerce_prices_include_tax' ) . "\n";

update_option( 'woocommerce_calc_taxes', 'yes' );           // Activer la TVA.
update_option( 'woocommerce_prices_include_tax', 'yes' );   // Prix saisis TTC.
update_option( 'woocommerce_tax_based_on', 'base' );        // Taux de l'adresse du restaurant.
update_option( 'woocommerce_tax_display_shop', 'incl' );    // Boutique : prix TTC.
update_option( 'woocommerce_tax_display_cart', 'incl' );    // Panier / commande : prix TTC.
update_option( 'woocommerce_tax_total_display', 'single' ); // Une seule ligne « TVA ».

// Taux standard FR 10 %, appliqué aussi à la livraison. Pas de doublon si on relance.
$existe = false;
foreach ( WC_Tax::get_rates_for_tax_class( '' ) as $rate ) {
	if ( 'FR' === $rate->tax_rate_country ) {
		$existe = true;
		echo 'Taux déjà présent : ' . $rate->tax_rate_name . ' ' . (float) $rate->tax_rate . " %\n";
	}
}
if ( ! $existe ) {
	$id = WC_Tax::_insert_tax_rate(
		array(
			'tax_rate_country'  => 'FR',
			'tax_rate'          => '10.0000',
			'tax_rate_name'     => 'TVA',
			'tax_rate_priority' => 1,
			'tax_rate_compound' => 0,
			'tax_rate_shipping' => 1,
			'tax_rate_order'    => 0,
			'tax_rate_class'    => '',
		)
	);
	echo "Taux TVA FR 10 % créé (n°$id)\n";
}

echo 'Après : calc_taxes=' . get_option( 'woocommerce_calc_taxes' ) . ', prix TTC=' . get_option( 'woocommerce_prices_include_tax' ) . "\n";

// ---------------------------------------------------------------
// 2. Réglages de l'extension de factures
// ---------------------------------------------------------------
if ( ! class_exists( 'WPO_WCPDF' ) ) {
	exit( "\nExtension PDF Invoices absente ou inactive : installe-la puis relance.\n" );
}

// Logo : réutilise celui de la médiathèque, sinon l'importe depuis le thème.
$titre_logo = 'Logo Chartier (factures)';
$logo       = get_posts( array( 'post_type' => 'attachment', 'title' => $titre_logo, 'posts_per_page' => 1, 'fields' => 'ids' ) );
if ( $logo ) {
	$logo_id = $logo[0];
	echo "\nLogo déjà dans la médiathèque (n°$logo_id)\n";
} else {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam( 'logo-chartier' );
	copy( get_stylesheet_directory() . '/assets/img/logo-email-rouge.png', $tmp );
	$logo_id = media_handle_sideload( array( 'name' => 'logo-chartier-facture.png', 'tmp_name' => $tmp ), 0, $titre_logo );
	echo "\nLogo importé (n°$logo_id)\n";
}

$general = array_merge(
	(array) get_option( 'wpo_wcpdf_settings_general', array() ),
	array(
		'download_display'        => 'display',          // Ouvrir dans un nouvel onglet.
		'paper_size'              => 'a4',
		'template_path'           => 'theme/chartier',   // woocommerce/pdf/chartier/ du thème enfant.
		'header_logo'             => array( 'default' => (string) $logo_id ),
		'shop_name'               => array( 'default' => 'Bouillon Chartier · Click & Collect' ),
		'shop_address_line_1'     => array( 'default' => '7 rue du Faubourg Montmartre' ),
		'shop_address_postcode'   => array( 'default' => '75009' ),
		'shop_address_city'       => array( 'default' => 'Paris' ),
		'shop_address_country'    => array( 'default' => 'FR' ),
		'vat_number'              => 'FR00 000000000 (fictif)',
		'coc_number'              => 'SIRET 000 000 000 00000 (fictif)',
		'footer'                  => array( 'default' => 'Projet étudiant SAé 3.01 – BUT MMI, Université de Limoges. Facture fictive, sans valeur commerciale.' ),
	)
);
update_option( 'wpo_wcpdf_settings_general', $general );

$facture = array_merge(
	(array) get_option( 'wpo_wcpdf_documents_settings_invoice', array() ),
	array(
		'enabled'                  => '1',
		'attach_to_email_ids'      => array( 'customer_processing_order' => '1' ), // E-mail « Commande en cours ».
		'my_account_buttons'       => 'available',  // Bouton dans Mon compte quand la facture existe.
		'display_email'            => '1',
		'display_phone'            => '1',
		'display_customer_notes'   => '1',
		'display_shipping_address' => 'when_different', // « Livrer à » seulement si l'adresse diffère (pas en retrait).
		'display_number'           => 'invoice_number',
		'number_format'            => array( 'prefix' => 'FAC-[invoice_year]-', 'suffix' => '', 'padding' => '4' ), // FAC-2026-0001
		'display_date'             => 'document_date',
	)
);
update_option( 'wpo_wcpdf_documents_settings_invoice', $facture );

echo 'Factures : modèle ' . $general['template_path'] . ', numéro ' . $facture['number_format']['prefix'] . "0001, jointes à « Commande en cours »\n";

