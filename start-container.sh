
#!/usr/bin/env bash
set -e

# 1) Dependencias PHP
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# 2) Preparativos de Laravel
php artisan key:generate --force || true
php artisan migrate --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3) Arranque (exponer $PORT de Railway)
exec php -S 0.0.0.0:${PORT} -t public
