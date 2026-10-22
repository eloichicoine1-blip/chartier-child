<?php
/**
 * IT-18 : recherche et filtrage de la carte. Relançable.
 * 1. Attribut global « Régime » (Végétarien, Sans gluten, Sans lactose),
 *    déduit des allergènes saisis à l'IT-4, assigné aux plats (pas une option de commande).
 * 2. Widgets natifs de WooCommerce dans la zone « Filtres de la carte » :
 *    filtres actifs, prix, régime.
 * Local : ddev wp eval-file wp-content/themes/chartier-child/docs/filtres-it18.php
 * Prod  : wp eval-file wp-content/themes/chartier-child/docs/filtres-it18.php
 */

// ---------------------------------------------------------------
// 1. Attribut « Régime »
// ---------------------------------------------------------------
$attribut_id = wc_attribute_taxonomy_id_by_name( 'regime' );
if ( ! $attribut_id ) {
	$attribut_id = wc_create_attribute(
		array(
			'name'         => 'Régime',
			'slug'         => 'regime',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		)
	);
	echo "Attribut « Régime » créé (n°$attribut_id)\n";
} else {
	echo "Attribut « Régime » déjà présent (n°$attribut_id)\n";
}
$taxonomie = 'pa_regime';
if ( ! taxonomy_exists( $taxonomie ) ) {
	// L'attribut vient d'être créé : WooCommerce ne déclarera la taxonomie qu'au prochain chargement.
	register_taxonomy( $taxonomie, array( 'product' ), array( 'hierarchical' => false, 'show_ui' => false, 'query_var' => true, 'rewrite' => false ) );
}

$termes = array(
	'vegetarien'   => 'Végétarien',
	'sans-gluten'  => 'Sans gluten',
	'sans-lactose' => 'Sans lactose',
);
$ids = array();
$ordre = 0;
foreach ( $termes as $slug => $nom ) {
	$terme = get_term_by( 'slug', $slug, $taxonomie );
	if ( ! $terme ) {
		$res   = wp_insert_term( $nom, $taxonomie, array( 'slug' => $slug ) );
		$terme = get_term( $res['term_id'], $taxonomie );
	}
	update_term_meta( $terme->term_id, 'order', $ordre++ );
	$ids[ $slug ] = (int) $terme->term_id;
}

// Régimes par plat (UGS), d'après les allergènes de l'IT-4 :
// végétarien = ni viande ni poisson ni mollusque ; sans gluten / sans lactose = allergène absent.
$regimes = array(
	'CH-E01' => array( 'vegetarien', 'sans-gluten', 'sans-lactose' ), // Œuf dur mayonnaise : œufs, moutarde
	'CH-E02' => array( 'vegetarien', 'sans-gluten', 'sans-lactose' ), // Poireaux vinaigrette : moutarde, sulfites
	'CH-E03' => array( 'sans-gluten' ),                               // Escargots : lait, mollusques
	'CH-P01' => array( 'sans-gluten' ),                               // Pièce du boucher : lait, œufs
	'CH-P02' => array( 'sans-lactose' ),                              // Bœuf bourguignon : gluten, œufs, sulfites
	'CH-P03' => array( 'sans-gluten', 'sans-lactose' ),               // Volaille rôtie : aucun
	'CH-P04' => array( 'sans-gluten', 'sans-lactose' ),               // Pot-au-feu : céleri
	'CH-P05' => array( 'vegetarien' ),                                // Spaghetti aux légumes : gluten, lait
	'CH-D01' => array( 'vegetarien', 'sans-gluten' ),                 // Mousse au chocolat : œufs, lait, soja
	'CH-D02' => array( 'vegetarien' ),                                // Profiteroles : gluten, œufs, lait, fruits à coque
	'CH-D03' => array( 'vegetarien' ),                                // Baba au rhum : gluten, œufs, lait
);

$lookup = null;
if ( class_exists( '\Automattic\WooCommerce\Internal\ProductAttributesLookup\LookupDataStore' ) ) {
	$lookup = wc_get_container()->get( \Automattic\WooCommerce\Internal\ProductAttributesLookup\LookupDataStore::class );
}

foreach ( $regimes as $ugs => $slugs ) {
	$produit_id = wc_get_product_id_by_sku( $ugs );
	if ( ! $produit_id ) {
		echo "- $ugs introuvable, ignoré\n";
		continue;
	}
	$produit   = wc_get_product( $produit_id );
	$attributs = $produit->get_attributes();
	$attr      = new WC_Product_Attribute();
	$attr->set_id( $attribut_id );
	$attr->set_name( $taxonomie );
	$attr->set_options( array_map( fn( $s ) => $ids[ $s ], $slugs ) );
	$attr->set_position( isset( $attributs[ $taxonomie ] ) ? $attributs[ $taxonomie ]->get_position() : count( $attributs ) );
	$attr->set_visible( true );    // Affiché dans l'onglet d'informations de la fiche.
	$attr->set_variation( false ); // Sert au filtrage, pas aux options de commande.
	$attributs[ $taxonomie ] = $attr;
	$produit->set_attributes( $attributs );
	$produit->save();
	if ( $lookup ) {
		$lookup->create_data_for_product( $produit ); // Table utilisée par le filtre « Régime ».
	}
	echo '- ' . $produit->get_name() . ' : ' . implode( ', ', array_map( fn( $s ) => $termes[ $s ], $slugs ) ) . "\n";
}
delete_transient( 'wc_layered_nav_counts_' . $taxonomie );
wc_delete_product_transients();

// ---------------------------------------------------------------
// 2. Widgets de la zone « Filtres de la carte »
// ---------------------------------------------------------------
$n = 18; // Numéro d'instance propre à l'IT-18 (évite tout conflit avec d'autres widgets).
update_option( 'widget_woocommerce_layered_nav_filters', array( $n => array( 'title' => 'Filtres actifs' ), '_multiwidget' => 1 ) );
update_option( 'widget_woocommerce_price_filter', array( $n => array( 'title' => 'Prix' ), '_multiwidget' => 1 ) );
update_option(
	'widget_woocommerce_layered_nav',
	array(
		$n             => array(
			'title'        => 'Régime',
			'attribute'    => 'regime',
			'display_type' => 'list',
			'query_type'   => 'and', // Végétarien + Sans gluten = plats qui sont les deux à la fois.
		),
		'_multiwidget' => 1,
	)
);
$zones                     = wp_get_sidebars_widgets();
$zones['chartier-filtres'] = array(
	"woocommerce_layered_nav_filters-$n",
	"woocommerce_price_filter-$n",
	"woocommerce_layered_nav-$n",
);
wp_set_sidebars_widgets( $zones );
echo "\nZone « Filtres de la carte » : filtres actifs, prix, régime\n";
