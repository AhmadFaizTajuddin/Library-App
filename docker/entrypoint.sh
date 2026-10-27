#!/bin/sh
# Runs every time the container starts.
set -e
cd /var/www/html

php artisan config:clear || true
php artisan storage:link --force || true

echo "Running database migrations..."
php artisan migrate --force

if [ "$SEED_DEMO" = "true" ]; then
    echo "Seeding demo data (skipped automatically when users already exist)..."
    php artisan db:seed --force
fi

# Artisan ran as root: make sure Apache (www-data) can still write logs, cache and sessions.
chown -R www-data:www-data storage bootstrap/cache

exec apache2-foreground
