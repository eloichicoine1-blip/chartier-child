/**
 * Commande (IT-6) : quand le client choisit un retrait, on affiche les
 * « Instructions pour le retrait » et on masque l'adresse (inutile pour un retrait).
 * WooCommerce recharge le récapitulatif à chaque changement : on resynchronise
 * sur l'événement « updated_checkout ».
 */
(function ($) {
	function isPickup() {
		var chosen = $('input[name^="shipping_method"]:checked, input[name^="shipping_method"][type="hidden"], select[name^="shipping_method"]').first().val() || '';
		return /^(local_pickup|pickup_location)/.test(chosen);
	}

	function sync() {
		$('form.checkout').toggleClass('is-pickup', isPickup());
	}

	$(document.body).on('updated_checkout', sync);
	$(document).on('change', 'input[name^="shipping_method"]', sync);
	$(sync);
})(jQuery);
