# QMS

Quality, Safety, Risk and Improvement platform for qms.ysaidea.com.

## Overview

QMS is a Laravel application for reporting, reviewing and managing safety, quality and assurance work.

The product includes:

- Reporter experience for safety and quality concerns
- Observation workflow for Unsafe Act and Unsafe Condition reporting
- Reports workspace
- Incidents
- Actions
- Investigations
- Risk register
- Audits and inspections
- NCR and CAPA
- Controlled documents
- Training
- Compliance and standards registry
- Administration control center
- Form Studio
- Workflow Studio
- Notification and report designers

## Current Focus

The current UAT focus is the Observation workflow:

1. Create Observation.
2. Choose Unsafe Act or Unsafe Condition.
3. Complete the page-based form.
4. HSE reviews the observation.
5. HSE creates actions only when follow-up is needed.

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

## Test

```bash
php artisan test
npm run build
```

## Production

Use the production runbook:

```text
docs/PRODUCTION_RUNBOOK.md
```

Production must use:

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=https://qms.ysaidea.com
```

Do not deploy with local SQLite settings unless explicitly approved.

## VPS Deployment

The Hostinger deployment script is available at:

```text
deploy/hostinger_publish_qms.sh
```

For the current VPS folder, deploy from:

```bash
cd /var/www/miniworld-pro/current/special-need
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci || npm install
npm run build
php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --class=QmsReporterProductSeeder --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
sudo systemctl reload php8.4-fpm
sudo systemctl reload nginx
```

Always back up files, storage and database before deploying.
