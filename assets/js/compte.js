/**
 * Mon compte (IT-9) : demande une confirmation avant d'annuler une commande.
 * Le lien « Annuler » de WooCommerce porte la classe .cancel ; sans confirmation, on ne le suit pas.
 */
document.addEventListener('click', function (event) {
	var link = event.target.closest('.woocommerce-orders-table .button.cancel, .woocommerce-order-details .button.cancel');
	if (!link) {
		return;
	}
	var row = link.closest('tr');
	var number = row ? row.querySelector('.woocommerce-orders-table__cell-order-number') : null;
	var label = number ? 'la commande ' + number.textContent.trim() : 'cette commande';
	if (!window.confirm('Annuler ' + label + ' ? Elle sera remboursée si vous l’avez déjà réglée.')) {
		event.preventDefault();
	}
});
