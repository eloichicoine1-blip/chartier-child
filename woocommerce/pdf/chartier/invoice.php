<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php do_action( 'wpo_wcpdf_before_document', $this->get_type(), $this->order ); ?>

<table class="head container">
	<tr>
		<td class="header">
			<?php if ( $this->has_header_logo() ) : ?>
				<?php do_action( 'wpo_wcpdf_before_shop_logo', $this->get_type(), $this->order ); ?>
				<?php $this->header_logo(); ?>
				<?php do_action( 'wpo_wcpdf_after_shop_logo', $this->get_type(), $this->order ); ?>
			<?php else : ?>
				<?php $this->title(); ?>
			<?php endif; ?>
		</td>
		<td class="shop-info">
			<?php do_action( 'wpo_wcpdf_before_shop_name', $this->get_type(), $this->order ); ?>
			<div class="shop-name"><h3><?php $this->shop_name(); ?></h3></div>
			<?php do_action( 'wpo_wcpdf_after_shop_name', $this->get_type(), $this->order ); ?>
			<?php do_action( 'wpo_wcpdf_before_shop_address', $this->get_type(), $this->order ); ?>
			<div class="shop-address"><?php $this->shop_address(); ?></div>
			<?php do_action( 'wpo_wcpdf_after_shop_address', $this->get_type(), $this->order ); ?>
			<?php do_action( 'wpo_wcpdf_before_shop_phone_number', $this->get_type(), $this->order ); ?>
			<?php if ( ! empty( $this->get_shop_phone_number() ) ) : ?>
				<div class="shop-phone-number"><?php $this->shop_phone_number(); ?></div>
			<?php endif; ?>
			<?php do_action( 'wpo_wcpdf_after_shop_phone_number', $this->get_type(), $this->order ); ?>
			<?php if ( ! empty( $this->get_shop_email_address() ) ) : ?>
				<div class="shop-email-address"><?php $this->shop_email_address(); ?></div>
			<?php endif; ?>
			<?php do_action( 'wpo_wcpdf_after_shop_email_address', $this->get_type(), $this->order ); ?>
			<?php /* Chartier (IT-17) : mentions obligatoires du vendeur (fictives). */ ?>
			<?php if ( ! empty( $this->get_shop_coc_number() ) ) : ?>
				<div class="shop-coc-number"><?php $this->shop_coc_number(); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $this->get_shop_vat_number() ) ) : ?>
				<div class="shop-vat-number">TVA intracommunautaire : <?php $this->shop_vat_number(); ?></div>
			<?php endif; ?>
		</td>
	</tr>
</table>

<?php do_action( 'wpo_wcpdf_before_document_label', $this->get_type(), $this->order ); ?>

<?php if ( $this->has_header_logo() ) : ?>
	<h1 class="document-type-label"><?php $this->title(); ?></h1>
<?php endif; ?>

<?php do_action( 'wpo_wcpdf_after_document_label', $this->get_type(), $this->order ); ?>

<table class="order-data-addresses">
	<tr>
		<td class="address billing-address">
			<?php do_action( 'wpo_wcpdf_before_billing_address', $this->get_type(), $this->order ); ?>
			<p><?php $this->billing_address(); ?></p>
			<?php do_action( 'wpo_wcpdf_after_billing_address', $this->get_type(), $this->order ); ?>
			<?php if ( isset( $this->settings['display_email'] ) ) : ?>
				<div class="billing-email"><?php $this->billing_email(); ?></div>
			<?php endif; ?>
			<?php if ( isset( $this->settings['display_phone'] ) ) : ?>
				<div class="billing-phone"><?php $this->billing_phone(); ?></div>
			<?php endif; ?>
		</td>
		<td class="address shipping-address">
			<?php if ( $this->show_shipping_address() ) : ?>
				<h3><?php $this->shipping_address_title(); ?></h3>
				<?php do_action( 'wpo_wcpdf_before_shipping_address', $this->get_type(), $this->order ); ?>
				<p><?php $this->shipping_address(); ?></p>
				<?php do_action( 'wpo_wcpdf_after_shipping_address', $this->get_type(), $this->order ); ?>
				<?php if ( isset( $this->settings['display_phone'] ) ) : ?>
					<div class="shipping-phone"><?php $this->shipping_phone(); ?></div>
				<?php endif; ?>
			<?php endif; ?>
		</td>
		<td class="order-data">
			<table>
				<?php do_action( 'wpo_wcpdf_before_order_data', $this->get_type(), $this->order ); ?>
				<?php if ( isset( $this->settings['display_number'] ) ) : ?>
					<tr class="invoice-number">
						<th><?php $this->number_title(); ?></th>
						<td><?php $this->number( $this->get_type() ); ?></td>
					</tr>
				<?php endif; ?>
				<?php if ( isset( $this->settings['display_date'] ) ) : ?>
					<tr class="invoice-date">
						<th><?php $this->date_title(); ?></th>
						<td><?php $this->date( $this->get_type() ); ?></td>
					</tr>
				<?php endif; ?>
				<?php if ( $this->show_due_date() ) : ?>
					<tr class="due-date">
						<th><?php $this->due_date_title(); ?></th>
						<td><?php $this->due_date(); ?></td>
					</tr>
				<?php endif; ?>
				<tr class="order-number">
					<th><?php $this->order_number_title(); ?></th>
					<td><?php $this->order_number(); ?></td>
				</tr>
				<tr class="order-date">
					<th><?php $this->order_date_title(); ?></th>
					<td><?php $this->order_date(); ?></td>
				</tr>
				<?php if ( $this->get_payment_method() ) : ?>
					<tr class="payment-method">
						<th><?php $this->payment_method_title(); ?></th>
						<td><?php $this->payment_method(); ?></td>
					</tr>
				<?php endif; ?>
				<?php do_action( 'wpo_wcpdf_after_order_data', $this->get_type(), $this->order ); ?>
			</table>
		</td>
	</tr>
</table>

<?php do_action( 'wpo_wcpdf_before_order_details', $this->get_type(), $this->order ); ?>

<table class="order-details">
	<thead>
		<tr>
			<?php
			// Chartier (IT-17) : les prix de la carte sont TTC, on le dit.
			$colonnes = wpo_wcpdf_get_simple_template_default_table_headers( $this );
			if ( isset( $colonnes['price'] ) ) {
				$colonnes['price'] = 'Prix TTC';
			}
			?>
			<?php foreach ( $colonnes as $column_class => $column_title ) : ?>
				<th class="<?php echo esc_attr( $column_class ); ?>"><?php echo esc_html( $column_title ); ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $this->get_order_items() as $item_id => $item ) : ?>
			<tr class="<?php echo esc_attr( $item['row_class'] ); ?>">
				<td class="product">
					<p class="item-name"><?php echo esc_html( $item['name'] ); ?></p>
					<?php do_action( 'wpo_wcpdf_before_item_meta', $this->get_type(), $item, $this->order ); ?>
					<div class="item-meta">
						<?php /* Chartier (IT-17) : UGS et poids retirés, inutiles pour le client. */ ?>
						<!-- ul.wc-item-meta -->
						<?php if ( ! empty( $item['meta'] ) ) : ?>
							<?php echo wp_kses_post( $item['meta'] ); ?>
						<?php endif; ?>
						<!-- / ul.wc-item-meta -->
					</div>
					<?php do_action( 'wpo_wcpdf_after_item_meta', $this->get_type(), $item, $this->order ); ?>
				</td>
				<td class="quantity"><?php echo esc_html( $item['quantity'] ); ?></td>
				<td class="price"><?php echo esc_html( $item['order_price'] ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<table class="notes-totals">
	<tbody>
		<tr class="no-borders">
			<td class="no-borders notes-cell">
				<?php do_action( 'wpo_wcpdf_before_document_notes', $this->get_type(), $this->order ); ?>
				<?php if ( $this->get_document_notes() ) : ?>
					<div class="document-notes">
						<h3><?php $this->notes_title(); ?></h3>
						<?php $this->document_notes(); ?>
					</div>
				<?php endif; ?>
				<?php do_action( 'wpo_wcpdf_after_document_notes', $this->get_type(), $this->order ); ?>
				<?php do_action( 'wpo_wcpdf_before_customer_notes', $this->get_type(), $this->order ); ?>
				<?php if ( $this->get_shipping_notes() ) : ?>
					<div class="customer-notes">
						<h3><?php $this->customer_notes_title(); ?></h3>
						<?php $this->shipping_notes(); ?>
					</div>
				<?php endif; ?>
				<?php do_action( 'wpo_wcpdf_after_customer_notes', $this->get_type(), $this->order ); ?>
			</td>
			<td class="no-borders totals-cell">
				<table class="totals">
					<tfoot>
						<?php
						// Chartier (IT-17) : une facture conforme affiche le total HT,
						// le taux et le montant de TVA, puis le total TTC.
						$devise = array( 'currency' => $this->order->get_currency() );
						$tva    = (float) $this->order->get_total_tax();
						$ttc    = (float) $this->order->get_total();
						$taux   = array();
						foreach ( $this->order->get_items( 'tax' ) as $ligne_tva ) {
							$taux[] = rtrim( rtrim( number_format( (float) $ligne_tva->get_rate_percent(), 2, ',', '' ), '0' ), ',' ) . ' %';
						}
						?>
						<?php foreach ( $this->get_woocommerce_totals() as $key => $total ) : ?>
							<?php if ( 'order_total' === $key ) : ?>
								<?php if ( $tva > 0 ) : ?>
									<tr class="total-ht">
										<th class="description">Total HT</th>
										<td class="price"><span class="totals-price"><?php echo wp_kses_post( wc_price( $ttc - $tva, $devise ) ); ?></span></td>
									</tr>
									<tr class="total-tva">
										<th class="description">TVA <?php echo esc_html( implode( ', ', array_unique( $taux ) ) ); ?></th>
										<td class="price"><span class="totals-price"><?php echo wp_kses_post( wc_price( $tva, $devise ) ); ?></span></td>
									</tr>
								<?php endif; ?>
								<tr class="order_total">
									<th class="description">Total TTC</th>
									<td class="price"><span class="totals-price"><?php echo wp_kses_post( wc_price( $ttc, $devise ) ); ?></span></td>
								</tr>
							<?php else : ?>
								<tr class="<?php echo esc_attr( $key ); ?>">
									<th class="description"><?php echo esc_html( $total['label'] ); ?></th>
									<td class="price"><span class="totals-price"><?php echo esc_html( $total['value'] ); ?></span></td>
								</tr>
							<?php endif; ?>
						<?php endforeach; ?>
					</tfoot>
				</table>
			</td>
		</tr>
	</tbody>
</table>

<?php do_action( 'wpo_wcpdf_after_order_details', $this->get_type(), $this->order ); ?>

<div class="bottom-spacer"></div>

<?php if ( $this->get_footer() ) : ?>
	<htmlpagefooter name="docFooter"><!-- required for mPDF engine -->
		<div id="footer">
			<!-- hook available: wpo_wcpdf_before_footer -->
			<?php $this->footer(); ?>
			<!-- hook available: wpo_wcpdf_after_footer -->
		</div>
	</htmlpagefooter><!-- required for mPDF engine -->
<?php endif; ?>

<?php do_action( 'wpo_wcpdf_after_document', $this->get_type(), $this->order ); ?>
