#!/bin/sh

if [ -z "$APP_KEY" ] || [ "$(echo "$APP_KEY" | wc -c)" -lt 20 ]; then
    php artisan key:generate --force
fi

php artisan migrate --force

if [ -z "$APP_URL" ]; then
    export APP_URL="https://${RENDER_EXTERNAL_HOST}.onrender.com"
fi

php-fpm -D
nginx -g 'daemon off;'
