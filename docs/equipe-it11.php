<?php
/**
 * IT-11 : crée les 3 profils de l'équipe et ajoute « L'équipe » au menu principal.
 * Relançable sans doublon.
 * Local : ddev wp eval-file wp-content/themes/chartier-child/docs/equipe-it11.php
 * Prod  : wp eval-file wp-content/themes/chartier-child/docs/equipe-it11.php
 * Profils fictifs (projet pédagogique).
 */
$membres = array(
	array(
		'titre'  => 'Lucien Morel',
		'ordre'  => 1,
		'bio'    => 'Entré comme commis de salle, Lucien connaît chaque table des Grands Boulevards. Il accueille, place et veille à ce que personne n’attende trop longtemps son bouillon.',
		'champs' => array( 'poste' => 'Maître d’hôtel', 'restaurant' => 'grands-boulevards', 'arrivee' => 2009, 'plat_prefere' => 'Pot-au-feu, os à moelle', 'citation' => 'Ici, on note la commande sur la nappe en papier, et on ne se trompe jamais.' ),
	),
	array(
		'titre'  => 'Camille Dufresne',
		'ordre'  => 2,
		'bio'    => 'Camille dirige la cuisine de Montparnasse : bouillons, sauces et desserts maison, préparés chaque matin pour plusieurs centaines de couverts.',
		'champs' => array( 'poste' => 'Cheffe de cuisine', 'restaurant' => 'montparnasse', 'arrivee' => 2017, 'plat_prefere' => 'Bœuf bourguignon, coquillettes', 'citation' => 'Une cuisine simple, mais jamais faite à la va-vite.' ),
	),
	array(
		'titre'  => 'Mehdi Benali',
		'ordre'  => 3,
		'bio'    => 'Chef de rang à la Gare de l’Est, Mehdi s’occupe aussi des commandes à emporter : c’est lui qui prépare et remet les commandes click & collect au comptoir.',
		'champs' => array( 'poste' => 'Chef de rang, comptoir à emporter', 'restaurant' => 'gare-de-l-est', 'arrivee' => 2021, 'plat_prefere' => 'Profiteroles, sauce chocolat', 'citation' => 'Votre numéro de commande, et c’est prêt.' ),
	),
);

foreach ( $membres as $m ) {
	$existant = get_posts( array( 'post_type' => 'membre', 'title' => $m['titre'], 'post_status' => 'any', 'numberposts' => 1 ) );
	$id       = wp_insert_post(
		array(
			'ID'           => $existant ? $existant[0]->ID : 0,
			'post_type'    => 'membre',
			'post_status'  => 'publish',
			'post_title'   => $m['titre'],
			'post_content' => $m['bio'],
			'menu_order'   => $m['ordre'],
		)
	);
	foreach ( $m['champs'] as $nom => $valeur ) {
		update_field( $nom, $valeur, $id );
	}
	echo ( $existant ? 'Mis à jour ' : 'Créé ' ) . $m['titre'] . ' (n°' . $id . ') → ' . get_permalink( $id ) . "\n";
}

// « L'équipe » dans le menu principal (après « La carte »), si absent.
$locations = get_nav_menu_locations();
$menu_id   = $locations['primary'] ?? 0;
if ( $menu_id ) {
	$deja = false;
	foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
		if ( 'membre' === $item->object && 'post_type_archive' === $item->type ) {
			$deja = true;
		}
	}
	if ( ! $deja ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'    => 'L’équipe',
				'menu-item-object'   => 'membre',
				'menu-item-type'     => 'post_type_archive',
				'menu-item-status'   => 'publish',
				'menu-item-position' => 2,
			)
		);
		echo "Lien « L’équipe » ajouté au menu principal\n";
	} else {
		echo "Lien « L’équipe » déjà présent dans le menu\n";
	}
} else {
	echo "Aucun menu à l'emplacement « primary » : ajoute « L’équipe » dans Apparence → Menus\n";
}

flush_rewrite_rules();
echo 'Liste : ' . get_post_type_archive_link( 'membre' ) . "\n";
