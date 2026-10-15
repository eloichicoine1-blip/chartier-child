/**
 * Fiche produit à options (IT-3, US-3.2)
 * WooCommerce affiche une alerte du navigateur quand on clique sur « Ajouter au panier »
 * sans avoir choisi toutes les options. On la remplace par le message de la maquette :
 * un bandeau d'erreur au-dessus du bouton et les listes manquantes encadrées en rouge.
 */
(function () {
  var MESSAGE = 'Choisissez une option dans chaque liste pour ajouter ce plat au panier.';

  // Phase de capture (true) : on passe avant le script de WooCommerce et on bloque son alerte.
  document.addEventListener('click', function (event) {
    var button = event.target.closest('.variations_form .single_add_to_cart_button.wc-variation-selection-needed');
    if (!button) {
      return;
    }
    event.preventDefault();
    event.stopPropagation();

    var form = button.closest('.variations_form');

    form.querySelectorAll('.variations select').forEach(function (select) {
      select.classList.toggle('is-missing', select.value === '');
    });

    if (!form.querySelector('.options-notice')) {
      var notice = document.createElement('p');
      notice.className = 'options-notice';
      notice.setAttribute('role', 'alert');
      notice.textContent = MESSAGE;
      form.querySelector('.single_variation_wrap').before(notice);
    }
  }, true);

  // Dès qu'une option est choisie, on retire l'erreur de cette liste, puis le message s'il n'en reste plus.
  document.addEventListener('change', function (event) {
    if (!event.target.matches('.variations select')) {
      return;
    }
    event.target.classList.remove('is-missing');

    var form = event.target.closest('.variations_form');
    var notice = form.querySelector('.options-notice');
    if (notice && !form.querySelector('.variations select.is-missing')) {
      notice.remove();
    }
  });
})();
