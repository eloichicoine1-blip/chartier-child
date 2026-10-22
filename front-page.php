<?php
/**
 * Page d'accueil (IT-16), affichée quand l'accueil est une page statique
 * (Réglages → Lecture). Hiérarchie des templates : front-page.php.
 *
 * Sections de la maquette : accroche, la carte, nouveautés (les 3 derniers
 * produits, US-16.1), où retirer sa commande. Le bloc « Plat star » (le plus
 * vendu, itération W) est placé entre les nouveautés et « Où retirer », comme la maquette (functions.php §25).
 *
 * @package chartier-child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$carte = wc_get_page_permalink( 'shop' );
?>

<div id="primary" class="content-area home-page">
	<main id="main" class="site-main" role="main">

		<section class="home-hero" style="--hero-image: url('<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/accueil-salle.jpg' ); ?>');">
			<div class="home-hero__inner">
				<p class="home-hero__eyebrow">Click &amp; collect · 3 bouillons à Paris</p>
				<h1 class="home-hero__title">Le bouillon,<br>sans la queue.</h1>
				<p class="home-hero__text">Les classiques de la maison, préparés pour votre retrait. Commandez en ligne, passez au comptoir, c’est prêt.</p>
				<div class="home-hero__actions">
					<a class="button" href="<?php echo esc_url( $carte ); ?>">Commander en retrait</a>
					<a class="button home-hero__secondary" href="<?php echo esc_url( $carte ); ?>">Me faire livrer</a>
				</div>
			</div>
		</section>

		<section class="home-section" aria-labelledby="home-carte">
			<div class="home-section__head">
				<h2 class="home-section__title" id="home-carte">La carte</h2>
				<a class="home-section__more" href="<?php echo esc_url( $carte ); ?>">Tout voir</a>
			</div>
			<nav class="category-tiles home-categories" aria-label="Catégories de la carte">
				<?php chartier_home_category_tiles(); ?>
			</nav>
		</section>

		<?php
		// US-16.1 / 16.2 : les 3 derniers produits ajoutés, lus à chaque affichage.
		$nouveautes = chartier_latest_products_query( 3 );
		if ( $nouveautes->have_posts() ) :
			?>
			<section class="home-section" aria-labelledby="home-nouveautes">
				<div class="home-section__head">
					<h2 class="home-section__title" id="home-nouveautes">Nouveautés</h2>
				</div>
				<div class="woocommerce">
					<?php
					wc_set_loop_prop( 'name', 'nouveautes' );
					wc_set_loop_prop( 'columns', 3 );
					woocommerce_product_loop_start();
					while ( $nouveautes->have_posts() ) :
						$nouveautes->the_post();
						wc_get_template_part( 'content', 'product' );
					endwhile;
					woocommerce_product_loop_end();
					wp_reset_postdata();
					wc_reset_loop();
					?>
				</div>
			</section>
		<?php endif; ?>

		<?php chartier_home_star(); // Itération W : le plat le plus vendu. ?>

		<section class="home-section home-restaurants" aria-labelledby="home-retrait">
			<div class="home-section__head">
				<h2 class="home-section__title" id="home-retrait">Où retirer votre commande</h2>
			</div>
			<ul class="home-restaurants__list">
				<?php
				$restaurants = array(
					array( 'Paris 9e', 'Grands Boulevards', '7 rue du Faubourg-Montmartre' ),
					array( 'Paris 6e', 'Montparnasse', '59 boulevard du Montparnasse' ),
					array( 'Paris 10e', 'Gare de l’Est', '5 rue du 8 Mai 1945' ),
				);
				foreach ( $restaurants as $r ) :
					?>
					<li class="restaurant-card">
						<span class="restaurant-card__area"><?php echo esc_html( $r[0] ); ?></span>
						<span class="restaurant-card__name"><?php echo esc_html( $r[1] ); ?></span>
						<span class="restaurant-card__address"><?php echo esc_html( $r[2] ); ?></span>
						<span class="restaurant-card__hours">Horaires de retrait à confirmer</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>

	</main>
</div>

<?php
get_footer();
