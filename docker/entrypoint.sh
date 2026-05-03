#!/bin/sh
set -e

echo "🚀 Starting Laravel container..."

# Pastikan folder ada
mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache

# Permission
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 🔑 CEK & GENERATE APP_KEY (DI SINI TEMPATNYA)
if ! php artisan key:generate --show | grep -q "base64"; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate --force
fi

# Storage link
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link || true
fi

# Clear cache lama
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Package discover
php artisan package:discover

# Cache ulang
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migration
php artisan migrate --force || true

echo "🚀 Gawe SKP ready"

exec "$@"
