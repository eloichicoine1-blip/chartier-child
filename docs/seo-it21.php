<?php
/**
 * IT-21 : SEO avec Yoast SEO. Relançable.
 * Balise Title et Meta description personnalisées pour chaque plat, chaque catégorie,
 * la carte et l'accueil (US-21.1). Les balises Open Graph sont générées par Yoast à
 * partir de ces textes et de la photo du plat (US-21.2 / 21.3).
 * Prérequis : wp plugin install wordpress-seo --activate
 *             wp language plugin install wordpress-seo fr_FR
 * Local : ddev wp eval-file wp-content/themes/chartier-child/docs/seo-it21.php
 * Prod  : wp eval-file wp-content/themes/chartier-child/docs/seo-it21.php
 * Puis  : wp yoast index --reindex --skip-confirmation   (met à jour les « indexables » de Yoast)
 */
if ( ! class_exists( 'WPSEO_Meta' ) ) {
	exit( "Yoast SEO absent ou inactif : installe-le puis relance.\n" );
}

// Titres ≤ 60 caractères, descriptions ≤ 155 (ce que Google affiche sans couper).
$plats = array(
	'CH-E01' => array( 'Œuf dur mayonnaise à emporter – Bouillon Chartier', 'L\'œuf mayo du Bouillon Chartier, servi depuis 1896 : mayonnaise maison ou mimosa. Commandez en ligne et retirez au comptoir sans faire la queue.' ),
	'CH-E02' => array( 'Poireaux vinaigrette à emporter – Bouillon Chartier', 'Poireaux tendres, vinaigrette à la moutarde ou sans : une entrée végétarienne et sans gluten du Bouillon Chartier, en click & collect.' ),
	'CH-E03' => array( 'Escargots de Bourgogne – Bouillon Chartier', 'Escargots de Bourgogne au beurre persillé, par 6 ou 12. Commandez en ligne, retirez au comptoir ou faites-vous livrer à Paris.' ),
	'CH-P01' => array( 'Pièce du boucher, frites – Bouillon Chartier', 'Pièce du boucher grillée à votre cuisson, frites, sauce poivre ou béarnaise. Un classique du bouillon parisien, en retrait ou livré à Paris.' ),
	'CH-P02' => array( 'Bœuf bourguignon, coquillettes – Bouillon Chartier', 'Bœuf mijoté au vin rouge, coquillettes ou pommes vapeur : le bourguignon du Bouillon Chartier, à commander en ligne et à retirer au comptoir.' ),
	'CH-P03' => array( 'Volaille rôtie, frites – Bouillon Chartier', 'Volaille rôtie, cuisse ou blanc, et ses frites : un plat sans gluten ni lactose du Bouillon Chartier, en click & collect ou livré à Paris.' ),
	'CH-P04' => array( 'Pot-au-feu à emporter – Bouillon Chartier', 'Le pot-au-feu du bouillon, avec ou sans os à moelle : bœuf, légumes et bouillon comme en 1896. Commandez en ligne, retirez sans faire la queue.' ),
	'CH-P05' => array( 'Spaghetti aux légumes – Bouillon Chartier', 'Spaghetti aux légumes, parmesan à part ou sans fromage : le plat végétarien du Bouillon Chartier, à emporter ou livré à Paris.' ),
	'CH-D01' => array( 'Mousse au chocolat – Bouillon Chartier', 'Mousse au chocolat, avec ou sans chantilly : un dessert végétarien et sans gluten du Bouillon Chartier, à commander en click & collect.' ),
	'CH-D02' => array( 'Profiteroles, sauce chocolat – Bouillon Chartier', 'Choux, glace vanille et sauce chocolat chaude, par 2 ou 3 : le dessert de bistrot du Bouillon Chartier, à retirer au comptoir ou livré à Paris.' ),
	'CH-D03' => array( 'Baba au rhum – Bouillon Chartier', 'Baba imbibé au rhum, avec ou sans chantilly : un dessert de bistrot du Bouillon Chartier, à commander en ligne et à retirer sans faire la queue.' ),
);
$categories = array(
	'entrees'  => array( 'Entrées à emporter – Bouillon Chartier', 'Œuf mayo, poireaux vinaigrette, escargots : les entrées du Bouillon Chartier depuis 1896, à commander en ligne et à retirer au comptoir.' ),
	'plats'    => array( 'Plats de bistrot à emporter – Bouillon Chartier', 'Pot-au-feu, bœuf bourguignon, volaille rôtie : les plats du Bouillon Chartier en click & collect ou livrés à Paris.' ),
	'desserts' => array( 'Desserts de bistrot – Bouillon Chartier', 'Mousse au chocolat, profiteroles, baba au rhum : les desserts du Bouillon Chartier, à emporter ou livrés à Paris.' ),
);
$pages = array(
	(int) wc_get_page_id( 'shop' )        => array( 'La carte à emporter – Bouillon Chartier', 'Toute la carte du Bouillon Chartier en click & collect : commandez en ligne, retirez au comptoir sans faire la queue ou faites-vous livrer à Paris.' ),
	(int) get_option( 'page_on_front' )   => array( 'Bouillon Chartier en click & collect, sans la queue', 'Commandez le Bouillon Chartier en ligne, choisissez votre créneau, retirez au comptoir (Grands Boulevards, Montparnasse, Gare de l\'Est).' ),
);

$verifie = function ( $titre, $desc ) {
	$alerte = array();
	if ( mb_strlen( $titre ) > 60 ) {
		$alerte[] = 'titre ' . mb_strlen( $titre ) . ' car.';
	}
	if ( mb_strlen( $desc ) > 155 ) {
		$alerte[] = 'description ' . mb_strlen( $desc ) . ' car.';
	}
	return $alerte ? '  ⚠ ' . implode( ', ', $alerte ) : '';
};

echo "Plats :\n";
foreach ( $plats as $ugs => list( $titre, $desc ) ) {
	$id = wc_get_product_id_by_sku( $ugs );
	if ( ! $id ) {
		echo "- $ugs introuvable\n";
		continue;
	}
	WPSEO_Meta::set_value( 'title', $titre, $id );
	WPSEO_Meta::set_value( 'metadesc', $desc, $id );
	echo "- $titre" . $verifie( $titre, $desc ) . "\n";
}

echo "\nCatégories :\n";
foreach ( $categories as $slug => list( $titre, $desc ) ) {
	$terme = get_term_by( 'slug', $slug, 'product_cat' );
	if ( ! $terme ) {
		echo "- $slug introuvable\n";
		continue;
	}
	WPSEO_Taxonomy_Meta::set_value( $terme->term_id, 'product_cat', 'wpseo_title', $titre );
	WPSEO_Taxonomy_Meta::set_value( $terme->term_id, 'product_cat', 'wpseo_desc', $desc );
	echo "- $titre" . $verifie( $titre, $desc ) . "\n";
}

echo "\nPages :\n";
foreach ( $pages as $id => list( $titre, $desc ) ) {
	if ( $id <= 0 ) {
		continue;
	}
	WPSEO_Meta::set_value( 'title', $titre, $id );
	WPSEO_Meta::set_value( 'metadesc', $desc, $id );
	echo '- ' . get_the_title( $id ) . " : $titre" . $verifie( $titre, $desc ) . "\n";
}
// Image de partage de l'accueil (Open Graph) : la photo de la salle du thème.
WPSEO_Meta::set_value( 'opengraph-image', get_stylesheet_directory_uri() . '/assets/img/accueil-salle.jpg', (int) get_option( 'page_on_front' ) );

// Nom du site vu par Google : WordPress enregistre « & » sous la forme « &amp; »,
// que Yoast réaffichait tel quel (« Click &amp; Collect »). On lui donne le nom en clair.
WPSEO_Options::set( 'website_name', 'Bouillon Chartier · Click & Collect' );
echo "\nNom du site pour Google : " . WPSEO_Options::get( 'website_name' ) . "\n";

echo "\nÀ lancer ensuite : wp yoast index --reindex --skip-confirmation\n";
