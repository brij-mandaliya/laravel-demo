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

COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

COPY nginx.conf /etc/nginx/http.d/default.conf

EXPOSE 80

CMD ["/docker-entrypoint.sh"]
