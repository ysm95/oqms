# QMS Production Runbook

## Required Settings

Use `.env.production.example` as the production baseline and keep:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://qms.ysaidea.com`
- `QUEUE_CONNECTION=database`

Never deploy with local SQLite settings or debug mode enabled.

## Pre-Deployment Validation

Run from the release folder before switching traffic:

```bash
composer validate
composer audit
npm audit
npm run build
php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --class=QmsReporterProductSeeder --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Backups

Before replacing Miniworld or rolling a new QMS release:

```bash
mkdir -p ~/backups/qms-$(date +%Y%m%d-%H%M%S)
cp -a ~/domains/qms.ysaidea.com/shared/.env ~/backups/qms-$(date +%Y%m%d-%H%M%S)/.env
cp -a ~/domains/qms.ysaidea.com/shared/storage ~/backups/qms-$(date +%Y%m%d-%H%M%S)/storage
mysqldump -u qms_db_user -p qms_database > ~/backups/qms-$(date +%Y%m%d-%H%M%S)/database.sql
```

## Deployment

For the existing VPS folder currently serving the Laravel app:

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
php artisan queue:restart
sudo systemctl reload php8.4-fpm
sudo systemctl reload nginx
```

For a release-folder deployment, use `deploy/hostinger_publish_qms.sh` after setting the required environment variables.

## Smoke Test

After deployment, confirm:

- Login works.
- Home opens.
- My Work opens.
- Reports opens.
- Observation create path opens.
- Unsafe Act and Unsafe Condition are visible.
- Observation submits successfully.
- HSE Review tab opens on an Observation record.
- Action Tracker tab opens on an Observation record.
- Notification page opens.
- No debug error is visible.

## Queue Worker

Run one supervised worker for background notifications, integrations, reference-data refreshes, and AI approval jobs:

```bash
cd /home/YOUR_USER/domains/qms.ysaidea.com/current
php artisan queue:work --queue=default --sleep=3 --tries=3 --timeout=90
```

Restart workers after every deployment:

```bash
php artisan queue:restart
```

## Scheduler

Install this cron entry for recurring sync, reminders, escalations, and health checks:

```bash
* * * * * cd /home/YOUR_USER/domains/qms.ysaidea.com/current && php artisan schedule:run >> /dev/null 2>&1
```

## Rollback

If using release folders:

```bash
ln -sfn /home/YOUR_USER/domains/qms.ysaidea.com/releases/PREVIOUS_RELEASE /home/YOUR_USER/domains/qms.ysaidea.com/current
ln -sfn /home/YOUR_USER/domains/qms.ysaidea.com/current/public /home/YOUR_USER/domains/qms.ysaidea.com/public_html
php artisan queue:restart
sudo systemctl reload php8.4-fpm
sudo systemctl reload nginx
```

If deploying by Git pull:

```bash
cd /home/YOUR_USER/domains/qms.ysaidea.com/current
git log --oneline -10
git checkout PREVIOUS_COMMIT_SHA
composer install --no-dev --optimize-autoloader
npm ci || npm install
npm run build
php artisan optimize:clear
php artisan migrate:rollback --step=1 --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
sudo systemctl reload php8.4-fpm
sudo systemctl reload nginx
```
