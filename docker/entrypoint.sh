#!/bin/sh
set -e

cd /var/www/html

if [ -z "$APP_KEY" ]; then
    echo "ERROR: Set APP_KEY in your host environment (e.g. php artisan key:generate --show)."
    exit 1
fi

php artisan storage:link --force 2>/dev/null || true

php artisan config:cache
php artisan view:cache

# Closure routes in web.php cannot be route:cached (Laravel limitation).
# Skipping route:cache so the container still starts.

if [ "$RUN_MIGRATIONS" = "true" ]; then
    php artisan migrate --force || echo "WARN: migrations failed; continuing startup."
fi

export PORT="${PORT:-8080}"
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf

exec "$@"
