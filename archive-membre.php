<?php
/**
 * Liste de l'équipe — /equipe/ (IT-11, US-11.3).
 *
 * Hiérarchie des templates : pour l'archive du type de contenu « membre »,
 * WordPress cherche archive-membre.php avant archive.php puis index.php.
 *
 * @package chartier-child
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main team">

		<header class="team__header">
			<div class="team__heading">
				<h1 class="team__title">L’équipe</h1>
				<p class="team__intro">En salle comme en cuisine, ce sont eux qui font tourner les trois bouillons, du premier bouillon servi au dernier café.</p>
			</div>
			<figure class="team__photo">
				<img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/equipe-service.jpg' ); ?>" width="970" height="1054" alt="Un serveur traverse la salle du Bouillon Chartier, une pile d’assiettes sur le bras." loading="eager">
			</figure>
		</header>

		<?php if ( have_posts() ) : ?>
			<ul class="team__grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/membre', 'carte' );
				endwhile;
				?>
			</ul>
		<?php else : ?>
			<p class="team__empty">L’équipe sera bientôt présentée ici.</p>
		<?php endif; ?>

	</main>
</div>

<?php
get_footer();
