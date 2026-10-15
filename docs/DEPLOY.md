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
