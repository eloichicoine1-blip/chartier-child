# Processus de mise en production

Environnements :

| | Local (développement) | Production |
|---|---|---|
| URL | https://chartier.ddev.site | https://mmi.unilim.fr/~chicoine3/wordpress |
| Outil | DDEV (OrbStack, macOS) | SSH `chicoine3@mmi.unilim.fr`, WP-CLI |
| Dossier | `05_site-wordpress/chartier` | `~/public_html/wordpress` |
| Base | conteneur DDEV | `chicoine3`, préfixe `r3sae01_` |

Le code du thème enfant est versionné sur GitHub :
https://github.com/eloichicoine1-blip/chartier-child

Aucun mot de passe n'est écrit dans ce dépôt ni dans les commandes (`-p` et `--prompt=dbpass` les demandent de façon masquée).

## 1. Mise en production courante (code du thème)

Chaque itération qui modifie le thème :

```bash
# Mac, dossier du thème
git add -A
git commit -m "[IT-X] Description"
git push

# Serveur
ssh chicoine3@mmi.unilim.fr
cd ~/public_html/wordpress/wp-content/themes/chartier-child
git pull
```

Puis ajouter une ligne dans `docs/CHANGELOG-PROD.md` (date, itération, commit, notes), committer et pousser.

## 2. Réglages faits dans le back-office

Les réglages WooCommerce (expédition, paiements, pages, etc.) sont stockés en base, pas dans Git.
Ils sont refaits à l'identique dans le back-office de production après validation en local, ou par WP-CLI quand c'est possible.
On n'écrase pas la base de production une fois que des commandes de test du correcteur y existent.

## 3. Extensions

```bash
# Serveur, après installation et validation en local
cd ~/public_html/wordpress
wp plugin install <slug> --activate
wp language plugin install <slug> fr_FR
```

## 4. Synchronisation complète (première mise en ligne, IT-1.4)

Utilisée une seule fois, le 12/10/2026, pour copier le site local vers la production.

```bash
# Serveur : sauvegarde de la base existante
cd ~/public_html/wordpress
wp db export ~/sauvegarde-wp-vierge.sql

# Mac, dossier 05_site-wordpress/chartier
ddev export-db --file=chartier.sql.gz
scp chartier.sql.gz chicoine3@mmi.unilim.fr:~/
rsync -avz wp-content/plugins wp-content/uploads chicoine3@mmi.unilim.fr:~/public_html/wordpress/wp-content/
rsync -avz wp-content/themes/storefront chicoine3@mmi.unilim.fr:~/public_html/wordpress/wp-content/themes/

# Serveur
cd ~/public_html/wordpress/wp-content/themes && git clone https://github.com/eloichicoine1-blip/chartier-child.git
cd ~/public_html/wordpress
URL=$(wp option get siteurl)
gunzip ~/chartier.sql.gz && wp db import ~/chartier.sql && rm ~/chartier.sql
wp search-replace 'https://chartier.ddev.site' "$URL" --all-tables-with-prefix --skip-columns=guid
wp core update --version=7.1.3 && wp core update-db
wp theme activate chartier-child
printf "apache_modules:\n  - mod_rewrite\n" > wp-cli.yml
wp rewrite structure '/%postname%/' --hard
rm wp-cli.yml
wp language core update
```

Problème rencontré : le dossier `wp-content/uploads` existant appartenait au serveur web (« Permission denied » à l'envoi).
Solution : `mv uploads uploads-ancien` puis nouvel envoi.

## 4 bis. Mise en production avec contenu (IT-2 et suivantes, tant qu'aucune commande de test n'existe en prod)

Les produits, catégories, menus et réglages WooCommerce sont en base : on renvoie la base locale complète et les médias.

```bash
# Mac, dossier du thème : code
git add -A && git commit -m "[IT-X] Description" && git push

# Mac, dossier 05_site-wordpress/chartier : base + médias
ddev export-db --file=chartier-itX.sql.gz
scp chartier-itX.sql.gz chicoine3@mmi.unilim.fr:~/
rsync -avz wp-content/uploads/ chicoine3@mmi.unilim.fr:~/public_html/wordpress/wp-content/uploads/
rsync -avz wp-content/languages/ chicoine3@mmi.unilim.fr:~/public_html/wordpress/wp-content/languages/   # traductions FR (sinon le site s'affiche en anglais)

# Serveur
cd ~/public_html/wordpress
wp db export ~/sauvegarde-$(date +%F).sql
URL=$(wp option get siteurl)
(cd wp-content/themes/chartier-child && git pull)
gunzip -f ~/chartier-itX.sql.gz && wp db import ~/chartier-itX.sql && rm ~/chartier-itX.sql
wp search-replace 'https://chartier.ddev.site' "$URL" --all-tables-with-prefix --skip-columns=guid
wp rewrite flush
wp cache flush
```

Vérifier ensuite : thème actif `chartier-child` (`wp theme list`), boutique, une catégorie, une fiche produit, en desktop et mobile.
Dès que le correcteur aura passé des commandes en prod, on n'importe plus la base : on refait les réglages à la main (section 2).

## 5. Retour arrière

- Thème : `git checkout <commit>` dans le dossier du thème sur le serveur, ou `git revert` puis `git pull`.
- Base : `wp db import ~/sauvegarde-<date>.sql` (faire `wp db export ~/sauvegarde-$(date +%F).sql` avant toute opération risquée).

## 5. Réglages par script (depuis l'IT-10)

Depuis l'IT-6, on n'importe plus la base locale en production : des commandes de test existent en prod.
Les réglages et contenus d'une itération sont donc écrits dans un script PHP **relançable** du dossier `docs/`,
testé en local puis rejoué en production. Chaque script vérifie l'existant avant de créer (pas de doublon).

```bash
# Local
ddev wp eval-file wp-content/themes/chartier-child/docs/<script>.php
# Serveur (après git pull)
cd ~/public_html/wordpress
wp db export ~/sauvegarde-avant-itX.sql      # sauvegarde avant chaque mise en production
wp eval-file wp-content/themes/chartier-child/docs/<script>.php
```

| Script | Itération | Rôle |
|---|---|---|
| `promos-it10.php` | IT-10 | Codes promo |
| `equipe-it11.php` | IT-11 | Membres de l'équipe et lien de menu |
| `collections-it12.php` | IT-12 | Taxonomie « collection » et ses termes |
| `ventes-croisees-it13.php` | IT-13 | Associations de ventes croisées |
| `avis-it14.php` | IT-14 | Avis de démonstration (acheteurs vérifiés) |
| `accueil-it16.php` | IT-16 | Page d'accueil statique |
| `factures-it17.php` | IT-17 | TVA 10 % et réglages des factures PDF (logo importé) |
| `filtres-it18.php` | IT-18 | Attribut « Régime » et widgets de filtres |
| `livraison-poids-it19.php` | IT-19 | Tranches de livraison selon le poids |
| `seo-it21.php` | IT-21 | Titres et méta-descriptions Yoast (puis `wp yoast index --reindex`) |
| `legal-it22.php` | IT-22 | Mentions légales, CGV, confidentialité, case CGV |
| `plat-star-itW.php` | IT-W | Diagnostic du plat star (lecture seule) |

## 6. Extensions et traductions

Extensions actives (stack du sujet + outils demandés par les itérations) :
WooCommerce, Secure Custom Fields, Classic Editor, PDF Invoices & Packing Slips for WooCommerce (IT-17),
Yoast SEO (IT-21), Loco Translate (IT-Z). Les extensions installées d'office par WooCommerce
(Jetpack, WooPayments, PayPal, Pinterest, Reddit, Snapchat, Google Listings, Klaviyo, WooCommerce Services,
Akismet, Hello Dolly) sont désactivées (IT-Z).

```bash
cd ~/public_html/wordpress
wp language core update && wp language plugin install --all fr_FR && wp language plugin update --all && wp language theme update --all
```

Les traductions modifiées dans Loco Translate sont enregistrées dans `wp-content/languages/loco/` (emplacement
« Personnalisé », protégé des mises à jour). Ce dossier n'est pas dans Git : la modification est refaite dans
Loco en production.

## 7. Contrôles après une mise en production

- `git log --oneline -1` sur le serveur = dernier commit poussé.
- Parcours rapide en prod : accueil, carte (filtres), fiche, panier, commande (créneau, case CGV), Mon compte.
- Ligne ajoutée dans `docs/CHANGELOG-PROD.md`, commit « [IT-X] Journal de mise en production ».
- IT-Y : audit Lighthouse de la fiche produit (mobile et desktop), rapports dans `07_livrables/lighthouse/`.
