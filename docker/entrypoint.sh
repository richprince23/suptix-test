#!/bin/bash
set -e

# Support both custom Dockerfile (/var/www/html) and nixpacks (/app)
if [ -d /var/www/html ] && [ -f /var/www/html/artisan ]; then
    export APP_DIR=/var/www/html
elif [ -d /app ] && [ -f /app/artisan ]; then
    export APP_DIR=/app
fi
cd "$APP_DIR"

# Generate .env from injected environment variables
# Coolify env vars take precedence over .env values (phpdotenv won't override existing env vars)
if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Cache configuration for production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
php artisan event:cache || true

# Run migrations
php artisan migrate --force || echo "Migration failed — app will still start"

# Create storage symlink if missing
php artisan storage:link || true

# Ensure correct permissions (covers volume mounts that override Dockerfile permissions)
mkdir -p storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache
touch storage/logs/laravel.log
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

exec "$@"
