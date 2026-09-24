#!/bin/sh

if [ -z "$APP_KEY" ] || ! echo "$APP_KEY" | grep -q '^base64:'; then
    export APP_KEY="base64:$(php -r "echo base64_encode(random_bytes(32));")"
fi

php artisan migrate --force

if [ -z "$APP_URL" ]; then
    export APP_URL="https://${RENDER_EXTERNAL_HOST}.onrender.com"
fi

php-fpm -D
nginx -g 'daemon off;'
