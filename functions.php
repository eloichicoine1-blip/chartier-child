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
 * La colonne de filtres de la maquette arrivera avec l'IT-18.
 */
function chartier_is_shop_context() {
	if ( is_post_type_archive( 'membre' ) || is_singular( 'membre' ) ) {
		return true; // Pages de l'équipe (IT-11) : pleine largeur, sans barre latérale.
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
	$note = $cost > 0 ? wc_price( $cost ) : 'Gratuit';

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
