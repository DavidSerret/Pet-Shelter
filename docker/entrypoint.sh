#!/bin/sh
set -e

cd /var/www/html

# Parse DATABASE_URL if provided by Fly.io Managed Postgres
# Fly.io sets DATABASE_URL as postgres://user:pass@host:port/dbname
if [ -n "$DATABASE_URL" ]; then
    DB_USER=$(echo $DATABASE_URL | sed 's|postgres://||' | cut -d: -f1)
    DB_PASS=$(echo $DATABASE_URL | cut -d: -f3 | cut -d@ -f1)
    DB_HOST=$(echo $DATABASE_URL | cut -d@ -f2 | cut -d: -f1)
    DB_PORT=$(echo $DATABASE_URL | cut -d@ -f2 | cut -d: -f2 | cut -d/ -f1)
    DB_NAME=$(echo $DATABASE_URL | cut -d/ -f4 | cut -d? -f1)

    export DB_CONNECTION=pgsql
    export DB_HOST=$DB_HOST
    export DB_PORT=$DB_PORT
    export DB_DATABASE=$DB_NAME
    export DB_USERNAME=$DB_USER
    export DB_PASSWORD=$DB_PASS
fi

# Cache config for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force

# Fix permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

exec "$@"
