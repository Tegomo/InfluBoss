#!/bin/bash
#
# Déploiement d'InfluBoss sur un hébergement cPanel.
# Lancé par .cpanel.yml depuis le clone Git du serveur ; peut aussi être lancé à la main en SSH :
#   bash ~/repositories/InfluBoss/deploy/cpanel-deploy.sh
#
# Étapes : vérifications -> sauvegarde MySQL -> maintenance -> copie du code -> composer (sans dev)
#          -> migrations -> lien storage -> caches -> remise en ligne.
# En cas d'erreur après la mise en maintenance, le site RESTE en maintenance (voir le message affiché).
#
# Configuration (fichier ~/.influboss-deploy.env, hors dépôt) :
#   DEPLOYPATH      dossier racine du site (obligatoire), ex. /home/compte/percoyouth.mccanndevs.com
#   PHP_BIN         binaire PHP CLI >= 8.2 (détecté sinon)
#   COMPOSER_BIN    composer.phar ou exécutable composer (détecté sinon)
#   BACKUP_DIR      dossier des sauvegardes SQL (défaut ~/influboss-backups)
#   KEEP_BACKUPS    nombre de sauvegardes conservées (défaut 10)
#   SKIP_DB_BACKUP  1 pour ne pas sauvegarder la base (déconseillé)

set -Eeuo pipefail

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
CONFIG_FILE="${INFLUBOSS_DEPLOY_CONFIG:-$HOME/.influboss-deploy.env}"
MAINTENANCE_ON=0

log()  { echo "[deploy $(date '+%H:%M:%S')] $*"; }
fail() { echo "[deploy] ERREUR : $*" >&2; exit 1; }

on_error() {
    local line="$1"
    # Le piège est hérité par les sous-shells (set -E) : n'afficher le message qu'une fois, depuis le shell principal.
    [ "$BASH_SUBSHELL" -eq 0 ] || return 0
    echo "[deploy] ERREUR à la ligne $line du script de déploiement." >&2
    if [ "$MAINTENANCE_ON" = "1" ]; then
        echo "[deploy] Le site est laissé EN MAINTENANCE pour éviter un état incohérent." >&2
        echo "[deploy] Corrigez le problème puis relancez le déploiement, ou remettez en ligne :" >&2
        echo "[deploy]   cd \"$DEPLOYPATH\" && \"$PHP_BIN\" artisan up" >&2
        echo "[deploy] Sauvegarde de la base avant déploiement : ${BACKUP_FILE:-aucune}" >&2
    fi
}
trap 'on_error $LINENO' ERR

# --- Configuration ------------------------------------------------------------------------------
if [ -f "$CONFIG_FILE" ]; then
    # shellcheck source=/dev/null
    . "$CONFIG_FILE"
fi

[ -n "${DEPLOYPATH:-}" ] || fail "DEPLOYPATH n'est pas défini. Créez $CONFIG_FILE avec : DEPLOYPATH=/home/<compte>/<dossier-du-site>"
DEPLOYPATH="${DEPLOYPATH%/}"
BACKUP_DIR="${BACKUP_DIR:-$HOME/influboss-backups}"
KEEP_BACKUPS="${KEEP_BACKUPS:-10}"
SKIP_DB_BACKUP="${SKIP_DB_BACKUP:-0}"

[ -d "$DEPLOYPATH" ] || fail "Le dossier du site $DEPLOYPATH n'existe pas (créez d'abord le domaine dans cPanel)."
[ "$DEPLOYPATH" != "$REPO_DIR" ] || fail "DEPLOYPATH ne doit pas être le dossier du dépôt Git ($REPO_DIR)."
[ -f "$DEPLOYPATH/.env" ] || fail "$DEPLOYPATH/.env est absent. Créez-le (contenu de .env-prod) avant le premier déploiement."

env_get() {
    # Valeur d'une clé du .env du site, sans guillemets.
    grep -E "^$1=" "$DEPLOYPATH/.env" | tail -n 1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'$//"
}

[ -n "$(env_get APP_KEY)" ] || fail "APP_KEY est vide dans $DEPLOYPATH/.env."

# --- Outils -------------------------------------------------------------------------------------
detect_php() {
    local candidate
    for candidate in "${PHP_BIN:-}" \
        /opt/cpanel/ea-php84/root/usr/bin/php /opt/cpanel/ea-php83/root/usr/bin/php /opt/cpanel/ea-php82/root/usr/bin/php \
        /usr/local/bin/ea-php84 /usr/local/bin/ea-php83 /usr/local/bin/ea-php82 \
        "$(command -v php 2>/dev/null || true)"; do
        [ -n "$candidate" ] && [ -x "$candidate" ] || continue
        if "$candidate" -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);' 2>/dev/null; then
            echo "$candidate"
            return 0
        fi
    done
    return 1
}

PHP_BIN="$(detect_php)" || fail "Aucun PHP >= 8.2 trouvé. Indiquez PHP_BIN dans $CONFIG_FILE (ex. /opt/cpanel/ea-php83/root/usr/bin/php)."

if [ -z "${COMPOSER_BIN:-}" ]; then
    for candidate in "$(command -v composer 2>/dev/null || true)" /opt/cpanel/composer/bin/composer "$HOME/bin/composer.phar" "$HOME/composer.phar"; do
        if [ -n "$candidate" ] && [ -f "$candidate" ]; then COMPOSER_BIN="$candidate"; break; fi
    done
fi
[ -n "${COMPOSER_BIN:-}" ] && [ -f "$COMPOSER_BIN" ] || fail "Composer introuvable. Indiquez COMPOSER_BIN dans $CONFIG_FILE."

log "Dépôt      : $REPO_DIR ($(git -C "$REPO_DIR" rev-parse --short HEAD 2>/dev/null || echo 'commit inconnu'))"
log "Site       : $DEPLOYPATH"
log "PHP        : $PHP_BIN ($("$PHP_BIN" -r 'echo PHP_VERSION;'))"
log "Composer   : $COMPOSER_BIN"

# --- 1. Sauvegarde de la base -------------------------------------------------------------------
BACKUP_FILE=""
if [ "$SKIP_DB_BACKUP" = "1" ]; then
    log "Sauvegarde de la base ignorée (SKIP_DB_BACKUP=1)."
elif [ "$(env_get DB_CONNECTION)" = "mysql" ]; then
    command -v mysqldump >/dev/null || fail "mysqldump introuvable : impossible de sauvegarder la base (SKIP_DB_BACKUP=1 pour forcer)."
    mkdir -p "$BACKUP_DIR"
    chmod 700 "$BACKUP_DIR"
    BACKUP_FILE="$BACKUP_DIR/$(env_get DB_DATABASE)_$(date '+%Y%m%d_%H%M%S').sql.gz"
    log "Sauvegarde de la base vers $BACKUP_FILE"
    MYSQL_PWD="$(env_get DB_PASSWORD)" mysqldump \
        --host="$(env_get DB_HOST)" --port="$(env_get DB_PORT)" --user="$(env_get DB_USERNAME)" \
        --single-transaction --quick --no-tablespaces --routines \
        "$(env_get DB_DATABASE)" | gzip > "$BACKUP_FILE"
    chmod 600 "$BACKUP_FILE"
    # Ne garder que les KEEP_BACKUPS sauvegardes les plus récentes.
    ls -1t "$BACKUP_DIR"/*.sql.gz 2>/dev/null | tail -n +"$((KEEP_BACKUPS + 1))" | xargs -r rm -f
else
    log "Base non MySQL : pas de sauvegarde automatique."
fi

# --- 2. Maintenance -----------------------------------------------------------------------------
if [ -f "$DEPLOYPATH/artisan" ] && [ -f "$DEPLOYPATH/vendor/autoload.php" ]; then
    log "Passage en maintenance"
    (cd "$DEPLOYPATH" && "$PHP_BIN" artisan down --retry=60) || log "Mise en maintenance impossible (poursuite du déploiement)."
fi
MAINTENANCE_ON=1

# --- 3. Copie du code ---------------------------------------------------------------------------
# Jamais copiés : le .env et les fichiers d'environnement, les données du serveur (storage, lien public/storage),
# les dépendances (installées par composer), les caches générés et les fichiers de développement.
# Chemins relatifs à la racine du projet (ancrés) :
ROOT_EXCLUDES=(
    ".git" ".env" ".env.*" ".env-*" ".cpanel.yml" "deploy" "docs" "tests" "node_modules" "vendor"
    "storage" "public/storage" "bootstrap/cache/*.php" ".phpunit.result.cache" "phpunit.xml"
    "CLAUDE.md" ".gitignoregg" "temp_controller.php"
)
# Motifs exclus à n'importe quelle profondeur :
ANY_EXCLUDES=("*.php.bak" "*.php.new")

log "Copie du code vers $DEPLOYPATH"
if command -v rsync >/dev/null; then
    rsync_args=(-a)
    for pattern in "${ROOT_EXCLUDES[@]}"; do rsync_args+=(--exclude="/$pattern"); done
    for pattern in "${ANY_EXCLUDES[@]}"; do rsync_args+=(--exclude="$pattern"); done
    rsync "${rsync_args[@]}" "$REPO_DIR/" "$DEPLOYPATH/"
else
    tar_args=()
    for pattern in "${ROOT_EXCLUDES[@]}"; do tar_args+=(--exclude="./$pattern"); done
    for pattern in "${ANY_EXCLUDES[@]}"; do tar_args+=(--exclude="$pattern"); done
    (cd "$REPO_DIR" && tar -cf - "${tar_args[@]}" .) | (cd "$DEPLOYPATH" && tar -xf -)
fi

# Arborescence d'écriture attendue par Laravel (déjà présente sur une installation existante).
mkdir -p "$DEPLOYPATH"/storage/app/public "$DEPLOYPATH"/storage/app/private \
         "$DEPLOYPATH"/storage/framework/cache/data "$DEPLOYPATH"/storage/framework/sessions \
         "$DEPLOYPATH"/storage/framework/views "$DEPLOYPATH"/storage/logs "$DEPLOYPATH"/bootstrap/cache
chmod -R ug+rwX "$DEPLOYPATH/storage" "$DEPLOYPATH/bootstrap/cache"

# Les manifestes générés en local référencent des paquets de développement absents en production.
rm -f "$DEPLOYPATH"/bootstrap/cache/*.php

# --- 4. Dépendances -----------------------------------------------------------------------------
log "Installation des dépendances (composer, sans paquets de développement)"
(cd "$DEPLOYPATH" && "$PHP_BIN" "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-progress)

# --- 5. Migrations ------------------------------------------------------------------------------
log "Migrations de la base"
(cd "$DEPLOYPATH" && "$PHP_BIN" artisan migrate --force --no-interaction)

# --- 6. Lien public/storage ---------------------------------------------------------------------
if [ -L "$DEPLOYPATH/public/storage" ]; then
    log "Lien public/storage déjà présent."
elif [ -e "$DEPLOYPATH/public/storage" ]; then
    log "ATTENTION : public/storage est un vrai dossier, pas un lien. Les photos envoyées depuis l'application"
    log "            n'y seront pas visibles. Déplacez son contenu dans storage/app/public, supprimez-le puis"
    log "            relancez le déploiement."
else
    log "Création du lien public/storage"
    (cd "$DEPLOYPATH" && "$PHP_BIN" artisan storage:link) \
        || ln -s "$DEPLOYPATH/storage/app/public" "$DEPLOYPATH/public/storage"
fi

# --- 7. Caches ----------------------------------------------------------------------------------
log "Reconstruction des caches (configuration, routes, vues, événements)"
(cd "$DEPLOYPATH" && "$PHP_BIN" artisan optimize:clear && "$PHP_BIN" artisan optimize)

# --- 8. Remise en ligne -------------------------------------------------------------------------
(cd "$DEPLOYPATH" && "$PHP_BIN" artisan up)
MAINTENANCE_ON=0

log "Déploiement terminé."
[ -n "$BACKUP_FILE" ] && log "Sauvegarde de la base : $BACKUP_FILE"
log "Rappel : la tâche cron du planificateur doit exister (cPanel > Tâches Cron) :"
log "  * * * * * cd $DEPLOYPATH && $PHP_BIN artisan schedule:run >> /dev/null 2>&1"
