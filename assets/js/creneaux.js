/**
 * Créneaux (IT-20) : selon le mode choisi (classe is-pickup posée par commande.js),
 * on montre la liste des créneaux de retrait OU de livraison ; l'autre est désactivée
 * (une liste désactivée n'est pas envoyée). Aujourd'hui, les créneaux qui commencent
 * dans moins d'1 h sont grisés. Le serveur refait toutes ces vérifications.
 */
(function ($) {
	function sync() {
		var $bloc = $('.delivery-slot');
		if (!$bloc.length) {
			return;
		}
		var mode = $('form.checkout').hasClass('is-pickup') ? 'retrait' : 'livraison';
		$bloc.toggleClass('is-pickup', mode === 'retrait');

		$bloc.find('[data-mode]').each(function () {
			var actif = $(this).data('mode') === mode;
			$(this).prop('hidden', !actif).find('select').prop('disabled', !actif);
		});

		var aujourdhui = String($bloc.data('today'));
		var heureMin = String($bloc.data('min'));
		var jour = $('#chartier_slot_date').val();
		$bloc.find('select[data-slots] option[data-start]').each(function () {
			var ferme = jour === aujourdhui && this.getAttribute('data-start') < heureMin;
			this.disabled = ferme;
			if (ferme && this.selected) {
				$(this).closest('select').val('');
			}
		});
	}

	// commande.js réagit aux mêmes événements : on passe juste après lui.
	function later() {
		window.setTimeout(sync, 0);
	}
	$(document.body).on('updated_checkout', later);
	$(document).on('change', 'input[name^="shipping_method"], #chartier_slot_date', later);
	$(later);
})(jQuery);
