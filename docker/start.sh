#!/bin/sh
cd /var/www/html || exit 1

PORT="${PORT:-8080}"
echo "=== Cambo-Mart boot (PORT=${PORT}) ==="

if [ -z "$APP_KEY" ]; then
    echo "FATAL: APP_KEY is not set in Render Environment Variables."
    exit 1
fi

php artisan storage:link --force 2>/dev/null || true

if [ "$RUN_MIGRATIONS" = "true" ]; then
    php artisan migrate --force 2>&1 || echo "WARN: migrations failed (check DB_* env vars)"
fi

rm -f /etc/nginx/http.d/default.conf 2>/dev/null || true
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf

nginx -t 2>&1 || {
    echo "FATAL: nginx config invalid"
    cat /etc/nginx/http.d/default.conf
    exit 1
}

php-fpm -D
echo "=== nginx listening on 0.0.0.0:${PORT} ==="
exec nginx -g 'daemon off;'
