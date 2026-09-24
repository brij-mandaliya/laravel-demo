FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    nodejs \
    npm \
    git \
    nginx \
    && docker-php-ext-install pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

COPY package.json package-lock.json vite.config.js tailwind.config.js postcss.config.js ./
COPY resources/ resources/
RUN npm ci && npm run build

COPY . .

RUN mkdir -p storage/framework/{views,cache,sessions} storage/logs && \
    composer run-script post-autoload-dump && \
    chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache

RUN echo "#!/bin/sh" > /docker-entrypoint.sh \
    && echo 'if [ -z \"\$APP_KEY\" ] || [ \"\$(echo \"\$APP_KEY\" | wc -c)\" -lt 20 ]; then' >> /docker-entrypoint.sh \
    && echo '    php artisan key:generate --force' >> /docker-entrypoint.sh \
    && echo 'fi' >> /docker-entrypoint.sh \
    && echo "php artisan migrate --force" >> /docker-entrypoint.sh \
    && echo "[ -z \"\$APP_URL\" ] && export APP_URL=\"https://\${RENDER_EXTERNAL_HOST}.onrender.com\"" >> /docker-entrypoint.sh \
    && echo "php-fpm -D" >> /docker-entrypoint.sh \
    && echo "nginx -g 'daemon off;'" >> /docker-entrypoint.sh \
    && chmod +x /docker-entrypoint.sh

COPY nginx.conf /etc/nginx/http.d/default.conf

EXPOSE 80

CMD ["/docker-entrypoint.sh"]
