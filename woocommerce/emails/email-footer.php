<?php
/**
 * Pied des e-mails — Bouillon Chartier (IT-8, US-8.2)
 *
 * Surcharge de woocommerce/templates/emails/email-footer.php.
 * Reprend le pied de page rouge du site : logo blanc, adresses des bouillons,
 * puis le texte réglé dans WooCommerce → Réglages → E-mails (« Texte du pied de page »).
 *
 * @package Chartier_Child
 * @version 10.4.0
 */

defined( 'ABSPATH' ) || exit;

$email    = $email ?? null;
$logo_url = get_stylesheet_directory_uri() . '/assets/img/logo-email-blanc.png';

$email_footer_text = get_option( 'woocommerce_email_footer_text' );
/** Cette option est documentée dans templates/emails/email-styles.php de WooCommerce. */
if ( apply_filters( 'woocommerce_is_email_preview', false ) ) {
	$text_transient    = get_transient( 'woocommerce_email_footer_text' );
	$email_footer_text = false !== $text_transient ? $text_transient : $email_footer_text;
}
?>
																		</div>
																	</td>
																</tr>
															</table>
															<!-- End Content -->
														</td>
													</tr>
												</table>
												<!-- End Body -->
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<!-- Footer -->
									<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_footer" role="presentation">
										<tr>
											<td valign="top" id="credit">
												<img src="<?php echo esc_url( $logo_url ); ?>" width="140" alt="Bouillon Chartier" />
												<p class="chartier-footer-places">Grands Boulevards · Montparnasse · Gare de l’Est</p>
												<?php
												echo wp_kses_post(
													wpautop(
														wptexturize(
															/** Filtre de WooCommerce sur le texte du pied de page (depuis 4.0.0). */
															apply_filters( 'woocommerce_email_footer_text', $email_footer_text, $email )
														)
													)
												);
												?>
											</td>
										</tr>
									</table>
									<!-- End Footer -->
								</td>
							</tr>
						</table>
					</div>
				</td>
				<td><!-- Vide : largeur stable dans tous les clients mail. --></td>
			</tr>
		</table>
	</body>
</html>
