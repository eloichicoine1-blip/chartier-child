<?php
/**
 * Fiche d'un membre de l'équipe — /equipe/{slug}/ (IT-11, US-11.3).
 *
 * Hiérarchie des templates : pour une publication du type « membre »,
 * WordPress cherche single-membre-{slug}.php, puis single-membre.php,
 * puis single.php, singular.php et index.php.
 *
 * @package chartier-child
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main member">
		<?php
		while ( have_posts() ) :
			the_post();
			$poste      = function_exists( 'get_field' ) ? get_field( 'poste' ) : '';
			$restaurant = function_exists( 'get_field' ) ? get_field( 'restaurant' ) : '';
			$arrivee    = function_exists( 'get_field' ) ? get_field( 'arrivee' ) : '';
			$plat       = function_exists( 'get_field' ) ? get_field( 'plat_prefere' ) : '';
			$citation   = function_exists( 'get_field' ) ? get_field( 'citation' ) : '';
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'member__card' ); ?>>

				<?php chartier_member_portrait( 'member__portrait' ); ?>

				<div class="member__body">
					<?php if ( $restaurant ) : ?>
						<p class="member__place"><?php echo esc_html( $restaurant ); ?></p>
					<?php endif; ?>

					<h1 class="member__name"><?php the_title(); ?></h1>

					<?php if ( $poste ) : ?>
						<p class="member__role"><?php echo esc_html( $poste ); ?></p>
					<?php endif; ?>

					<?php if ( $citation ) : ?>
						<blockquote class="member__quote"><p><?php echo esc_html( $citation ); ?></p></blockquote>
					<?php endif; ?>

					<div class="member__bio"><?php the_content(); ?></div>

					<?php if ( $arrivee || $plat ) : ?>
						<dl class="member__facts">
							<?php if ( $arrivee ) : ?>
								<div class="member__fact"><dt>Chez Chartier depuis</dt><dd><?php echo esc_html( $arrivee ); ?></dd></div>
							<?php endif; ?>
							<?php if ( $plat ) : ?>
								<div class="member__fact"><dt>Plat préféré</dt><dd><?php echo esc_html( $plat ); ?></dd></div>
							<?php endif; ?>
						</dl>
					<?php endif; ?>

					<a class="member__back" href="<?php echo esc_url( get_post_type_archive_link( 'membre' ) ); ?>">← Toute l’équipe</a>
				</div>

			</article>
		<?php endwhile; ?>
	</main>
</div>

<?php
get_footer();
