#!/bin/sh
set -e

echo "🚀 Starting Laravel container..."

# Pastikan folder ada
mkdir -p storage bootstrap/cache
mkdir -p storage/framework/views

# Permission
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Generate APP_KEY kalau kosong
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate --force
fi

# Storage link
php artisan storage:link || true

# Clear cache (aman)
php artisan optimize:clear || true

# Package discover (amanin)
php artisan package:discover || true

# Cache ulang (jangan pakai view:cache dulu!)
php artisan config:cache || true
php artisan route:cache || true

# Migration
php artisan migrate --force || true

echo "🚀 Gawe SKP ready"

exec "$@"
