#!/bin/sh
set -e

mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link --force >/dev/null 2>&1 || true
fi

exec "$@"
