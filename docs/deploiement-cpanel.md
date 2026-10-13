# Déploiement d'InfluBoss sur cPanel

Ce guide décrit la mise en place unique, puis le déploiement courant. Le déploiement s'appuie sur
« Git Version Control » de cPanel : `.cpanel.yml` lance `deploy/cpanel-deploy.sh`.

## Ce que fait un déploiement

1. Vérifie la configuration : dossier du site, `.env` présent, `APP_KEY` renseignée, PHP ≥ 8.2, Composer.
2. Sauvegarde la base MySQL dans `~/influboss-backups/` (les 10 dernières sont conservées).
3. Met le site en maintenance (page « Service indisponible » pendant 1 à 2 minutes).
4. Copie le code dans le dossier du site **sans jamais toucher** au `.env`, à `storage/` (fichiers envoyés,
   contrats, documents légaux, journaux) ni au lien `public/storage`.
5. Installe les dépendances PHP sans les paquets de développement.
6. Applique les migrations de base de données.
7. Crée le lien `public/storage` s'il manque.
8. Reconstruit les caches Laravel, puis remet le site en ligne.

En cas d'erreur après l'étape 3, **le site reste en maintenance** et le journal de déploiement indique la
sauvegarde à utiliser et la commande de remise en ligne.

## Mise en place (une seule fois)

### 1. Pousser le code sur GitHub

Le serveur récupère le code depuis `https://github.com/Tegomo/InfluBoss`. Pousser la branche `master` :

```
git push origin master
```

Le dépôt étant privé, cPanel a besoin d'un accès : dans cPanel, **Accès SSH > Gérer les clés SSH**, générer
une clé, puis l'ajouter dans GitHub comme **Deploy key** en lecture seule (Settings > Deploy keys). Utiliser
alors l'URL SSH `git@github.com:Tegomo/InfluBoss.git`.

### 2. Cloner le dépôt dans cPanel

**Git Version Control > Create** :

- Clone URL : `git@github.com:Tegomo/InfluBoss.git`
- Repository Path : `/home/<compte>/repositories/InfluBoss` (hors du dossier public du site)
- Repository Name : `InfluBoss`

### 3. Créer le fichier de configuration du déploiement

Avec le **Gestionnaire de fichiers** (afficher les fichiers cachés) ou le **Terminal**, créer
`/home/<compte>/.influboss-deploy.env` :

```
DEPLOYPATH=/home/<compte>/<dossier-du-site>
# Facultatif si détecté automatiquement :
# PHP_BIN=/opt/cpanel/ea-php83/root/usr/bin/php
# COMPOSER_BIN=/opt/cpanel/composer/bin/composer
```

`DEPLOYPATH` est la **racine du domaine** dans cPanel (Domaines > colonne « Racine du document »). Le
`.htaccess` du projet redirige ensuite toutes les requêtes vers `public/`.

Pour trouver PHP et Composer dans le Terminal cPanel :

```
ls /opt/cpanel/ea-php8*/root/usr/bin/php
command -v composer || ls /opt/cpanel/composer/bin/composer
```

### 4. Préparer le `.env` du site

Si le site tourne déjà, son `.env` est conservé tel quel. Sinon, créer `<DEPLOYPATH>/.env` à partir de
`.env-prod`. Vérifier ces réglages :

| Clé | Valeur attendue |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://percoyouth.mccanndevs.com` (sans `/` final) |
| `APP_KEY` | renseignée (ne jamais la changer sur un site en service) |
| `LOG_LEVEL` | `warning` (au lieu de `debug`) |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME` | compte SMTP réel (cPanel > Comptes de messagerie > Configurer le client) |
| `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | adresse réelle, par ex. `notifications@mccanndevs.com`, « InfluBoss » |

Tant que `MAIL_MAILER=log`, les alertes d'expiration de contrat n'arrivent pas par e-mail (elles restent
visibles dans la cloche de l'application).

### 5. Programmer la tâche cron

**Tâches Cron > Ajouter une nouvelle tâche Cron**, réglage « Une fois par minute » :

```
cd /home/<compte>/<dossier-du-site> && /opt/cpanel/ea-php83/root/usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Adapter le chemin de PHP. Sans cette tâche, les contrats ne passent pas automatiquement en « Expiré » et les
alertes 30/15/7 jours ne partent pas.

### 6. Premier déploiement

**Git Version Control > Manage > Pull or Deploy** : cliquer **Update from Remote**, puis
**Deploy HEAD Commit**. Le journal du déploiement s'affiche dans cPanel (Last Deployment Information).

## Déploiement courant

1. En local : `git push origin master`.
2. cPanel > Git Version Control > Manage > Pull or Deploy : **Update from Remote**, puis **Deploy HEAD Commit**.

Le même script peut être lancé dans le Terminal cPanel :

```
bash ~/repositories/InfluBoss/deploy/cpanel-deploy.sh
```

## Vérifications après le premier déploiement

- Connexion, liste des ambassadeurs, fiche d'un ambassadeur (onglets Contrats, Documents, Paiements).
- Une photo de profil s'affiche (sinon voir « Photos invisibles » ci-dessous).
- Envoi puis téléchargement d'un document légal ; l'URL directe du fichier ne doit pas être accessible.
- `php artisan schedule:list` (Terminal) liste `contracts:check-expirations` à 07:00.
- `php artisan contracts:check-expirations` s'exécute sans erreur.

## Incidents

**Le site reste en maintenance après un échec.** Lire le journal de déploiement, corriger, puis relancer le
déploiement. Pour remettre en ligne sans redéployer : `cd <DEPLOYPATH> && <PHP_BIN> artisan up`.

**Revenir à la base d'avant le déploiement.** Les sauvegardes sont dans `~/influboss-backups/` :

```
gunzip < ~/influboss-backups/<base>_<date>.sql.gz | mysql -h <DB_HOST> -u <DB_USERNAME> -p <DB_DATABASE>
```

Puis redéployer le commit précédent (Git Version Control permet de choisir le commit à déployer).

**Photos invisibles.** Si le journal signale que `public/storage` est un vrai dossier : déplacer son contenu dans
`storage/app/public`, supprimer `public/storage`, puis relancer le déploiement, qui recréera le lien.

**« Aucun PHP >= 8.2 trouvé » ou « Composer introuvable ».** Renseigner `PHP_BIN` ou `COMPOSER_BIN` dans
`~/.influboss-deploy.env`.

**« mysqldump introuvable ».** Demander l'outil à l'hébergeur, ou ajouter `SKIP_DB_BACKUP=1` dans
`~/.influboss-deploy.env` après avoir fait une sauvegarde manuelle (cPanel > Sauvegarde).
