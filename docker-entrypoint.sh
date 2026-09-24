#!/bin/sh

if [ -z "$APP_KEY" ] || ! echo "$APP_KEY" | grep -q '^base64:'; then
    php artisan key:generate --force
    export APP_KEY=$(grep '^APP_KEY=' .env | tail -1 | cut -d= -f2)
fi

php artisan migrate --force

if [ -z "$APP_URL" ]; then
    export APP_URL="https://${RENDER_EXTERNAL_HOST}.onrender.com"
fi

php-fpm -D
nginx -g 'daemon off;'
