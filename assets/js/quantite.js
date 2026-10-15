/**
 * Boutons « − » et « + » du sélecteur de quantité (fiche produit et panier).
 * stepDown / stepUp respectent le minimum, le maximum et le pas fixés par WooCommerce.
 * L'événement « change » prévient WooCommerce (mise à jour du panier, etc.).
 */
document.addEventListener('click', function (event) {
  var button = event.target.closest('.quantity__button');
  if (!button) {
    return;
  }

  var input = button.parentElement.querySelector('input.qty');
  if (!input) {
    return;
  }

  if (button.classList.contains('quantity__button--minus')) {
    input.stepDown();
  } else {
    input.stepUp();
  }

  input.dispatchEvent(new Event('change', { bubbles: true }));
});
