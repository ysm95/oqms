#!/usr/bin/env bash
set -euo pipefail

# Hostinger VPS QMS deployment script.
# Run this ON THE VPS after reviewing all variables below.
#
# Goal:
# - Publish https://qms.ysaidea.com from https://github.com/ysm95/oqms.git
# - Back up the current Miniworld files before switching.
# - Use release folders so rollback is possible.
#
# Required environment variables may be exported before running:
#   export HOSTINGER_USER="your-hostinger-user"
#   export DB_DATABASE="qms_database"
#   export DB_USERNAME="qms_db_user"
#   export DB_PASSWORD="qms_db_password"
#
# Optional:
#   export REPO="https://github.com/ysm95/oqms.git"
#   export DOMAIN="qms.ysaidea.com"
#   export MINI_DOMAIN="miniworld.ysaidea.com"
#   export PHP_FPM_SERVICE="php8.4-fpm"
#   export WEB_USER="www-data"

REPO="${REPO:-https://github.com/ysm95/oqms.git}"
DOMAIN="${DOMAIN:-qms.ysaidea.com}"
MINI_DOMAIN="${MINI_DOMAIN:-miniworld.ysaidea.com}"
HOSTINGER_USER="${HOSTINGER_USER:-$USER}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.4-fpm}"
WEB_USER="${WEB_USER:-www-data}"

BASE="/home/${HOSTINGER_USER}/domains/${DOMAIN}"
MINIWORLD_PUBLIC="/home/${HOSTINGER_USER}/domains/${MINI_DOMAIN}/public_html"
RELEASES="${BASE}/releases"
SHARED="${BASE}/shared"
CURRENT="${BASE}/current"
PUBLIC_HTML="${BASE}/public_html"
RELEASE="$(date +%Y%m%d-%H%M%S)"
RELEASE_PATH="${RELEASES}/${RELEASE}"
BACKUP_ROOT="/home/${HOSTINGER_USER}/backups/miniworld-before-qms-${RELEASE}"

echo "Deploying QMS"
echo "Domain: ${DOMAIN}"
echo "Release: ${RELEASE_PATH}"
echo "Miniworld backup: ${BACKUP_ROOT}"

mkdir -p "${RELEASES}" "${SHARED}" "${BACKUP_ROOT}"

if [ -d "${MINIWORLD_PUBLIC}" ]; then
  echo "Backing up Miniworld public files..."
  cp -a "${MINIWORLD_PUBLIC}" "${BACKUP_ROOT}/public_html"
else
  echo "Miniworld public folder not found at ${MINIWORLD_PUBLIC}; continuing."
fi

if [ -f "${MINIWORLD_PUBLIC}/../.env" ]; then
  cp -a "${MINIWORLD_PUBLIC}/../.env" "${BACKUP_ROOT}/miniworld.env"
elif [ -f "${MINIWORLD_PUBLIC}/.env" ]; then
  cp -a "${MINIWORLD_PUBLIC}/.env" "${BACKUP_ROOT}/miniworld.env"
fi

if command -v mysqldump >/dev/null 2>&1 && [ -n "${MINIWORLD_DB_DATABASE:-}" ] && [ -n "${MINIWORLD_DB_USERNAME:-}" ]; then
  echo "Backing up Miniworld database..."
  MYSQL_PWD="${MINIWORLD_DB_PASSWORD:-}" mysqldump -u "${MINIWORLD_DB_USERNAME}" "${MINIWORLD_DB_DATABASE}" > "${BACKUP_ROOT}/miniworld_database.sql"
else
  echo "Miniworld database backup skipped. Set MINIWORLD_DB_DATABASE and MINIWORLD_DB_USERNAME to enable it."
fi

echo "Cloning QMS repository..."
git clone --depth=1 "${REPO}" "${RELEASE_PATH}"
cd "${RELEASE_PATH}"

echo "Preparing Laravel .env..."
if [ ! -f "${SHARED}/.env" ]; then
  cp .env.example "${SHARED}/.env"
  sed -i "s#^APP_NAME=.*#APP_NAME=\"QMS\"#" "${SHARED}/.env"
  sed -i "s#^APP_ENV=.*#APP_ENV=production#" "${SHARED}/.env"
  sed -i "s#^APP_DEBUG=.*#APP_DEBUG=false#" "${SHARED}/.env"
  sed -i "s#^APP_URL=.*#APP_URL=https://${DOMAIN}#" "${SHARED}/.env"
  sed -i "s#^DB_CONNECTION=.*#DB_CONNECTION=mysql#" "${SHARED}/.env"
  sed -i "s#^DB_DATABASE=.*#DB_DATABASE=${DB_DATABASE:-qms}#" "${SHARED}/.env"
  sed -i "s#^DB_USERNAME=.*#DB_USERNAME=${DB_USERNAME:-qms}#" "${SHARED}/.env"
  sed -i "s#^DB_PASSWORD=.*#DB_PASSWORD=${DB_PASSWORD:-}#" "${SHARED}/.env"
fi

ln -sfn "${SHARED}/.env" .env
mkdir -p "${SHARED}/storage"
rm -rf storage
ln -sfn "${SHARED}/storage" storage

if grep -Eq '^APP_DEBUG=(true|TRUE|1)$' "${SHARED}/.env"; then
  echo "APP_DEBUG must be false for production. Update ${SHARED}/.env and run again."
  exit 1
fi

echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "Installing frontend dependencies and building assets..."
npm ci || npm install
npm run build

echo "Optimizing Laravel..."
php artisan key:generate --force
php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --class=QmsReporterProductSeeder --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart || true

echo "Switching current release..."
ln -sfn "${RELEASE_PATH}" "${CURRENT}"

echo "Pointing public_html to Laravel public folder..."
if [ -e "${PUBLIC_HTML}" ] && [ ! -L "${PUBLIC_HTML}" ]; then
  mv "${PUBLIC_HTML}" "${BACKUP_ROOT}/qms_public_html_before_switch"
fi
ln -sfn "${CURRENT}/public" "${PUBLIC_HTML}"

echo "Fixing permissions..."
chown -R "${HOSTINGER_USER}:${HOSTINGER_USER}" "${BASE}" || true
chown -R "${WEB_USER}:${WEB_USER}" "${SHARED}/storage" "${RELEASE_PATH}/bootstrap/cache" 2>/dev/null || true
chmod -R ug+rwX "${SHARED}/storage" "${RELEASE_PATH}/bootstrap/cache"

echo "Reloading services..."
systemctl reload nginx 2>/dev/null || true
systemctl reload apache2 2>/dev/null || true
systemctl restart "${PHP_FPM_SERVICE}" 2>/dev/null || true

echo "Deployment complete: https://${DOMAIN}"
echo "Queue worker required:"
echo "  cd ${CURRENT} && php artisan queue:work --queue=default --sleep=3 --tries=3 --timeout=90"
echo "Scheduler cron required:"
echo "  * * * * * cd ${CURRENT} && php artisan schedule:run >> /dev/null 2>&1"
echo "Rollback example:"
echo "  ln -sfn <previous-release-path> ${CURRENT} && ln -sfn ${CURRENT}/public ${PUBLIC_HTML}"
