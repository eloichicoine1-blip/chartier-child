<?php
/**
 * IT-22 : Mentions légales, CGV et politique de confidentialité. Relançable.
 * - Crée ou met à jour les pages « Mentions légales » (/mentions-legales/) et
 *   « Conditions générales de vente » (/cgv/) : le pied de page les affiche
 *   tout seul (fonction chartier_footer_page_link, section 4).
 * - Publie la « Politique de confidentialité » et la déclare à WordPress.
 * - US-22.3 : déclare la page CGV dans WooCommerce › Réglages › Avancé ›
 *   « Page des conditions générales » : WooCommerce ajoute alors au paiement une
 *   case obligatoire « J'ai lu et j'accepte les conditions générales ».
 * Les montants (frais de préparation, livraison) sont lus dans les réglages au
 * moment du script : relancer le script après les avoir modifiés.
 * Site fictif (projet étudiant) : aucune vente réelle, identifiants fictifs.
 * Local : ddev wp eval-file wp-content/themes/chartier-child/docs/legal-it22.php
 * Prod  : wp eval-file wp-content/themes/chartier-child/docs/legal-it22.php
 */
$euro = function ( $montant ) {
	return number_format( (float) $montant, 2, ',', ' ' ) . ' €';
};
$tranches = chartier_weight_tiers();
$t1 = $tranches[0];
$t2 = $tranches[1];
$t3 = $tranches[2];
$avertissement = '<p><strong>Site fictif réalisé dans le cadre d\'un projet étudiant (SAé 3.01, BUT MMI, Université de Limoges). Aucune commande n\'est réellement préparée, livrée ni payée. Ce site n\'est pas affilié au Bouillon Chartier.</strong></p>';

$pages = array();

$pages['mentions-legales'] = array(
	'Mentions légales',
	$avertissement . '
<h2>Éditeur du site</h2>
<p>Bouillon Chartier · Click &amp; Collect (enseigne fictive)<br>7 rue du Faubourg Montmartre, 75009 Paris<br>SIRET : 000 000 000 00000 (fictif) – TVA intracommunautaire : FR00 000000000 (fictif)</p>
<p>Site conçu et réalisé par Eloi Chicoine, étudiant en BUT Métiers du Multimédia et de l\'Internet (MMI), IUT du Limousin, Université de Limoges.</p>
<h2>Directeur de la publication</h2>
<p>Eloi Chicoine.</p>
<h2>Hébergement</h2>
<p>Université de Limoges – serveur pédagogique mmi.unilim.fr<br>33 rue François Mitterrand, 87032 Limoges Cedex 1.</p>
<h2>Propriété intellectuelle</h2>
<p>La marque, le logo et les photographies du Bouillon Chartier appartiennent à leurs propriétaires respectifs. Ils sont utilisés ici uniquement à des fins pédagogiques, sans exploitation commerciale. Le code du thème et la mise en page ont été réalisés pour ce projet.</p>
<h2>Données personnelles</h2>
<p>Les informations recueillies lors d\'une commande servent uniquement à la traiter. Voir la <a href="' . esc_url( get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/politique-de-confidentialite/' ) ) . '">politique de confidentialité</a>.</p>
<h2>Cookies</h2>
<p>Le site n\'utilise que les cookies nécessaires à son fonctionnement : panier, session de commande et connexion au compte client. Aucun cookie publicitaire ni de mesure d\'audience.</p>',
);

$pages['cgv'] = array(
	'Conditions générales de vente',
	$avertissement . '
<h2>1. Objet</h2>
<p>Les présentes conditions régissent les commandes passées sur ce site auprès du Bouillon Chartier · Click &amp; Collect (ci-après « le restaurant ») : plats à retirer au comptoir d\'un des trois bouillons (Grands Boulevards, Montparnasse, Gare de l\'Est) ou livrés à Paris. Toute commande suppose leur acceptation, matérialisée par la case à cocher avant la validation.</p>
<h2>2. Plats</h2>
<p>Les plats sont décrits sur leur fiche : composition, options, allergènes et valeurs nutritionnelles (estimées). Les photographies ne sont pas contractuelles. Un plat épuisé ne peut pas être commandé.</p>
<h2>3. Prix</h2>
<p>Les prix sont indiqués en euros, toutes taxes comprises (TVA de 10 % de la vente à emporter). S\'y ajoutent, le cas échéant :</p>
<ul>
<li>des frais de préparation de ' . $euro( chartier_prep_fee_amount() ) . ' lorsque le montant des plats est inférieur à ' . $euro( chartier_prep_fee_threshold() ) . ' ;</li>
<li>pour une livraison à Paris, des frais calculés selon le poids de la commande : ' . $euro( $t1['price'] ) . ' jusqu\'à ' . wc_format_localized_decimal( $t1['max'] ) . ' kg, ' . $euro( $t2['price'] ) . ' jusqu\'à ' . wc_format_localized_decimal( $t2['max'] ) . ' kg, ' . $euro( $t3['price'] ) . ' au-delà. Le retrait au comptoir est gratuit.</li>
</ul>
<p>Le montant total est affiché avant la validation de la commande.</p>
<h2>4. Commande</h2>
<p>Le client choisit ses plats, le mode (retrait ou livraison), puis un jour et un créneau parmi ceux proposés, au plus tôt une heure après la commande. Il accepte les présentes conditions, puis valide. Un e-mail de confirmation récapitule la commande et le créneau.</p>
<h2>5. Paiement</h2>
<p>Le paiement s\'effectue par virement bancaire ou, pour un retrait, au comptoir. Aucune donnée bancaire n\'est saisie ni conservée sur ce site.</p>
<h2>6. Retrait et livraison</h2>
<p>Retrait : le client se présente au comptoir de vente à emporter du bouillon choisi, pendant son créneau, avec son numéro de commande. Livraison : uniquement à Paris, à l\'adresse indiquée, pendant le créneau choisi.</p>
<h2>7. Annulation et droit de rétractation</h2>
<p>Le client peut annuler une commande en attente depuis son espace « Mon compte ». Conformément à l\'article L221-28, 4° du Code de la consommation, le droit de rétractation ne s\'applique pas aux denrées susceptibles de se détériorer ou de se périmer rapidement, ce qui est le cas des plats préparés.</p>
<h2>8. Facture</h2>
<p>Une facture au format PDF est établie pour chaque commande validée. Elle est jointe à l\'e-mail de confirmation et téléchargeable dans « Mon compte › Commandes ».</p>
<h2>9. Réclamations</h2>
<p>Toute réclamation peut être adressée au restaurant. En cas de litige, le client peut recourir gratuitement à un médiateur de la consommation (coordonnées fictives dans le cadre de ce projet).</p>
<h2>10. Données personnelles</h2>
<p>Voir la politique de confidentialité.</p>
<h2>11. Droit applicable</h2>
<p>Les présentes conditions sont soumises au droit français.</p>',
);

$ids = array();
foreach ( $pages as $slug => list( $titre, $contenu ) ) {
	$page = get_page_by_path( $slug );
	$data = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $titre,
		'post_name'    => $slug,
		'post_content' => $contenu,
	);
	if ( $page ) {
		$data['ID'] = $page->ID;
		$ids[ $slug ] = wp_update_post( $data );
		echo "Page mise à jour : $titre (n°{$ids[ $slug ]})\n";
	} else {
		$ids[ $slug ] = wp_insert_post( $data );
		echo "Page créée : $titre (n°{$ids[ $slug ]})\n";
	}
}

// Politique de confidentialité : texte adapté au site, page publiée et déclarée à WordPress.
$privacy_id = (int) get_option( 'wp_page_for_privacy_policy' );
$privacy    = array(
	'post_type'    => 'page',
	'post_status'  => 'publish',
	'post_title'   => 'Politique de confidentialité',
	'post_name'    => 'politique-de-confidentialite',
	'post_content' => $avertissement . '
<h2>Données collectées</h2>
<p>Lors d\'une commande : nom, prénom, téléphone, adresse e-mail, et pour une livraison l\'adresse postale ; le contenu de la commande, le mode, le jour et le créneau choisis. Lors d\'un avis : le prénom affiché et le texte de l\'avis.</p>
<h2>Utilisation</h2>
<p>Ces données servent uniquement à préparer, livrer ou remettre la commande, à envoyer les e-mails qui la concernent et à établir la facture. Elles ne sont ni vendues ni transmises à des tiers.</p>
<h2>Durée de conservation</h2>
<p>Les commandes et factures sont conservées pendant la durée légale de conservation des pièces comptables. Le compte client est conservé jusqu\'à sa suppression.</p>
<h2>Vos droits</h2>
<p>Vous pouvez consulter et modifier vos informations dans « Mon compte », et demander l\'accès, la rectification ou la suppression de vos données auprès de l\'éditeur du site (voir les mentions légales). Vous pouvez aussi saisir la CNIL.</p>
<h2>Cookies</h2>
<p>Seuls des cookies techniques sont utilisés : panier, session de commande et connexion au compte.</p>',
);
if ( $privacy_id && get_post( $privacy_id ) ) {
	$privacy['ID'] = $privacy_id;
	wp_update_post( $privacy );
} else {
	$privacy_id = wp_insert_post( $privacy );
	update_option( 'wp_page_for_privacy_policy', $privacy_id );
}
echo "Politique de confidentialité publiée (n°$privacy_id)\n";

// US-22.3 : case « J'ai lu et j'accepte les conditions générales » obligatoire au paiement.
update_option( 'woocommerce_terms_page_id', $ids['cgv'] );
update_option( 'woocommerce_checkout_terms_and_conditions_checkbox_text', 'J\'ai lu et j\'accepte les [terms] du Bouillon Chartier · Click & Collect' );
update_option( 'woocommerce_checkout_privacy_policy_text', 'Vos données personnelles servent uniquement à traiter votre commande. Pour en savoir plus, consultez notre [privacy_policy].' );
echo "Page des CGV déclarée à WooCommerce : case à cocher obligatoire au paiement\n";
echo "\nPied de page « Informations » : CGV, Mentions légales, Confidentialité\n";
