<?php
/**
 * IT-16 : l'accueil devient une page statique « Accueil » (Réglages → Lecture),
 * affichée par le template front-page.php du thème. Relançable.
 * Local : ddev wp eval-file wp-content/themes/chartier-child/docs/accueil-it16.php
 * Prod  : wp eval-file wp-content/themes/chartier-child/docs/accueil-it16.php
 */
echo 'Avant : show_on_front=' . get_option( 'show_on_front' ) . ', page_on_front=' . get_option( 'page_on_front' ) . "\n";

// Réutilise la page d'accueil statique déjà réglée, sinon une page « accueil », sinon la crée.
$page = ( 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' ) ) ? get_post( get_option( 'page_on_front' ) ) : get_page_by_path( 'accueil' );
if ( ! $page ) {
	$id = wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => 'Accueil',
			'post_name'   => 'accueil',
		)
	);
	echo "Page « Accueil » créée (n°$id)\n";
} else {
	$id = $page->ID;
	echo "Page d'accueil déjà présente : « " . $page->post_title . " » (n°$id)\n";
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $id );
echo 'Après : show_on_front=page, page_on_front=' . $id . ' → ' . home_url( '/' ) . "\n\n";

echo "3 derniers produits (Nouveautés) :\n";
$q = chartier_latest_products_query( 3 );
foreach ( $q->posts as $p ) {
	echo '- ' . $p->post_title . ' (' . $p->post_date . ")\n";
}
