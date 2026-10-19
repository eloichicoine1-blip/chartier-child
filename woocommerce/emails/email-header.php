<?php
/**
 * En-tête des e-mails — Bouillon Chartier (IT-8, US-8.2)
 *
 * Surcharge de woocommerce/templates/emails/email-header.php.
 * Reprend l'en-tête du site : bandeau noir « Commandez en ligne… » puis logo rouge sur fond blanc.
 * Les identifiants (#template_container, #template_header, #body_content…) sont conservés
 * pour que les styles d'e-mail de WooCommerce continuent de s'appliquer.
 *
 * @package Chartier_Child
 * @version 10.7.0
 */

defined( 'ABSPATH' ) || exit;

$store_name = $store_name ?? get_bloginfo( 'name', 'display' );
$logo_url   = get_stylesheet_directory_uri() . '/assets/img/logo-email-rouge.png';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
		<meta content="width=device-width, initial-scale=1.0" name="viewport">
		<title><?php echo esc_html( $store_name ); ?></title>
	</head>
	<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
		<table width="100%" id="outer_wrapper" role="presentation">
			<tr>
				<td><!-- Vide : largeur stable dans tous les clients mail. --></td>
				<td width="600">
					<div id="wrapper" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
						<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="inner_wrapper" role="presentation">
							<tr>
								<td align="center" valign="top">
									<!-- Bandeau + logo (en-tête du site) -->
									<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
										<tr>
											<td class="chartier-band">Commandez en ligne, retirez au comptoir</td>
										</tr>
										<tr>
											<td id="template_header_image" class="chartier-logo">
												<a href="<?php echo esc_url( home_url() ); ?>" target="_blank">
													<img src="<?php echo esc_url( $logo_url ); ?>" width="220" alt="<?php echo esc_attr( $store_name ); ?>" />
												</a>
											</td>
										</tr>
									</table>
									<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_container" role="presentation">
										<tr>
											<td align="center" valign="top">
												<!-- Header -->
												<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_header" role="presentation">
													<tr>
														<td id="header_wrapper">
															<h1><?php echo esc_html( $email_heading ); ?></h1>
														</td>
													</tr>
												</table>
												<!-- End Header -->
											</td>
										</tr>
										<tr>
											<td align="center" valign="top">
												<!-- Body -->
												<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_body" role="presentation">
													<tr>
														<td valign="top" id="body_content">
															<!-- Content -->
															<table border="0" cellpadding="20" cellspacing="0" width="100%" role="presentation">
																<tr>
																	<td valign="top" id="body_content_inner_cell">
																		<div id="body_content_inner">
