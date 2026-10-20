<?php
/**
 * IT-12 : crée les 3 collections et y range les produits (par UGS/SKU),
 * d'après 06_catalogue/collections-it12.csv.
 * Relançable sans doublon : les collections de chaque produit sont remplacées.
 * Local : ddev wp eval-file wp-content/themes/chartier-child/docs/collections-it12.php
 * Prod  : wp eval-file wp-content/themes/chartier-child/docs/collections-it12.php
 */
if ( ! taxonomy_exists( 'collection' ) ) {
	WP_CLI::error( 'Taxonomie « collection » absente : faire le git pull du thème avant ce script.' );
}

// Ordre d'affichage = ordre du tableau.
$collections = array(
	'classiques'  => array( 'Les classiques depuis 1896', 'Les recettes de la maison, servies depuis l’ouverture du Bouillon en 1896 et toujours à la carte.' ),
	'vegetarien'  => array( 'Végétarien', 'Sans viande ni poisson, de l’entrée au dessert.' ),
	'petits-prix' => array( 'Petits prix', 'Les plats les plus doux pour le porte-monnaie, pour composer un repas complet sans se ruiner.' ),
);

$produits = array(
	'CH-E01' => array( 'classiques', 'vegetarien', 'petits-prix' ),
	'CH-E02' => array( 'classiques', 'vegetarien', 'petits-prix' ),
	'CH-E03' => array( 'classiques' ),
	'CH-P01' => array( 'classiques' ),
	'CH-P02' => array( 'classiques' ),
	'CH-P03' => array( 'classiques' ),
	'CH-P04' => array( 'classiques' ),
	'CH-P05' => array( 'vegetarien', 'petits-prix' ),
	'CH-D01' => array( 'classiques', 'vegetarien', 'petits-prix' ),
	'CH-D02' => array( 'classiques', 'vegetarien' ),
	'CH-D03' => array( 'classiques', 'vegetarien' ),
);

// 1. Termes (US-12.2).
$ordre = 0;
foreach ( $collections as $slug => list( $nom, $description ) ) {
	$terme = get_term_by( 'slug', $slug, 'collection' );
	if ( $terme ) {
		wp_update_term( $terme->term_id, 'collection', array( 'name' => $nom, 'description' => $description ) );
		$id = $terme->term_id;
		echo "Collection mise à jour : $nom\n";
	} else {
		$res = wp_insert_term( $nom, 'collection', array( 'slug' => $slug, 'description' => $description ) );
		if ( is_wp_error( $res ) ) {
			WP_CLI::error( $nom . ' : ' . $res->get_error_message() );
		}
		$id = $res['term_id'];
		echo "Collection créée : $nom\n";
	}
	wc_set_term_order( $id, $ordre++, 'collection' );
}

// 2. Produits rangés dans les collections.
foreach ( $produits as $sku => $slugs ) {
	$id = wc_get_product_id_by_sku( $sku );
	if ( ! $id ) {
		WP_CLI::warning( "UGS $sku introuvable, produit ignoré." );
		continue;
	}
	wp_set_object_terms( $id, $slugs, 'collection' );
	echo $sku . ' ' . get_the_title( $id ) . ' → ' . implode( ', ', $slugs ) . "\n";
}

// 3. Bilan + adresses des pages dédiées (US-12.3).
echo "\n";
foreach ( array_keys( $collections ) as $slug ) {
	$terme = get_term_by( 'slug', $slug, 'collection' );
	echo $terme->name . ' : ' . $terme->count . ' produits → ' . get_term_link( $terme ) . "\n";
}

// Nouvelle adresse /collection/ : régénère les règles de réécriture (sinon 404).
flush_rewrite_rules();
wc_delete_product_transients();
echo "\nRègles d'URL régénérées.\n";
