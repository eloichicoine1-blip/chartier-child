<?php
/**
 * Carte d'un membre dans la liste de l'équipe (IT-11).
 * Appelée par archive-membre.php avec get_template_part( 'template-parts/membre', 'carte' ).
 *
 * @package chartier-child
 */

defined( 'ABSPATH' ) || exit;

$poste      = function_exists( 'get_field' ) ? get_field( 'poste' ) : '';
$restaurant = function_exists( 'get_field' ) ? get_field( 'restaurant' ) : '';
?>
<li class="team-card">
	<a class="team-card__link" href="<?php the_permalink(); ?>">
		<?php chartier_member_portrait( 'team-card__portrait' ); ?>
		<span class="team-card__body">
			<?php if ( $restaurant ) : ?>
				<span class="team-card__place"><?php echo esc_html( $restaurant ); ?></span>
			<?php endif; ?>
			<span class="team-card__name"><?php the_title(); ?></span>
			<?php if ( $poste ) : ?>
				<span class="team-card__role"><?php echo esc_html( $poste ); ?></span>
			<?php endif; ?>
		</span>
	</a>
</li>
