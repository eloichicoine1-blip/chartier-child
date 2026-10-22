<?php
/**
 * Thème enfant Chartier (Storefront).
 *
 * Sommaire
 * 1. Styles et scripts
 * 2. Nettoyage de Storefront (CSS du Customizer, sidebar)
 * 3. En-tête (US-2.1)
 * 4. Pied de page (US-2.1)
 * 5. Boutique : onglets de catégories, badges, cartes produit (US-2.3)
 * 6. Fiche produit simple (US-2.4)
 * 7. Produits à options (IT-3)
 * 8. Sélecteur de quantité « − 1 + »
 * 9. Champs SCF sur la fiche produit (IT-4)
 * 10. Panier et expédition (IT-5)
 * 11. Commande : instructions pour le retrait, champs, confirmation (IT-6)
 * 12. Compte client (IT-7)
 * 13. E-mails transactionnels (IT-8)
 * 14. Commandes : annulation par le client (IT-9)
 * 15. Équipe : type de contenu « membre » (IT-11)
 * 16. Collections : taxonomie « collection » (IT-12)
 * 17. Ventes croisées : fiche produit et panier (IT-13)
 * 18. Avis clients (IT-14)
 * 19. Frais de préparation (IT-15)
 * 20. Accueil : derniers produits, tuiles de la carte (IT-16)
 * 21. Filtres de la carte : barre latérale, attribut « Régime », prix (IT-18)
 * 22. Livraison selon le poids : 3 tranches réglables (IT-19)
 * 23. Créneaux de livraison et de retrait (IT-20)
 * 24. Back-office : liste des produits lisible (IT-21)
 * 25. Plat star : le plus vendu, badge et bloc d'accueil (IT-W)
 * 26. Sécurité des formulaires : anti-spam invisible (IT-X)
 * (IT-10 Promotions : codes créés dans le back-office, libellé « Appliquer » en section 10, styles en CSS section 22)
 *
 * @package chartier-child
 */

defined( 'ABSPATH' ) || exit;


/* =========================================================
   1. Styles et scripts
   Storefront charge déjà le style.css du thème enfant
   (versionné avec le numéro « Version » de son en-tête).
   ========================================================= */
function chartier_enqueue_assets() {
	// Polices de la charte : Gloock (titres) et Poppins (textes).
	wp_enqueue_style(
		'chartier-fonts',
		'https://fonts.googleapis.com/css2?family=Gloock&family=Poppins:wght@400;600;700&display=swap',
		array(),
		null
	);

	// Mise à jour du total et du compteur du panier sans recharger la page.
	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_script( 'wc-cart-fragments' );
	}
}
add_action( 'wp_enqueue_scripts', 'chartier_enqueue_assets' );


/* =========================================================
   2. Nettoyage de Storefront
   ========================================================= */

/**
 * Retire le CSS généré par le Customizer de Storefront (couleurs grises par défaut) :
 * nos couleurs viennent uniquement des variables du Design System (style.css).
 */
function chartier_remove_storefront_customizer_css() {
	global $storefront;

	if ( empty( $storefront ) ) {
		return;
	}

	if ( isset( $storefront->customizer ) ) {
		remove_action( 'wp_enqueue_scripts', array( $storefront->customizer, 'add_customizer_css' ), 130 );
	}
	if ( isset( $storefront->woocommerce_customizer ) ) {
		remove_action( 'wp_enqueue_scripts', array( $storefront->woocommerce_customizer, 'add_customizer_css' ), 130 );
	}
	if ( isset( $storefront->woocommerce ) ) {
		remove_action( 'wp_enqueue_scripts', array( $storefront->woocommerce, 'add_customizer_css' ), 140 );
	}
}
add_action( 'wp_enqueue_scripts', 'chartier_remove_storefront_customizer_css', 1 );

/**
 * Pages WooCommerce en pleine largeur à l'IT-2.
 * Exception IT-18 : la carte, ses catégories, collections et la recherche de
 * plats ont la colonne de filtres (section 21).
 */
function chartier_is_shop_context() {
	if ( chartier_is_catalog_context() ) {
		return false; // Carte (IT-18) : colonne de filtres à gauche, voir section 21.
	}
	if ( is_post_type_archive( 'membre' ) || is_singular( 'membre' ) ) {
		return true; // Pages de l'équipe (IT-11) : pleine largeur, sans barre latérale.
	}
	if ( is_front_page() ) {
		return true; // Accueil (IT-16) : pleine largeur, sans barre latérale.
	}
	return function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() );
}

function chartier_full_width_body_class( $classes ) {
	if ( chartier_is_shop_context() ) {
		$classes[] = 'storefront-full-width-content';
	}
	return $classes;
}
add_filter( 'body_class', 'chartier_full_width_body_class' );

function chartier_remove_shop_sidebar() {
	if ( chartier_is_shop_context() ) {
		remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
	}
}
add_action( 'wp', 'chartier_remove_shop_sidebar' );


/* =========================================================
   3. En-tête
   On garde le menu de Storefront (et son bouton burger accessible),
   mais on remplace la mise en page par celle de la maquette.
   Les remove_action se font sur « init » : le functions.php du thème
   enfant est chargé AVANT celui de Storefront.
   ========================================================= */
function chartier_replace_storefront_header() {
	remove_action( 'storefront_header', 'storefront_header_container', 0 );
	remove_action( 'storefront_header', 'storefront_site_branding', 20 );
	remove_action( 'storefront_header', 'storefront_secondary_navigation', 30 );
	remove_action( 'storefront_header', 'storefront_product_search', 40 );
	remove_action( 'storefront_header', 'storefront_header_container_close', 41 );
	remove_action( 'storefront_header', 'storefront_primary_navigation_wrapper', 42 );
	remove_action( 'storefront_header', 'storefront_primary_navigation', 50 );
	remove_action( 'storefront_header', 'storefront_header_cart', 60 );
	remove_action( 'storefront_header', 'storefront_primary_navigation_wrapper_close', 68 );

	add_action( 'storefront_header', 'chartier_header', 10 );
}
add_action( 'init', 'chartier_replace_storefront_header' );

/**
 * Contenu de l'en-tête : bandeau, logo, menu, recherche, panier.
 */
function chartier_header() {
	$logo = get_stylesheet_directory_uri() . '/assets/img/logo-bouillon-chartier-rouge.svg';
	?>
	<p class="header__band">Commandez en ligne, retirez au comptoir sans faire la queue</p>
	<div class="header__bar">
		<a class="header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<img class="header__logo-image" src="<?php echo esc_url( $logo ); ?>" alt="Bouillon Chartier, retour à l'accueil" width="230" height="72">
		</a>

		<?php storefront_primary_navigation(); ?>

		<div class="header__actions">
			<form class="header__search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="header-search-field">Rechercher un plat</label>
				<input class="header__search-field" id="header-search-field" type="search" name="s" placeholder="Rechercher un plat…" value="<?php echo esc_attr( get_search_query() ); ?>">
				<input type="hidden" name="post_type" value="product">
			</form>
			<?php chartier_header_cart(); ?>
		</div>
	</div>
	<?php
}

/**
 * Total et compteur du panier (aussi utilisé pour la mise à jour en AJAX).
 */
function chartier_header_cart() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}
	$count = WC()->cart->get_cart_contents_count();
	?>
	<a class="header__cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
		<span class="header__cart-total"><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></span>
		<span class="header__cart-count" aria-label="<?php echo esc_attr( sprintf( _n( '%d article dans le panier', '%d articles dans le panier', $count, 'chartier-child' ), $count ) ); ?>"><?php echo esc_html( $count ); ?></span>
	</a>
	<?php
}

function chartier_header_cart_fragment( $fragments ) {
	ob_start();
	chartier_header_cart();
	$fragments['a.header__cart'] = ob_get_clean();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'chartier_header_cart_fragment' );


/* =========================================================
   4. Pied de page
   ========================================================= */
function chartier_replace_storefront_footer() {
	remove_action( 'storefront_footer', 'storefront_footer_widgets', 10 );
	remove_action( 'storefront_footer', 'storefront_credit', 20 );
	add_action( 'storefront_footer', 'chartier_footer', 10 );
}
add_action( 'init', 'chartier_replace_storefront_footer' );

/**
 * Lien vers une page seulement si elle existe déjà
 * (CGV et mentions légales arrivent à l'IT-22 : pas de lien mort d'ici là).
 */
function chartier_footer_page_link( $slug, $label ) {
	$page = get_page_by_path( $slug );
	if ( ! $page || 'publish' !== $page->post_status ) {
		return;
	}
	printf( '<li><a class="footer__link" href="%s">%s</a></li>', esc_url( get_permalink( $page ) ), esc_html( $label ) );
}

function chartier_footer() {
	$logo = get_stylesheet_directory_uri() . '/assets/img/logo-bouillon-chartier-blanc.svg';
	?>
	<div class="footer__columns">
		<img class="footer__logo-image" src="<?php echo esc_url( $logo ); ?>" alt="Bouillon Chartier" width="230" height="72" loading="lazy">

		<nav aria-labelledby="footer-commander">
			<p class="footer__title" id="footer-commander">Commander</p>
			<ul class="footer__list">
				<li><a class="footer__link" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">La carte</a></li>
				<li><a class="footer__link" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">Mon compte</a></li>
				<li><a class="footer__link" href="<?php echo esc_url( wc_get_cart_url() ); ?>">Mon panier</a></li>
			</ul>
		</nav>

		<nav aria-labelledby="footer-informations">
			<p class="footer__title" id="footer-informations">Informations</p>
			<ul class="footer__list">
				<?php
				chartier_footer_page_link( 'cgv', 'CGV' );
				chartier_footer_page_link( 'mentions-legales', 'Mentions légales' );
				if ( get_privacy_policy_url() ) {
					printf( '<li><a class="footer__link" href="%s">Confidentialité</a></li>', esc_url( get_privacy_policy_url() ) );
				}
				?>
			</ul>
		</nav>

		<div>
			<p class="footer__title">Nos bouillons</p>
			<ul class="footer__list">
				<li class="footer__text">Grands Boulevards</li>
				<li class="footer__text">Montparnasse</li>
				<li class="footer__text">Gare de l'Est</li>
			</ul>
		</div>
	</div>
	<p class="footer__credit">Site réalisé par Eloi Chicoine · SAé 3.01, BUT MMI Limoges · projet pédagogique, non affilié à Bouillon Chartier</p>
	<?php
}


/* =========================================================
   5. Boutique (US-2.3)
   ========================================================= */

/** Fil d'Ariane : « Accueil › La carte › Plats ». */
function chartier_breadcrumb_defaults( $defaults ) {
	$defaults['delimiter'] = '<span class="breadcrumb-separator" aria-hidden="true">›</span>';
	$defaults['home']      = 'Accueil';
	return $defaults;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'chartier_breadcrumb_defaults', 20 );

/** 3 produits par ligne. */
function chartier_loop_columns() {
	return 3;
}
add_filter( 'loop_shop_columns', 'chartier_loop_columns', 20 );

/**
 * Tuiles de catégories (Entrées / Plats / Desserts) sous le titre de la boutique,
 * composant .category-tile du Design System.
 */
function chartier_category_tabs() {
	if ( ! is_shop() && ! is_product_category() ) {
		return;
	}

	$categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'exclude'    => get_option( 'default_product_cat' ), // « Non classé »
			'orderby'    => 'menu_order', // ordre réglé par glisser-déposer dans Produits › Catégories
		)
	);

	if ( empty( $categories ) || is_wp_error( $categories ) ) {
		return;
	}

	$current = is_product_category() ? get_queried_object_id() : 0;

	echo '<nav class="category-tiles" aria-label="Catégories de la carte">';

	// Sur une catégorie : lien de retour vers toute la carte.
	if ( $current ) {
		printf(
			'<a class="category-tiles__all" href="%s">← Toute la carte</a>',
			esc_url( wc_get_page_permalink( 'shop' ) )
		);
	}

	echo '<ul class="category-tiles__list">';
	foreach ( $categories as $category ) {
		$active = ( $current === $category->term_id );
		printf(
			'<li><a class="category-tile%s" href="%s"%s><span class="category-tile__count">%s</span><span class="category-tile__title">%s</span></a></li>',
			$active ? ' category-tile--active' : '',
			esc_url( get_term_link( $category ) ),
			$active ? ' aria-current="page"' : '',
			esc_html( sprintf( _n( '%d plat', '%d plats', $category->count, 'chartier-child' ), $category->count ) ),
			esc_html( $category->name )
		);
	}
	echo '</ul></nav>';
}
add_action( 'woocommerce_archive_description', 'chartier_category_tabs', 20 );

/** Badge « Promo » au format du Design System. */
function chartier_sale_flash() {
	return '<span class="onsale badge badge--promo">Promo</span>';
}
add_filter( 'woocommerce_sale_flash', 'chartier_sale_flash' );

/** Badge « Épuisé » sur les cartes des produits en rupture. */
function chartier_soldout_badge() {
	global $product;
	if ( $product && ! $product->is_in_stock() ) {
		echo '<span class="badge badge--soldout product-badge--soldout">Épuisé</span>';
	}
}
add_action( 'woocommerce_before_shop_loop_item_title', 'chartier_soldout_badge', 9 );


/* =========================================================
   6. Fiche produit (US-2.4)
   Ordre de la maquette : badge, titre, description courte,
   prix, bouton, catégorie.
   ========================================================= */
function chartier_single_product_order() {
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_show_product_sale_flash', 4 );

	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 25 );
}
add_action( 'init', 'chartier_single_product_order' );

/** L'UGS (référence interne) n'est utile qu'en back-office. */
function chartier_hide_sku_on_front( $enabled ) {
	return is_admin() ? $enabled : false;
}
add_filter( 'wc_product_sku_enabled', 'chartier_hide_sku_on_front' );

/** Rappel du mode de retrait sous la catégorie. */
function chartier_pickup_reminder() {
	echo '<p class="product-pickup">Retrait dans l\'un de nos 3 bouillons</p>';
}
add_action( 'woocommerce_product_meta_end', 'chartier_pickup_reminder' );


/* =========================================================
   7. Produits à options (IT-3)
   ========================================================= */

/** Script du message « options manquantes » (fiches produit uniquement). */
function chartier_enqueue_product_options_script() {
	$version = wp_get_theme()->get( 'Version' );

	if ( is_product() ) {
		wp_enqueue_script( 'chartier-options-produit', get_stylesheet_directory_uri() . '/assets/js/options-produit.js', array(), $version, true );
	}
	if ( is_product() || is_cart() ) {
		wp_enqueue_script( 'chartier-quantite', get_stylesheet_directory_uri() . '/assets/js/quantite.js', array(), $version, true );
	}
}
add_action( 'wp_enqueue_scripts', 'chartier_enqueue_product_options_script' );

/**
 * Prix d'un produit à options : « Dès 11,90 € » au lieu de la fourchette « 11,90 € – 12,40 € »,
 * avec l'ancien prix barré si le produit est en promo (comme la carte produit de la maquette).
 * Le prix exact s'affiche dès que les options sont choisies (US-3.3).
 */
function chartier_variable_price_html( $price, $product ) {
	$min = $product->get_variation_price( 'min', true );
	$max = $product->get_variation_price( 'max', true );

	if ( $min === $max ) {
		return $price;
	}

	$regular_min = $product->get_variation_regular_price( 'min', true );
	$amount      = ( $product->is_on_sale() && $regular_min > $min )
		? wc_format_sale_price( $regular_min, $min )
		: wc_price( $min );

	return '<span class="price-from">Dès</span> ' . $amount;
}
add_filter( 'woocommerce_variable_price_html', 'chartier_variable_price_html', 10, 2 );

/** Bouton des cartes produit à options : libellé de la maquette. */
function chartier_loop_add_to_cart_text( $text, $product ) {
	if ( $product->is_type( 'variable' ) && $product->is_in_stock() ) {
		return 'Choix des options';
	}
	return $text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'chartier_loop_add_to_cart_text', 10, 2 );


/* =========================================================
   8. Sélecteur de quantité « − 1 + » (composant Quantity de la maquette)
   WooCommerce prévoit deux crochets autour du champ quantité :
   on y place les boutons, le script quantite.js les fait fonctionner.
   ========================================================= */
function chartier_quantity_minus_button() {
	echo '<button type="button" class="quantity__button quantity__button--minus" aria-label="Retirer un">−</button>';
}
add_action( 'woocommerce_before_quantity_input_field', 'chartier_quantity_minus_button' );

function chartier_quantity_plus_button() {
	echo '<button type="button" class="quantity__button quantity__button--plus" aria-label="Ajouter un">+</button>';
}
add_action( 'woocommerce_after_quantity_input_field', 'chartier_quantity_plus_button' );

/** Barre « Ajouter au panier » collante de Storefront désactivée (non prévue dans la maquette desktop). */
add_filter( 'theme_mod_storefront_sticky_add_to_cart', '__return_false' );


/* =========================================================
   9. Champs SCF sur la fiche produit (IT-4, US-4.3)
   Les champs sont créés dans le back-office (SCF › Groupes de champs) :
   composition, allergenes, valeurs_nutritionnelles.
   Ici, on ne fait que les afficher, avant le prix (priorité 22 < prix 25).
   ========================================================= */
function chartier_product_scf_fields() {
	// Sécurité : si SCF est désactivé, on n'affiche rien au lieu de provoquer une erreur.
	if ( ! function_exists( 'get_field' ) ) {
		return;
	}

	$fields = array(
		'composition'             => 'Composition',
		'allergenes'              => 'Allergènes',
		'valeurs_nutritionnelles' => 'Valeurs nutritionnelles',
	);

	$rows = '';
	foreach ( $fields as $name => $label ) {
		$value = get_field( $name );

		// Case à cocher (allergènes) : SCF renvoie un tableau de libellés → « Lait, Œufs ».
		if ( is_array( $value ) ) {
			$value = implode( ', ', $value );
		}

		if ( $value ) {
			$rows .= sprintf(
				'<div class="product-info__row"><dt class="product-info__label">%s</dt><dd class="product-info__value">%s</dd></div>',
				esc_html( $label ),
				nl2br( esc_html( $value ) )
			);
		}
	}

	// Aucun champ rempli pour ce plat : pas de bloc vide.
	if ( '' === $rows ) {
		return;
	}

	echo '<section class="product-info" aria-labelledby="product-info-title">';
	echo '<h2 class="product-info__title" id="product-info-title">Composition · Allergènes</h2>';
	echo '<dl class="product-info__list">' . $rows . '</dl>'; // phpcs:ignore -- valeurs déjà échappées ci-dessus.
	echo '</section>';
}
add_action( 'woocommerce_single_product_summary', 'chartier_product_scf_fields', 22 );


/* =========================================================
   10. Panier et expédition (IT-5)
   Le panier utilise le gabarit classique de WooCommerce (shortcode [woocommerce_cart]),
   habillé en « ticket » comme la maquette.
   ========================================================= */

/** Textes du panier alignés sur la maquette. */
function chartier_cart_texts( $translation, $text, $domain ) {
	if ( 'woocommerce' !== $domain ) {
		return $translation;
	}
	$texts = array(
		'Cart totals'         => 'Total',
		'Proceed to checkout' => 'Valider la commande',
		'Billing details'     => 'Vos coordonnées',
		'Billing &amp; Shipping' => 'Vos coordonnées',
		'Apply coupon'        => 'Appliquer',
	);
	return isset( $texts[ $text ] ) ? $texts[ $text ] : $translation;
}
add_filter( 'gettext', 'chartier_cart_texts', 10, 3 );

/** Titre de la ligne des modes d'expédition : « Retrait ou livraison ». */
function chartier_shipping_package_name() {
	return 'Retrait ou livraison';
}
add_filter( 'woocommerce_shipping_package_name', 'chartier_shipping_package_name' );

/**
 * Chaque mode d'expédition sur deux lignes, comme la maquette :
 * « Retrait · Grands Boulevards » puis « Gratuit » (ou le prix de la livraison).
 */
function chartier_shipping_method_label( $label, $method ) {
	$cost = (float) $method->get_cost();
	// IT-19 : get_cost() est le prix HT ; le client doit voir le prix TTC qu'il paiera.
	if ( WC()->cart && WC()->cart->display_prices_including_tax() ) {
		$cost += array_sum( (array) $method->get_taxes() );
	}
	$note = $cost > 0 ? wc_price( $cost ) : 'Gratuit';
	$weight = $method->get_meta_data()['Poids de la commande'] ?? '';
	if ( $cost > 0 && '' !== $weight ) {
		$note .= ' · commande de ' . esc_html( $weight ); // IT-19 : le client voit sur quoi repose le tarif.
	}

	return sprintf(
		'<span class="shipping-method__name">%s</span><span class="shipping-method__note">%s</span>',
		esc_html( $method->get_label() ),
		wp_kses_post( $note )
	);
}
add_filter( 'woocommerce_cart_shipping_method_full_label', 'chartier_shipping_method_label', 10, 2 );


/* =========================================================
   11. Commande (IT-6)
   - US-6.1 : texte « Instructions pour le retrait » réglable dans
     WooCommerce → Réglages → Expédition → Options d'expédition.
   - Le texte s'affiche dans le formulaire quand un retrait est choisi ;
     l'adresse n'est alors plus demandée (elle ne sert qu'à la livraison).
   ========================================================= */

/** Texte par défaut, utilisé tant que l'admin n'a rien saisi. */
function chartier_pickup_instructions_default() {
	return 'Présentez-vous au comptoir de vente à emporter avec votre numéro de commande. Pas besoin de faire la queue.';
}

/** Texte réglé dans le back-office. */
function chartier_pickup_instructions() {
	return (string) get_option( 'chartier_pickup_instructions', chartier_pickup_instructions_default() );
}

/** Ajoute le champ dans la section « Options d'expédition » des réglages WooCommerce. */
function chartier_pickup_instructions_setting( $settings ) {
	$new = array();
	foreach ( $settings as $setting ) {
		if ( isset( $setting['type'], $setting['id'] ) && 'sectionend' === $setting['type'] && 'shipping_options' === $setting['id'] ) {
			$new[] = array(
				'title'    => 'Instructions pour le retrait',
				'desc'     => 'Affiché dans le formulaire de commande et sur la confirmation quand le client choisit un retrait.',
				'desc_tip' => true,
				'id'       => 'chartier_pickup_instructions',
				'type'     => 'textarea',
				'default'  => chartier_pickup_instructions_default(),
				'css'      => 'min-width: 400px; height: 90px;',
			);
		}
		$new[] = $setting;
	}
	return $new;
}
add_filter( 'woocommerce_get_settings_shipping', 'chartier_pickup_instructions_setting' );

/** Le mode d'expédition est-il un retrait au comptoir ? */
function chartier_is_pickup_method( $method_id ) {
	return 0 === strpos( (string) $method_id, 'local_pickup' ) || 0 === strpos( (string) $method_id, 'pickup_location' );
}

/** Le client a-t-il choisi un retrait (d'après la session) ? */
function chartier_is_pickup_chosen() {
	if ( ! function_exists( 'WC' ) || ! WC()->session ) {
		return false;
	}
	foreach ( (array) WC()->session->get( 'chosen_shipping_methods', array() ) as $method_id ) {
		if ( chartier_is_pickup_method( $method_id ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Champs dans l'ordre de la maquette : prénom, nom, téléphone, e-mail, puis l'adresse.
 * L'adresse (masquée en CSS pour un retrait) n'est plus obligatoire quand un retrait est choisi.
 */
function chartier_checkout_fields( $fields ) {
	unset( $fields['billing']['billing_company'] );

	$layout = array(
		'billing_first_name' => array( 10, 'form-row-first' ),
		'billing_last_name'  => array( 20, 'form-row-last' ),
		'billing_phone'      => array( 22, 'form-row-first' ),
		'billing_email'      => array( 24, 'form-row-last' ),
	);
	foreach ( $layout as $key => $setup ) {
		if ( isset( $fields['billing'][ $key ] ) ) {
			$fields['billing'][ $key ]['priority'] = $setup[0];
			$fields['billing'][ $key ]['class']    = array( $setup[1] );
		}
	}
	if ( isset( $fields['billing']['billing_phone'] ) ) {
		$fields['billing']['billing_phone']['required'] = true;
	}
	// Le code postal décide de la zone : la livraison n'apparaît que pour Paris.
	if ( isset( $fields['billing']['billing_postcode'] ) ) {
		$fields['billing']['billing_postcode']['description'] = 'La livraison est proposée à Paris (75…) ; partout ailleurs, retrait au comptoir.';
	}

	if ( chartier_is_pickup_chosen() ) {
		foreach ( array( 'billing_country', 'billing_address_1', 'billing_address_2', 'billing_postcode', 'billing_city', 'billing_state' ) as $key ) {
			if ( isset( $fields['billing'][ $key ] ) ) {
				$fields['billing'][ $key ]['required'] = false;
			}
		}
	}
	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'chartier_checkout_fields', 20 );

/**
 * Le téléphone fait partie des champs d'adresse de WooCommerce : le script qui adapte
 * les champs au pays lui redonne sa position par défaut (après l'adresse).
 * On fixe donc sa position ici, à la source.
 */
function chartier_default_address_fields( $fields ) {
	if ( isset( $fields['phone'] ) ) {
		$fields['phone']['priority'] = 22;
		$fields['phone']['class']    = array( 'form-row-first' );
	}
	return $fields;
}
add_filter( 'woocommerce_default_address_fields', 'chartier_default_address_fields' );

/** Encadré « Instructions pour le retrait » sous les coordonnées (style ticket). */
function chartier_pickup_instructions_box() {
	$text = trim( chartier_pickup_instructions() );
	if ( '' === $text ) {
		return;
	}
	printf(
		'<div class="pickup-instructions" role="note"><p class="pickup-instructions__title">Instructions pour le retrait</p><p class="pickup-instructions__text">%s</p></div>',
		nl2br( esc_html( $text ) )
	);
}
add_action( 'woocommerce_after_checkout_billing_form', 'chartier_pickup_instructions_box' );

/** Script qui affiche les instructions ou l'adresse selon le mode choisi. */
function chartier_enqueue_checkout_script() {
	if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() ) {
		wp_enqueue_script( 'chartier-commande', get_stylesheet_directory_uri() . '/assets/js/commande.js', array( 'jquery' ), wp_get_theme()->get( 'Version' ), true );
	}
}
add_action( 'wp_enqueue_scripts', 'chartier_enqueue_checkout_script' );

/** Page de confirmation : « Oui chef ! » (maquette), puis les instructions si c'est un retrait. */
function chartier_order_received_text() {
	return '<span class="order-received__title">Oui chef !</span> Votre commande est bien reçue.';
}
add_filter( 'woocommerce_thankyou_order_received_text', 'chartier_order_received_text' );

/** La commande est-elle à retirer au comptoir ? (utilisé par la confirmation et les e-mails) */
function chartier_order_has_pickup( $order ) {
	if ( ! $order instanceof WC_Order ) {
		return false;
	}
	foreach ( $order->get_shipping_methods() as $item ) {
		if ( chartier_is_pickup_method( $item->get_method_id() ) ) {
			return true;
		}
	}
	return false;
}

function chartier_thankyou_pickup_instructions( $order_id ) {
	if ( chartier_order_has_pickup( wc_get_order( $order_id ) ) ) {
		chartier_pickup_instructions_box();
	}
}
add_action( 'woocommerce_thankyou', 'chartier_thankyou_pickup_instructions', 5 );


/* =========================================================
   12. Compte client (IT-7)
   Inscription et connexion se règlent dans WooCommerce → Réglages →
   Comptes et confidentialité ; le thème ne fait que l'habillage.
   ========================================================= */

/** Menu du compte : pas de « Téléchargements » (aucun produit téléchargeable). */
function chartier_account_menu_items( $items ) {
	unset( $items['downloads'] );
	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'chartier_account_menu_items' );


/* =========================================================
   13. E-mails transactionnels (IT-8)
   - US-8.1 : expéditeur, couleurs et pied de page réglés dans
     WooCommerce → Réglages → E-mails.
   - US-8.2 : gabarits surchargés dans woocommerce/emails/
     (email-header.php, email-footer.php) + styles ci-dessous.
   - US-8.3 : instructions de retrait ajoutées aux e-mails client.
   Les clients mail ignorent les polices web : Gloock est remplacée par Georgia,
   Poppins par Helvetica / Arial.
   ========================================================= */

/** Styles ajoutés à ceux de WooCommerce (ils sont ensuite recopiés « en ligne » dans chaque balise). */
function chartier_email_styles( $css ) {
	$css .= '
		#template_container { border: 0; border-radius: 0; box-shadow: none; }
		.chartier-band { background-color: #111111; color: #ffffff; padding: 10px 16px; font-family: Helvetica, Arial, sans-serif; font-size: 11px; font-weight: bold; letter-spacing: 1px; text-align: center; text-transform: uppercase; }
		.chartier-logo { background-color: #ffffff; padding: 24px 16px; text-align: center; border-bottom: 1px solid #111111; }
		.chartier-logo img { display: inline-block; width: 220px; height: auto; border: 0; }
		#template_header { background-color: #ffffff; color: #111111; border-radius: 0; }
		#template_header h1 { color: #111111; font-family: Georgia, "Times New Roman", serif; font-size: 32px; font-weight: normal; line-height: 1.15; text-align: left; text-shadow: none; }
		#body_content_inner, #body_content td, #body_content th { color: #111111; font-family: Helvetica, Arial, sans-serif; }
		h2, h3 { color: #111111; font-family: Helvetica, Arial, sans-serif; }
		#template_footer { background-color: #C31729; }
		#template_footer #credit { padding: 32px 24px; color: #ffffff; font-family: Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.5; text-align: center; }
		#template_footer #credit p { color: #ffffff; margin: 8px 0 0; }
		#template_footer #credit a { color: #ffffff; }
		.chartier-footer-places { font-weight: bold; letter-spacing: 1px; text-transform: uppercase; font-size: 11px; }
		.pickup-instructions { margin: 0 0 24px; padding: 16px 20px; border: 2px dashed #9B9493; border-radius: 8px; background-color: #EDE7DC; }
		.pickup-instructions__title { margin: 0 0 4px; font-size: 11px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
		.pickup-instructions__text { margin: 0; }
	';
	return $css;
}
add_filter( 'woocommerce_email_styles', 'chartier_email_styles' );

/**
 * US-8.3 : instructions de retrait dans les e-mails envoyés au client
 * (commande en attente, en cours, terminée, facture) quand la commande est un retrait.
 */
function chartier_email_pickup_instructions( $order, $sent_to_admin, $plain_text ) {
	if ( $sent_to_admin || ! chartier_order_has_pickup( $order ) ) {
		return;
	}
	$text = trim( chartier_pickup_instructions() );
	if ( '' === $text ) {
		return;
	}
	if ( $plain_text ) {
		echo "\n" . esc_html( strtoupper( 'Instructions pour le retrait' ) ) . "\n" . esc_html( $text ) . "\n\n";
		return;
	}
	chartier_pickup_instructions_box();
}
add_action( 'woocommerce_email_before_order_table', 'chartier_email_pickup_instructions', 5, 3 );


/* =========================================================
   14. Commandes (IT-9)
   Statuts, stock et remboursements sont gérés par WooCommerce dans le
   back-office. Le thème ajoute seulement l'annulation par le client
   d'une commande en attente de virement (US-9.3).
   ========================================================= */

/**
 * Par défaut, le client ne peut annuler qu'une commande « En attente de paiement » ou « Échouée ».
 * Une commande par virement est « En attente » (on-hold) tant que le virement n'est pas reçu :
 * on autorise aussi son annulation depuis Mon compte → Commandes.
 */
function chartier_cancellable_statuses( $statuses ) {
	$statuses[] = 'on-hold';
	return array_unique( $statuses );
}
add_filter( 'woocommerce_valid_order_statuses_for_cancel', 'chartier_cancellable_statuses' );

/** Script de confirmation avant l'annulation (« Annuler la commande n°… ? »). */
function chartier_enqueue_account_script() {
	if ( function_exists( 'is_account_page' ) && is_account_page() ) {
		wp_enqueue_script( 'chartier-compte', get_stylesheet_directory_uri() . '/assets/js/compte.js', array(), wp_get_theme()->get( 'Version' ), true );
	}
}
add_action( 'wp_enqueue_scripts', 'chartier_enqueue_account_script' );


/* =========================================================
   15. Équipe (IT-11)
   - Type de contenu « membre » déclaré en PHP (register_post_type), comme
     dans le cours : il est versionné avec le thème, un git pull le crée en prod.
   - Champs du profil (poste, bouillon, arrivée, plat préféré, citation) :
     groupe SCF enregistré en JSON local dans acf-json/ (versionné lui aussi).
   - Affichage : archive-membre.php (liste) et single-membre.php (fiche),
     selon la hiérarchie des templates.
   ========================================================= */

/** US-11.1 : menu « Équipe » dans le back-office, une page par membre. */
function chartier_register_membre() {
	register_post_type(
		'membre',
		array(
			'labels'        => array(
				'name'               => 'Équipe',
				'singular_name'      => 'Membre de l’équipe',
				'menu_name'          => 'Équipe',
				'all_items'          => 'Tous les membres',
				'add_new'            => 'Ajouter un membre',
				'add_new_item'       => 'Ajouter un membre de l’équipe',
				'edit_item'          => 'Modifier le membre',
				'new_item'           => 'Nouveau membre',
				'view_item'          => 'Voir la fiche',
				'search_items'       => 'Rechercher un membre',
				'not_found'          => 'Aucun membre',
				'not_found_in_trash' => 'Aucun membre dans la corbeille',
				'featured_image'     => 'Portrait',
				'set_featured_image' => 'Choisir le portrait',
			),
			'public'        => true,
			'has_archive'   => 'equipe',
			'rewrite'       => array(
				'slug'       => 'equipe',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-groups',
			'menu_position' => 25,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'show_in_rest'  => true,
		)
	);
}
add_action( 'init', 'chartier_register_membre' );

/** La liste suit l'ordre choisi dans le back-office (« Ordre » de chaque membre), puis le nom. */
function chartier_order_membres( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_post_type_archive( 'membre' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		$query->set( 'posts_per_page', -1 );
	}
}
add_action( 'pre_get_posts', 'chartier_order_membres' );

/**
 * Fil d'Ariane WooCommerce : « Accueil › L'équipe » sur la liste comme sur
 * une fiche (sinon il affiche « Équipe » puis « Membre de l'équipe »).
 */
function chartier_membre_breadcrumb( $crumbs ) {
	if ( is_post_type_archive( 'membre' ) || is_singular( 'membre' ) ) {
		$crumbs[1] = array( 'L’équipe', get_post_type_archive_link( 'membre' ) );
	}
	return $crumbs;
}
add_filter( 'woocommerce_get_breadcrumb', 'chartier_membre_breadcrumb' );

/**
 * Portrait du membre : sa photo (image mise en avant) si elle existe,
 * sinon ses initiales dans un médaillon rouge.
 */
function chartier_member_portrait( $class ) {
	if ( has_post_thumbnail() ) {
		the_post_thumbnail( 'medium_large', array( 'class' => $class . ' ' . $class . '--photo' ) );
		return;
	}
	$initials = '';
	foreach ( preg_split( '/\s+/', trim( get_the_title() ) ) as $word ) {
		$initials .= mb_strtoupper( mb_substr( $word, 0, 1 ) );
	}
	printf(
		'<span class="%1$s %1$s--initials" aria-hidden="true">%2$s</span>',
		esc_attr( $class ),
		esc_html( mb_substr( $initials, 0, 2 ) )
	);
}


/* =========================================================
   16. Collections (IT-12)
   - Taxonomie « collection » déclarée en PHP (register_taxonomy) et
     rattachée aux produits seulement : elle est indépendante des
     catégories (product_cat). Versionnée avec le thème, un git pull
     la crée en prod.
   - Back-office : Produits › Collections (ajouter, renommer, décrire,
     ordonner par glisser-déposer), cases à cocher sur chaque produit,
     colonne « Collections » dans la liste des produits.
   - Page dédiée : /collection/<terme>/, template taxonomy-collection.php
     (hiérarchie des templates).
   ========================================================= */

/** US-12.1 : nouveau critère de classement des produits, administrable. */
function chartier_register_collection() {
	register_taxonomy(
		'collection',
		'product',
		array(
			'labels'            => array(
				'name'          => 'Collections',
				'singular_name' => 'Collection',
				'menu_name'     => 'Collections',
				'all_items'     => 'Toutes les collections',
				'edit_item'     => 'Modifier la collection',
				'view_item'     => 'Voir la collection',
				'update_item'   => 'Mettre à jour la collection',
				'add_new_item'  => 'Ajouter une collection',
				'new_item_name' => 'Nom de la nouvelle collection',
				'search_items'  => 'Rechercher une collection',
				'not_found'     => 'Aucune collection',
				'back_to_items' => '← Retour aux collections',
			),
			'public'            => true,
			'hierarchical'      => true, // Cases à cocher dans la fiche produit, comme les catégories.
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'collection',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'chartier_register_collection' );

/** Ordre des collections réglable par glisser-déposer (comme les catégories WooCommerce). */
function chartier_sortable_collection( $taxonomies ) {
	$taxonomies[] = 'collection';
	return $taxonomies;
}
add_filter( 'woocommerce_sortable_taxonomies', 'chartier_sortable_collection' );

/**
 * US-12.3 : tuiles « Nos collections » sur la boutique et sur chaque
 * collection, pour accéder aux pages dédiées. Même composant que les
 * tuiles de catégories (.category-tile), en version compacte.
 */
function chartier_collection_tiles() {
	if ( ! is_shop() && ! is_tax( 'collection' ) ) {
		return;
	}

	$collections = get_terms(
		array(
			'taxonomy'   => 'collection',
			'hide_empty' => true,
			'orderby'    => 'menu_order',
		)
	);

	if ( empty( $collections ) || is_wp_error( $collections ) ) {
		return;
	}

	$current = is_tax( 'collection' ) ? get_queried_object_id() : 0;

	echo '<nav class="category-tiles collection-tiles" aria-labelledby="collection-tiles-title">';
	echo '<h2 class="collection-tiles__title" id="collection-tiles-title">Nos collections</h2>';

	if ( $current ) {
		printf(
			'<a class="category-tiles__all" href="%s">← Toute la carte</a>',
			esc_url( wc_get_page_permalink( 'shop' ) )
		);
	}

	echo '<ul class="category-tiles__list collection-tiles__list">';
	foreach ( $collections as $collection ) {
		$active = ( $current === $collection->term_id );
		printf(
			'<li><a class="category-tile collection-tile%s" href="%s"%s><span class="category-tile__count">%s</span><span class="category-tile__title">%s</span></a></li>',
			$active ? ' category-tile--active' : '',
			esc_url( get_term_link( $collection ) ),
			$active ? ' aria-current="page"' : '',
			esc_html( sprintf( _n( '%d plat', '%d plats', $collection->count, 'chartier-child' ), $collection->count ) ),
			esc_html( $collection->name )
		);
	}
	echo '</ul></nav>';
}
add_action( 'woocommerce_archive_description', 'chartier_collection_tiles', 25 );

/**
 * Fil d'Ariane : « Accueil › La carte › Végétarien » (comme une catégorie),
 * au lieu de « Collections », qui n'est pas une page.
 */
function chartier_collection_breadcrumb( $crumbs ) {
	if ( is_tax( 'collection' ) ) {
		foreach ( $crumbs as $i => $crumb ) {
			if ( 'Collections' === $crumb[0] ) {
				$crumbs[ $i ] = array( get_the_title( wc_get_page_id( 'shop' ) ), wc_get_page_permalink( 'shop' ) );
			}
		}
	}
	return $crumbs;
}
add_filter( 'woocommerce_get_breadcrumb', 'chartier_collection_breadcrumb' );


/* =========================================================
   17. Ventes croisées (IT-13)
   - US-13.1 : les plats complémentaires se règlent dans le champ
     « Ventes croisées » de chaque produit (onglet Produits liés),
     sans extension. Un seul champ, affiché à deux endroits.
   - US-13.2 / 13.3 : « Pour compléter votre repas » sur la fiche
     produit et sous le panier, avec les mêmes cartes que la boutique
     (gabarit WooCommerce cart/cross-sells.php).
   - Les « Produits apparentés » automatiques sont retirés : seules
     les suggestions choisies par l'admin restent.
   ========================================================= */

/** Titre des suggestions. */
function chartier_cross_sells_heading() {
	return 'Pour compléter votre repas';
}
add_filter( 'woocommerce_product_cross_sells_products_heading', 'chartier_cross_sells_heading' );

/** Fiche produit : apparentés et « upsells » de Storefront retirés (ajoutés par le parent, d'où le hook init). */
function chartier_single_product_suggestions_setup() {
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	remove_action( 'woocommerce_after_single_product_summary', 'storefront_upsell_display', 15 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );

	// Panier : suggestions sous le panier, sur toute la largeur, au lieu de la colonne « Total ».
	remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
	add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display' );
}
add_action( 'init', 'chartier_single_product_suggestions_setup' );

/** Fiche produit : les ventes croisées du plat, sous la description. */
function chartier_product_cross_sells() {
	global $product;
	if ( ! $product ) {
		return;
	}
	$suggestions = array_filter(
		array_map( 'wc_get_product', $product->get_cross_sell_ids() ),
		function ( $item ) {
			return $item && $item->is_visible();
		}
	);
	if ( empty( $suggestions ) ) {
		return;
	}
	wc_get_template(
		'cart/cross-sells.php',
		array(
			'cross_sells' => $suggestions,
			'columns'     => 3,
		)
	);
}
add_action( 'woocommerce_after_single_product_summary', 'chartier_product_cross_sells', 20 );

/** Panier : jusqu'à 3 suggestions, 3 par ligne (2 en mobile, comme la boutique). */
function chartier_cart_cross_sells_total() {
	return 3;
}
add_filter( 'woocommerce_cross_sells_total', 'chartier_cart_cross_sells_total' );

function chartier_cart_cross_sells_columns() {
	return 3;
}
add_filter( 'woocommerce_cross_sells_columns', 'chartier_cart_cross_sells_columns' );


/* =========================================================
   18. Avis clients (IT-14)
   - Réglages natifs (script docs/avis-it14.php) : avis et notes
     obligatoires, réservés aux clients ayant acheté le produit
     (US-14.2), badge « achat vérifié », publication immédiate.
   - Affichage (US-14.1) : style.css section 26.
   ========================================================= */

/** Textes des avis. */
function chartier_review_texts( $translation, $text, $domain ) {
	if ( 'woocommerce' !== $domain ) {
		return $translation;
	}
	$texts = array(
		'verified owner' => 'achat vérifié',
		'Submit'         => 'Publier mon avis',
		'Add a review'   => 'Donner votre avis',
	);
	return isset( $texts[ $text ] ) ? $texts[ $text ] : $translation;
}
add_filter( 'gettext', 'chartier_review_texts', 10, 3 );

/**
 * Auteur d'un avis : prénom + initiale du nom (« Camille D. ») plutôt que
 * l'identifiant du compte, tiré de l'e-mail à l'inscription (IT-7).
 */
function chartier_review_author( $author, $comment_id, $comment ) {
	if ( ! $comment || 'review' !== $comment->comment_type || ! $comment->user_id ) {
		return $author;
	}
	$prenom = get_user_meta( $comment->user_id, 'first_name', true );
	$nom    = get_user_meta( $comment->user_id, 'last_name', true );
	if ( ! $prenom ) {
		$prenom = get_user_meta( $comment->user_id, 'billing_first_name', true );
		$nom    = get_user_meta( $comment->user_id, 'billing_last_name', true );
	}
	if ( ! $prenom ) {
		return $author;
	}
	return $nom ? $prenom . ' ' . mb_strtoupper( mb_substr( $nom, 0, 1 ) ) . '.' : $prenom;
}
add_filter( 'get_comment_author', 'chartier_review_author', 10, 3 );


/**
 * Onglets de la fiche : « Informations complémentaires » devient « Détails »,
 * pour que l'onglet « Avis » tienne sur la largeur d'un mobile.
 */
function chartier_product_tabs_titles( $tabs ) {
	if ( isset( $tabs['additional_information'] ) ) {
		$tabs['additional_information']['title'] = 'Détails';
	}
	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'chartier_product_tabs_titles', 20 );


/* =========================================================
   19. Frais de préparation (IT-15)
   - US-15.1 : montant et seuil réglables dans WooCommerce →
     Réglages → Général, section « Frais de préparation ».
     Un montant à 0 désactive les frais.
   - US-15.2 : frais ajoutés au calcul du panier par le hook
     woocommerce_cart_calculate_fees quand le montant des plats
     (après réduction) est sous le seuil. Ils suivent donc le panier,
     la commande, les e-mails et le back-office comme une ligne native.
   - US-15.3 : la ligne apparaît ou disparaît selon le panier ; tant
     qu'elle s'applique, le ticket indique combien ajouter pour l'éviter.
   ========================================================= */

/** Valeurs réglées dans le back-office (par défaut : 1,50 € sous 15 €). */
function chartier_prep_fee_amount() {
	return (float) wc_format_decimal( get_option( 'chartier_prep_fee_amount', '1.50' ) );
}

function chartier_prep_fee_threshold() {
	return (float) wc_format_decimal( get_option( 'chartier_prep_fee_threshold', '15' ) );
}

/** Section « Frais de préparation » dans WooCommerce → Réglages → Général. */
function chartier_prep_fee_settings( $settings ) {
	$settings[] = array(
		'title' => 'Frais de préparation',
		'type'  => 'title',
		'desc'  => 'Frais fixes ajoutés au panier quand le montant des plats (après réduction) est inférieur au seuil. Mettre le montant à 0 pour les désactiver.',
		'id'    => 'chartier_prep_fee',
	);
	$settings[] = array(
		'title'             => 'Montant des frais (€)',
		'id'                => 'chartier_prep_fee_amount',
		'type'              => 'number',
		'default'           => '1.50',
		'custom_attributes' => array( 'min' => '0', 'step' => '0.01' ),
		'css'               => 'width: 8em;',
	);
	$settings[] = array(
		'title'             => 'Seuil du panier (€)',
		'desc'              => 'Les frais s’appliquent en dessous de ce montant.',
		'id'                => 'chartier_prep_fee_threshold',
		'type'              => 'number',
		'default'           => '15',
		'custom_attributes' => array( 'min' => '0', 'step' => '0.01' ),
		'css'               => 'width: 8em;',
	);
	$settings[] = array(
		'type' => 'sectionend',
		'id'   => 'chartier_prep_fee',
	);
	return $settings;
}
add_filter( 'woocommerce_general_settings', 'chartier_prep_fee_settings' );

/** Montant manquant pour atteindre le seuil (0 si atteint ou si les frais sont désactivés). */
function chartier_prep_fee_missing( $cart ) {
	if ( ! $cart || $cart->is_empty() || chartier_prep_fee_amount() <= 0 ) {
		return 0;
	}
	$total = (float) $cart->get_cart_contents_total() + (float) $cart->get_cart_contents_tax();
	return max( 0, chartier_prep_fee_threshold() - $total );
}

/** US-15.2 : ajout dynamique des frais au calcul du panier. */
function chartier_add_prep_fee( $cart ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	if ( chartier_prep_fee_missing( $cart ) > 0 ) {
		$cart->add_fee( 'Frais de préparation', chartier_prep_fee_amount(), false );
	}
}
add_action( 'woocommerce_cart_calculate_fees', 'chartier_add_prep_fee' );

/** US-15.3 : « Plus que X € de plats pour ne pas payer les frais de préparation ». */
function chartier_prep_fee_hint() {
	$missing = chartier_prep_fee_missing( WC()->cart );
	if ( $missing <= 0 ) {
		return;
	}
	printf(
		'<tr class="prep-fee-hint"><td colspan="2">Plus que %s de plats pour ne pas payer les frais de préparation.</td></tr>',
		wp_kses_post( wc_price( $missing ) )
	);
}
add_action( 'woocommerce_cart_totals_before_order_total', 'chartier_prep_fee_hint' );
add_action( 'woocommerce_review_order_before_order_total', 'chartier_prep_fee_hint' );



/* =========================================================
   20. Accueil (IT-16)
   - La page d'accueil est une page statique (Réglages → Lecture),
     affichée par front-page.php (hiérarchie des templates).
   - US-16.1 / 16.2 : « Nouveautés » = les 3 derniers produits
     publiés, lus à chaque affichage (WP_Query) et affichés avec la
     carte produit de la boutique (get_template_part via
     wc_get_template_part) : un nouveau plat apparaît tout seul.
   ========================================================= */

/** Les 3 derniers produits publiés et visibles dans le catalogue. */
function chartier_latest_products_query( $nombre = 3 ) {
	return new WP_Query(
		array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => $nombre,
			'orderby'             => array( 'date' => 'DESC', 'ID' => 'DESC' ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'tax_query'           => array(
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => array( 'exclude-from-catalog' ),
					'operator' => 'NOT IN',
				),
			),
		)
	);
}

/** Tuiles Entrées / Plats / Desserts (même composant que la boutique). */
function chartier_home_category_tiles() {
	$categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'exclude'    => get_option( 'default_product_cat' ),
			'orderby'    => 'menu_order',
		)
	);
	if ( empty( $categories ) || is_wp_error( $categories ) ) {
		return;
	}
	echo '<ul class="category-tiles__list">';
	foreach ( $categories as $category ) {
		printf(
			'<li><a class="category-tile" href="%s"><span class="category-tile__count">%s</span><span class="category-tile__title">%s</span></a></li>',
			esc_url( get_term_link( $category ) ),
			esc_html( sprintf( _n( '%d plat', '%d plats', $category->count, 'chartier-child' ), $category->count ) ),
			esc_html( $category->name )
		);
	}
	echo '</ul>';
}


/* =========================================================
   21. Filtres de la carte (IT-18)
   Colonne « Filtres de la carte » à gauche sur la carte, ses
   catégories, ses collections et la recherche de plats.
   Widgets natifs de WooCommerce (prix, attribut global
   « Régime », filtres actifs), placés par docs/filtres-it18.php.
   En mobile : les filtres passent au-dessus des plats, repliés
   derrière un bouton « Filtrer les plats » (ouverts sans JS).
   ========================================================= */

/** Pages de la carte : boutique, catégories, collections, recherche de plats. */
function chartier_is_catalog_context() {
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return false;
	}
	if ( is_shop() || is_product_taxonomy() ) {
		return true;
	}
	return is_search() && 'product' === get_query_var( 'post_type' );
}

/** Zone de widgets dédiée (Apparence › Widgets › « Filtres de la carte »). */
function chartier_register_filters_sidebar() {
	register_sidebar(
		array(
			'name'          => 'Filtres de la carte',
			'id'            => 'chartier-filtres',
			'description'   => 'Colonne de filtres de la carte (IT-18) : prix, régime, filtres actifs.',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'chartier_register_filters_sidebar' );

/** Sur la carte, la barre latérale de Storefront est remplacée par nos filtres. */
function chartier_catalog_sidebar_setup() {
	if ( chartier_is_catalog_context() ) {
		remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
		add_action( 'storefront_sidebar', 'chartier_filters_sidebar', 10 );
	}
}
add_action( 'wp', 'chartier_catalog_sidebar_setup' );

/** Colonne à gauche (classe « left-sidebar » de Storefront) ; pleine largeur s'il n'y a aucun widget. */
function chartier_catalog_body_class( $classes ) {
	if ( ! chartier_is_catalog_context() ) {
		return $classes;
	}
	$classes = array_diff( $classes, array( 'right-sidebar', 'left-sidebar', 'storefront-full-width-content' ) );
	if ( is_active_sidebar( 'chartier-filtres' ) ) {
		$classes[] = 'left-sidebar';
		$classes[] = 'has-filters';
	} else {
		$classes[] = 'storefront-full-width-content';
	}
	return $classes;
}
add_filter( 'body_class', 'chartier_catalog_body_class', 20 );

/** Nombre de filtres appliqués (régime choisi, prix min ou max), affiché sur le bouton mobile. */
function chartier_active_filters_count() {
	$count = 0;
	foreach ( WC_Query::get_layered_nav_chosen_attributes() as $attribute ) {
		$count += count( $attribute['terms'] );
	}
	foreach ( array( 'min_price', 'max_price' ) as $key ) {
		if ( isset( $_GET[ $key ] ) && '' !== $_GET[ $key ] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$count++;
		}
	}
	return $count;
}

function chartier_filters_sidebar() {
	if ( ! is_active_sidebar( 'chartier-filtres' ) ) {
		return;
	}
	$count = chartier_active_filters_count();
	?>
	<aside id="secondary" class="widget-area chartier-filters" aria-label="Filtres de la carte">
		<button type="button" class="button chartier-filters__toggle" aria-expanded="false" aria-controls="chartier-filters-panel">
			Filtrer les plats<?php echo $count ? ' (' . (int) $count . ')' : ''; ?>
		</button>
		<div id="chartier-filters-panel" class="chartier-filters__panel">
			<?php dynamic_sidebar( 'chartier-filtres' ); ?>
		</div>
	</aside>
	<script>
	( function () {
		var aside = document.querySelector( '.chartier-filters' );
		var button = aside && aside.querySelector( '.chartier-filters__toggle' );
		if ( ! button ) {
			return;
		}
		aside.classList.add( 'is-ready' ); // Sans JS, les filtres restent ouverts.
		button.addEventListener( 'click', function () {
			var open = button.getAttribute( 'aria-expanded' ) === 'true';
			button.setAttribute( 'aria-expanded', String( ! open ) );
			aside.classList.toggle( 'is-open', ! open );
		} );
	} )();
	</script>
	<?php
}


/* =========================================================
   22. Livraison selon le poids (IT-19)
   - US-19.2 : 3 tranches réglables dans WooCommerce › Réglages ›
     Expédition › Options d'expédition (même principe que les
     frais de préparation de l'IT-15). Tarifs saisis TTC.
   - US-19.1 : le prix du forfait « Livraison à Paris » (flat_rate)
     est remplacé par celui de la tranche du poids du panier.
     Les retraits restent gratuits.
   - US-19.3 : le prix TTC et le poids s'affichent sous le mode
     de livraison, dans le panier et la commande (section 10).
   ========================================================= */

/** Valeurs par défaut des tranches (kg, € TTC). */
function chartier_weight_tiers_defaults() {
	return array(
		'chartier_weight_tier1_max'   => '1',
		'chartier_weight_tier1_price' => '4.90',
		'chartier_weight_tier2_max'   => '3',
		'chartier_weight_tier2_price' => '6.90',
		'chartier_weight_tier3_price' => '9.90',
	);
}

/** Les 3 tranches réglées dans le back-office : poids maximum (kg) et tarif TTC. */
function chartier_weight_tiers() {
	$d   = chartier_weight_tiers_defaults();
	$get = function ( $key ) use ( $d ) {
		return (float) wc_format_decimal( get_option( $key, $d[ $key ] ) );
	};
	return array(
		array( 'max' => $get( 'chartier_weight_tier1_max' ), 'price' => $get( 'chartier_weight_tier1_price' ) ),
		array( 'max' => $get( 'chartier_weight_tier2_max' ), 'price' => $get( 'chartier_weight_tier2_price' ) ),
		array( 'max' => INF, 'price' => $get( 'chartier_weight_tier3_price' ) ),
	);
}

/** Tranche correspondant à un poids en kg. */
function chartier_weight_tier( $kg ) {
	$tiers = chartier_weight_tiers();
	foreach ( $tiers as $tier ) {
		if ( $kg <= $tier['max'] ) {
			return $tier;
		}
	}
	return end( $tiers );
}

/** Poids d'un colis en kg (les poids des plats sont saisis dans l'unité de la boutique). */
function chartier_package_weight( $package ) {
	$weight = 0;
	foreach ( $package['contents'] as $item ) {
		$weight += (float) $item['data']->get_weight() * $item['quantity'];
	}
	return wc_get_weight( $weight, 'kg' );
}

/** Poids lisible : « 1,2 kg » (espace insécable : « kg » ne passe jamais seul à la ligne). */
function chartier_format_kg( $kg ) {
	return wc_format_localized_decimal( rtrim( rtrim( number_format( $kg, 2, '.', '' ), '0' ), '.' ) ) . "\u{00A0}kg";
}

/** US-19.1 : prix de la livraison selon la tranche de poids. */
function chartier_weight_based_rates( $rates, $package ) {
	$kg   = chartier_package_weight( $package );
	$tier = chartier_weight_tier( $kg );
	foreach ( $rates as $rate ) {
		if ( 'flat_rate' !== $rate->get_method_id() ) {
			continue; // Retraits : toujours gratuits.
		}
		$ttc   = $tier['price'];
		$taxes = array();
		if ( wc_tax_enabled() && array_sum( (array) $rate->get_taxes() ) > 0 ) {
			// Livraison soumise à la TVA (réglage du mode). Tarif saisi TTC : on en retire la TVA pour que le HT et la TVA de la facture (IT-17) soient justes.
			$taxes = WC_Tax::calc_inclusive_tax( $ttc, WC_Tax::get_shipping_tax_rates() );
		}
		$rate->set_cost( $ttc - array_sum( $taxes ) );
		$rate->set_taxes( $taxes );
		$rate->add_meta_data( 'Poids de la commande', chartier_format_kg( $kg ) ); // Repris dans la commande (back-office, e-mail).
	}
	return $rates;
}
add_filter( 'woocommerce_package_rates', 'chartier_weight_based_rates', 20, 2 );

/** US-19.2 : section « Tarifs de livraison selon le poids » dans les options d'expédition. */
function chartier_weight_tiers_settings( $settings ) {
	$d   = chartier_weight_tiers_defaults();
	$new = array();
	foreach ( $settings as $setting ) {
		$new[] = $setting;
		if ( isset( $setting['type'], $setting['id'] ) && 'sectionend' === $setting['type'] && 'shipping_options' === $setting['id'] ) {
			$new[] = array(
				'title' => 'Tarifs de livraison selon le poids',
				'type'  => 'title',
				'desc'  => 'S\'applique à « Livraison à Paris » : le tarif dépend du poids total de la commande. Tarifs TTC, tels que le client les voit. Les retraits restent gratuits.',
				'id'    => 'chartier_weight_tiers',
			);
			$fields = array(
				'chartier_weight_tier1_max'   => array( 'Tranche 1 : jusqu\'à (kg)', 'wc_input_decimal' ),
				'chartier_weight_tier1_price' => array( 'Tranche 1 : tarif (€ TTC)', 'wc_input_price' ),
				'chartier_weight_tier2_max'   => array( 'Tranche 2 : jusqu\'à (kg)', 'wc_input_decimal' ),
				'chartier_weight_tier2_price' => array( 'Tranche 2 : tarif (€ TTC)', 'wc_input_price' ),
				'chartier_weight_tier3_price' => array( 'Tranche 3 : au-delà, tarif (€ TTC)', 'wc_input_price' ),
			);
			foreach ( $fields as $id => $field ) {
				$new[] = array(
					'title'   => $field[0],
					'id'      => $id,
					'type'    => 'text',
					'class'   => $field[1],
					'default' => $d[ $id ],
					'css'     => 'width: 80px;',
				);
			}
			$new[] = array( 'type' => 'sectionend', 'id' => 'chartier_weight_tiers' );
		}
	}
	return $new;
}
add_filter( 'woocommerce_get_settings_shipping', 'chartier_weight_tiers_settings', 20 );

/** Après un changement de tarifs, WooCommerce doit recalculer les prix de livraison mis en cache. */
function chartier_weight_tiers_flush_cache() {
	WC_Cache_Helper::get_transient_version( 'shipping', true );
}
add_action( 'woocommerce_update_options_shipping_options', 'chartier_weight_tiers_flush_cache' );


/* =========================================================
   23. Créneaux de livraison et de retrait (IT-20)
   - Bloc « Quand ? » dans la commande : un jour (aujourd'hui + 6 jours)
     et un créneau, dont la liste dépend du mode choisi (classe is-pickup
     posée par assets/js/commande.js, IT-6).
   - Aujourd'hui : pas de créneau qui commence dans moins d'1 h
     (temps de préparation), vérifié aussi côté serveur.
   - Le créneau est enregistré dans la commande et repris partout :
     confirmation, Mon compte, e-mails, back-office, facture PDF.
   ========================================================= */

/** Créneaux proposés selon le mode (« retrait » ou « livraison »), heures de début–fin. */
function chartier_slots( $mode ) {
	$slots = array(
		'retrait'   => array( '11:30-12:30', '12:30-13:30', '13:30-14:30', '18:30-19:30', '19:30-20:30', '20:30-21:30', '21:30-22:30' ),
		'livraison' => array( '12:00-13:00', '13:00-14:00', '19:00-20:00', '20:00-21:00', '21:00-22:00' ),
	);
	return isset( $slots[ $mode ] ) ? $slots[ $mode ] : array();
}

/** « 12:30-13:30 » → « 12h30–13h30 » ; « 12:00-13:00 » → « 12h–13h ». */
function chartier_slot_label( $slot ) {
	$heure = function ( $t ) {
		list( $h, $m ) = explode( ':', $t );
		return (int) $h . 'h' . ( '00' === $m ? '' : $m );
	};
	list( $debut, $fin ) = explode( '-', $slot );
	return $heure( $debut ) . '–' . $heure( $fin );
}

/** Heure minimale d'un créneau aujourd'hui : maintenant + 1 h de préparation. */
function chartier_slot_min_time() {
	return current_datetime()->modify( '+1 hour' );
}

/** Le créneau est-il encore réservable ? */
function chartier_slot_is_open( $date, $slot ) {
	$debut = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $date . ' ' . strtok( $slot, '-' ), wp_timezone() );
	return $debut && $debut >= chartier_slot_min_time();
}

/** Les 7 jours proposés : clé Y-m-d → « Aujourd'hui, jeudi 22 octobre ». */
function chartier_slot_days() {
	$jours = array();
	$today = current_datetime()->setTime( 0, 0 );
	for ( $i = 0; $i < 7; $i++ ) {
		$jour  = $today->modify( "+$i day" );
		$label = wp_date( 'l j F', $jour->getTimestamp() );
		if ( 0 === $i ) {
			// Aujourd'hui seulement s'il reste au moins un créneau, quel que soit le mode.
			$reste = false;
			foreach ( array_merge( chartier_slots( 'retrait' ), chartier_slots( 'livraison' ) ) as $slot ) {
				$reste = $reste || chartier_slot_is_open( $jour->format( 'Y-m-d' ), $slot );
			}
			if ( ! $reste ) {
				continue;
			}
			$label = 'Aujourd\'hui, ' . $label;
		} elseif ( 1 === $i ) {
			$label = 'Demain, ' . $label;
		}
		$jours[ $jour->format( 'Y-m-d' ) ] = $label;
	}
	return $jours;
}

/** Une liste du DS, au balisage des champs WooCommerce. */
function chartier_slot_select( $name, $label, $options, $selected, $classes, $mode = '' ) {
	printf(
		'<p class="form-row validate-required %1$s" id="%2$s_field"%3$s><label for="%2$s">%4$s&nbsp;<abbr class="required" title="obligatoire">*</abbr></label><span class="woocommerce-input-wrapper"><select name="%2$s" id="%2$s" class="select"%5$s%6$s>',
		esc_attr( $classes ),
		esc_attr( $name ),
		$mode ? ' data-mode="' . esc_attr( $mode ) . '"' . ( ( 'retrait' === $mode ) !== chartier_is_pickup_chosen() ? ' hidden' : '' ) : '',
		esc_html( $label ),
		$mode ? ' data-slots' : '',
		$mode && ( ( 'retrait' === $mode ) !== chartier_is_pickup_chosen() ) ? ' disabled' : ''
	);
	echo '<option value="">' . ( $mode ? 'Choisir un créneau' : 'Choisir un jour' ) . '</option>';
	foreach ( $options as $value => $text ) {
		printf(
			'<option value="%1$s"%2$s%3$s>%4$s</option>',
			esc_attr( $value ),
			$mode ? ' data-start="' . esc_attr( strtok( $value, '-' ) ) . '"' : '',
			selected( $selected, $value, false ),
			esc_html( $text )
		);
	}
	echo '</select></span></p>';
}

/** Bloc « Quand ? » sous les coordonnées (et sous les instructions de retrait). */
function chartier_slot_fields( $checkout ) {
	$min = chartier_slot_min_time();
	echo '<fieldset class="delivery-slot" data-today="' . esc_attr( current_datetime()->format( 'Y-m-d' ) ) . '" data-min="' . esc_attr( $min->format( 'H:i' ) ) . '">';
	echo '<legend class="delivery-slot__title"><span class="delivery-slot__pickup">Quand passez-vous ?</span><span class="delivery-slot__delivery">Quand vous livrer ?</span></legend>';
	chartier_slot_select( 'chartier_slot_date', 'Jour', chartier_slot_days(), $checkout->get_value( 'chartier_slot_date' ), 'form-row-first' );
	foreach ( array( 'retrait' => 'Créneau de retrait', 'livraison' => 'Créneau de livraison' ) as $mode => $label ) {
		$options = array();
		foreach ( chartier_slots( $mode ) as $slot ) {
			$options[ $slot ] = chartier_slot_label( $slot );
		}
		chartier_slot_select( 'chartier_slot_' . $mode, $label, $options, $checkout->get_value( 'chartier_slot_' . $mode ), 'form-row-last', $mode );
	}
	echo '<p class="delivery-slot__note">Aujourd\'hui, les créneaux commencent au plus tôt 1&nbsp;h après votre commande, le temps de la préparer.</p>';
	echo '</fieldset>';
}
add_action( 'woocommerce_after_checkout_billing_form', 'chartier_slot_fields', 20 );

/** Mode d'après les données envoyées : « retrait » ou « livraison ». */
function chartier_slot_mode_from_data( $data ) {
	$methods = isset( $data['shipping_method'] ) ? (array) $data['shipping_method'] : array();
	return chartier_is_pickup_method( reset( $methods ) ) ? 'retrait' : 'livraison';
}

/** Jour et créneau obligatoires, existants et encore ouverts. */
function chartier_slot_validate( $data, $errors ) {
	$mode = chartier_slot_mode_from_data( $data );
	$date = isset( $_POST['chartier_slot_date'] ) ? sanitize_text_field( wp_unslash( $_POST['chartier_slot_date'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce vérifié par WooCommerce.
	$slot = isset( $_POST[ 'chartier_slot_' . $mode ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'chartier_slot_' . $mode ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( ! array_key_exists( $date, chartier_slot_days() ) ) {
		$errors->add( 'chartier_slot_date', '<strong>Jour</strong> : choisissez le jour de votre ' . $mode . '.' );
		return;
	}
	if ( ! in_array( $slot, chartier_slots( $mode ), true ) ) {
		$errors->add( 'chartier_slot', '<strong>Créneau</strong> : choisissez un créneau de ' . $mode . '.' );
		return;
	}
	if ( ! chartier_slot_is_open( $date, $slot ) ) {
		$errors->add( 'chartier_slot', '<strong>Créneau</strong> : ce créneau commence trop tôt pour préparer votre commande, choisissez-en un plus tard.' );
	}
}
add_action( 'woocommerce_after_checkout_validation', 'chartier_slot_validate', 10, 2 );

/** Enregistrement dans la commande (champs cachés, préfixés par « _ »). */
function chartier_slot_save( $order, $data ) {
	$mode = chartier_slot_mode_from_data( $data );
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- déjà validé ci-dessus.
	$order->update_meta_data( '_chartier_slot_mode', $mode );
	$order->update_meta_data( '_chartier_slot_date', sanitize_text_field( wp_unslash( $_POST['chartier_slot_date'] ?? '' ) ) );
	$order->update_meta_data( '_chartier_slot', sanitize_text_field( wp_unslash( $_POST[ 'chartier_slot_' . $mode ] ?? '' ) ) );
	// phpcs:enable
}
add_action( 'woocommerce_checkout_create_order', 'chartier_slot_save', 10, 2 );

/** « Retrait prévu le vendredi 23 octobre, 12h30–13h30 » (vide si la commande n'a pas de créneau). */
function chartier_order_slot_text( $order ) {
	if ( ! $order instanceof WC_Order || ! $order->get_meta( '_chartier_slot' ) ) {
		return '';
	}
	$jour = DateTimeImmutable::createFromFormat( 'Y-m-d', $order->get_meta( '_chartier_slot_date' ), wp_timezone() );
	return sprintf(
		'%s le %s, %s',
		'retrait' === $order->get_meta( '_chartier_slot_mode' ) ? 'Retrait prévu' : 'Livraison prévue',
		$jour ? wp_date( 'l j F', $jour->getTimestamp() ) : '',
		chartier_slot_label( $order->get_meta( '_chartier_slot' ) )
	);
}

/** Confirmation et Mon compte › Commande (même gabarit « détails de la commande »). */
function chartier_order_slot_summary( $order ) {
	$texte = chartier_order_slot_text( $order );
	if ( $texte ) {
		echo '<p class="delivery-slot-summary">' . esc_html( $texte ) . '</p>';
	}
}
add_action( 'woocommerce_order_details_before_order_table', 'chartier_order_slot_summary' );

/** E-mails : au-dessus du tableau de la commande (client et restaurant). */
function chartier_email_slot( $order, $sent_to_admin, $plain_text ) {
	$texte = chartier_order_slot_text( $order );
	if ( ! $texte ) {
		return;
	}
	if ( $plain_text ) {
		echo "\n" . esc_html( strtoupper( $texte ) ) . "\n\n";
		return;
	}
	echo '<p style="margin:0 0 16px;padding:12px 16px;border:2px dashed #9B9493;font-weight:600;">' . esc_html( $texte ) . '</p>';
}
add_action( 'woocommerce_email_before_order_table', 'chartier_email_slot', 4, 3 );

/** Back-office : sous l'adresse de livraison de la commande. */
function chartier_admin_order_slot( $order ) {
	$texte = chartier_order_slot_text( $order );
	if ( $texte ) {
		echo '<p><strong>Créneau :</strong><br>' . esc_html( $texte ) . '</p>';
	}
}
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'chartier_admin_order_slot' );

/** Facture PDF (IT-17) : une ligne de plus dans les données de la commande. */
function chartier_invoice_slot( $type, $order ) {
	$texte = chartier_order_slot_text( $order );
	if ( 'invoice' === $type && $texte ) {
		list( $titre, $valeur ) = explode( ' le ', $texte, 2 );
		echo '<tr class="delivery-slot"><th>' . esc_html( $titre ) . ' :</th><td>' . esc_html( $valeur ) . '</td></tr>';
	}
}
add_action( 'wpo_wcpdf_after_order_data', 'chartier_invoice_slot', 10, 2 );

/** Script du bloc « Quand ? » (après celui de l'IT-6 qui pose la classe is-pickup). */
function chartier_enqueue_slot_script() {
	if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() ) {
		wp_enqueue_script( 'chartier-creneaux', get_stylesheet_directory_uri() . '/assets/js/creneaux.js', array( 'jquery', 'chartier-commande' ), wp_get_theme()->get( 'Version' ), true );
	}
}
add_action( 'wp_enqueue_scripts', 'chartier_enqueue_slot_script' );


/* =========================================================
   24. Back-office : liste des produits lisible (IT-21)
   Avec Yoast SEO (4 colonnes de plus), Marques et Étiquettes, la
   colonne « Collections » était écrasée à une lettre de large : chaque
   ligne devenait immense (d'où l'impression qu'un seul produit
   s'affichait). On retire les colonnes vides ou inutiles ici et on
   donne une largeur aux collections. Le score SEO de Yoast reste.
   ========================================================= */
function chartier_admin_product_columns( $columns ) {
	foreach ( array( 'wpseo-links', 'wpseo-linked', 'wpseo-score-readability', 'taxonomy-product_brand', 'product_tag' ) as $cle ) {
		unset( $columns[ $cle ] );
	}
	return $columns;
}
add_filter( 'manage_edit-product_columns', 'chartier_admin_product_columns', 99 );

function chartier_admin_product_columns_css() {
	$ecran = get_current_screen();
	if ( $ecran && 'edit-product' === $ecran->id ) {
		echo '<style>.wp-list-table .column-taxonomy-collection { width: 12%; }</style>';
	}
}
add_action( 'admin_head', 'chartier_admin_product_columns_css' );


/* =========================================================
   25. Plat star (itération W)
   - US-W.1 : le plat le plus vendu est identifié automatiquement grâce
     au compteur de ventes de WooCommerce (méta « total_sales », mis à
     jour à chaque commande payée). Les plats épuisés sont écartés.
     Résultat gardé 1 h en cache, recalculé dès qu'une vente est
     enregistrée ou qu'un plat est modifié.
   - US-W.3 : badge « ★ Plat star » (.badge--star du Design System)
     sur sa carte (carte, recherche, nouveautés…) et sur sa fiche.
   - US-W.2 : bloc « Plat star » de l'accueil (front-page.php).
   ========================================================= */

/** Identifiant du plat le plus vendu (0 s'il n'y a encore aucune vente). */
function chartier_star_product_id() {
	$id = get_transient( 'chartier_star_product' );
	if ( false !== $id ) {
		return (int) $id;
	}
	$visibilite = wc_get_product_visibility_term_ids();
	$requete    = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery -- 11 plats, résultat mis en cache.
			'orderby'        => array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ),
			'meta_query'     => array( array( 'key' => 'total_sales', 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			'tax_query'      => array( array( 'taxonomy' => 'product_visibility', 'field' => 'term_taxonomy_id', 'terms' => array( $visibilite['outofstock'] ), 'operator' => 'NOT IN' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	$id = $requete->posts ? (int) $requete->posts[0] : 0;
	set_transient( 'chartier_star_product', $id, HOUR_IN_SECONDS );
	return $id;
}

/** Le cache est vidé quand une vente est comptée ou qu'un plat change (stock, prix…). */
function chartier_star_flush() {
	delete_transient( 'chartier_star_product' );
}
add_action( 'woocommerce_recorded_sales', 'chartier_star_flush' );
add_action( 'woocommerce_update_product', 'chartier_star_flush' );
add_action( 'woocommerce_product_set_stock_status', 'chartier_star_flush' );

function chartier_is_star( $product ) {
	return $product instanceof WC_Product && $product->get_id() === chartier_star_product_id();
}

function chartier_star_badge_html() {
	return '<span class="badge badge--star"><span aria-hidden="true">★ </span>Plat star</span>';
}

/** US-W.3 : badge sur la carte du plat (toutes les listes de produits). */
function chartier_star_badge_loop() {
	global $product;
	if ( chartier_is_star( $product ) ) {
		echo str_replace( 'badge badge--star', 'badge badge--star product-badge--star', chartier_star_badge_html() ); // phpcs:ignore WordPress.Security.EscapeOutput -- HTML fixe.
	}
}
add_action( 'woocommerce_before_shop_loop_item_title', 'chartier_star_badge_loop', 8 );

/** US-W.3 : badge sur la fiche, au-dessus du titre (comme le badge Promo). */
function chartier_star_badge_single() {
	global $product;
	if ( chartier_is_star( $product ) ) {
		echo chartier_star_badge_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- HTML fixe.
	}
}
add_action( 'woocommerce_single_product_summary', 'chartier_star_badge_single', 3 );

/**
 * US-W.2 : bloc « Plat star » de l'accueil, comme la maquette de conception (v1.0) :
 * bandeau noir, photo à gauche ; badge, nom, description, prix et bouton à droite.
 * En mobile : la photo passe en fond de la carte, assombrie, texte par-dessus.
 * Rien ne s'affiche tant qu'aucun plat n'a été vendu.
 */
function chartier_home_star() {
	$product = wc_get_product( chartier_star_product_id() );
	if ( ! $product ) {
		return;
	}
	$texte = $product->get_short_description() ? $product->get_short_description() : $product->get_description();
	?>
	<section class="home-section home-star" aria-labelledby="home-star">
		<div class="home-star__media">
			<?php echo $product->get_image( 'woocommerce_single', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
		<div class="home-star__body">
			<?php echo chartier_star_badge_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<h2 class="home-star__title" id="home-star"><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h2>
			<p class="home-star__text"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $texte ), 24 ) ); ?></p>
			<div class="home-star__buy">
				<span class="home-star__price"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<a class="button home-star__button" href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"><?php echo esc_html( $product->add_to_cart_text() ); ?></a>
			</div>
		</div>
	</section>
	<?php
}


/* =========================================================
   26. Sécurité des formulaires (itération X)
   Anti-spam invisible, sans captcha ni service extérieur, sur
   l'inscription (Mon compte et commande) et les avis :
   1. Pot de miel : un champ « Site web » caché aux humains (hors écran,
      ignoré par les lecteurs d'écran et la touche Tab). Un robot qui
      remplit tous les champs le remplit aussi → refus.
   2. Délai minimum : un humain met plus de 3 s à remplir le formulaire.
      L'heure d'affichage est signée (wp_hash) : un robot ne peut pas
      la falsifier. Trop rapide ou signature fausse → refus.
   Le visiteur ne voit rien et n'a aucune étape en plus (US-X.2, X.3).
   Les tentatives bloquées sont comptées et affichées dans le tableau
   de bord (US-X.1). Les nonces de WordPress / WooCommerce restent actifs.
   ========================================================= */

const CHARTIER_SPAM_MIN_SECONDS = 3;

/** Champs invisibles à ajouter dans un formulaire. */
function chartier_antispam_fields() {
	$heure = time();
	?>
	<div class="chartier-hp" aria-hidden="true">
		<label for="chartier_site_web">Site web (laisser vide)</label>
		<input type="text" name="chartier_site_web" id="chartier_site_web" value="" tabindex="-1" autocomplete="off">
	</div>
	<input type="hidden" name="chartier_ts" value="<?php echo esc_attr( $heure . '|' . wp_hash( 'chartier_ts' . $heure ) ); ?>">
	<?php
}

/** Raison du refus, ou '' si l'envoi semble humain. */
function chartier_antispam_reason() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- les formulaires ont déjà leur nonce.
	if ( ! empty( $_POST['chartier_site_web'] ) ) {
		return 'pot de miel rempli';
	}
	$ts = isset( $_POST['chartier_ts'] ) ? sanitize_text_field( wp_unslash( $_POST['chartier_ts'] ) ) : '';
	// phpcs:enable
	$parts = explode( '|', $ts );
	if ( 2 !== count( $parts ) || ! hash_equals( wp_hash( 'chartier_ts' . $parts[0] ), $parts[1] ) ) {
		return 'heure absente ou falsifiée';
	}
	if ( time() - (int) $parts[0] < CHARTIER_SPAM_MIN_SECONDS ) {
		return 'envoyé trop vite';
	}
	return '';
}

/** Compteur des tentatives bloquées (US-X.1). */
function chartier_antispam_count( $formulaire, $raison ) {
	$stats                 = (array) get_option( 'chartier_spam_blocked', array() );
	$stats[ $formulaire ]  = (int) ( $stats[ $formulaire ] ?? 0 ) + 1;
	$stats['derniere']     = current_time( 'mysql' ) . ' (' . $formulaire . ' : ' . $raison . ')';
	update_option( 'chartier_spam_blocked', $stats, false );
}

/* --- Inscription : Mon compte et case « Créer un compte » de la commande --- */
add_action( 'woocommerce_register_form', 'chartier_antispam_fields' );
add_action( 'woocommerce_after_checkout_registration_form', 'chartier_antispam_fields' );

/** WooCommerce n'applique ce filtre qu'au moment de créer un compte (Mon compte ou commande). */
function chartier_antispam_registration( $errors ) {
	$raison = chartier_antispam_reason();
	if ( $raison ) {
		chartier_antispam_count( 'inscription', $raison );
		$errors->add( 'chartier_spam', 'Votre inscription n’a pas pu être validée. Patientez quelques secondes et réessayez.' );
	}
	return $errors;
}
add_filter( 'woocommerce_registration_errors', 'chartier_antispam_registration' );

/* --- Avis sur les plats (formulaire de commentaires des produits) --- */
add_action( 'comment_form', 'chartier_antispam_review_fields' );
function chartier_antispam_review_fields( $post_id ) {
	if ( 'product' === get_post_type( $post_id ) ) {
		chartier_antispam_fields();
	}
}

/** Un avis suspect n'est pas publié : il part dans « Indésirables », que l'admin peut consulter. */
function chartier_antispam_review( $approved, $commentdata ) {
	if ( 'product' !== get_post_type( $commentdata['comment_post_ID'] ?? 0 ) || is_admin() ) {
		return $approved;
	}
	$raison = chartier_antispam_reason();
	if ( $raison ) {
		chartier_antispam_count( 'avis', $raison );
		return 'spam';
	}
	return $approved;
}
add_filter( 'pre_comment_approved', 'chartier_antispam_review', 20, 2 );

/* --- Tableau de bord : bilan pour l'administrateur (US-X.1) --- */
function chartier_antispam_dashboard() {
	wp_add_dashboard_widget(
		'chartier_antispam',
		'Formulaires protégés (anti-spam)',
		function () {
			$stats = (array) get_option( 'chartier_spam_blocked', array() );
			echo '<p>Inscription et avis protégés par un champ piège invisible et un délai minimum de ' . (int) CHARTIER_SPAM_MIN_SECONDS . ' s. Aucun captcha pour les visiteurs.</p>';
			echo '<ul>';
			echo '<li>Inscriptions bloquées : <strong>' . (int) ( $stats['inscription'] ?? 0 ) . '</strong></li>';
			echo '<li>Avis classés indésirables : <strong>' . (int) ( $stats['avis'] ?? 0 ) . '</strong></li>';
			echo '</ul>';
			if ( ! empty( $stats['derniere'] ) ) {
				echo '<p>Dernière tentative : ' . esc_html( $stats['derniere'] ) . '</p>';
			}
		}
	);
}
add_action( 'wp_dashboard_setup', 'chartier_antispam_dashboard' );

