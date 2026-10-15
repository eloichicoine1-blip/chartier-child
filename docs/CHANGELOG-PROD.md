# Journal des mises en production

| Date | Itération | Commit | Notes |
|---|---|---|---|
| 12/10/2026 | IT-1.4 | c93da00 | Première mise en ligne : WordPress 7.1.3, WooCommerce, Storefront, thème enfant cloné depuis GitHub, base locale importée (préfixe r3sae01_), permaliens /%postname%/. |
| 15/10/2026 | IT-2 | 24e58b0 | Thème enfant habillé avec les variables du Design System (en-tête, pied de page, barre du bas mobile), boutique en grille 3 colonnes avec tuiles de catégories, fiche produit simple. Base locale importée (11 produits, 3 catégories, menu principal, boutique ouverte) + médias envoyés en rsync. Sauvegarde prod faite avant import. |
| 15/10/2026 | IT-3 | 23efbdc | 11 produits passés en produits à options (24 variations, attributs globaux), stock disponible / faible (escargots, 4) / épuisé (bourguignon). Listes d'options au style du Design System, message « options manquantes » à la place de l'alerte du navigateur, prix « Dès » sur les produits à options, sélecteur de quantité « − 1 + », barre collante de Storefront désactivée. Base locale importée + médias, sauvegarde prod avant import. |
