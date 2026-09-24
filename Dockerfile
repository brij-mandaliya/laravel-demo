FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    nodejs \
    npm \
    git \
    && docker-php-ext-install pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

COPY package.json package-lock.json vite.config.js tailwind.config.js postcss.config.js ./
COPY resources/ resources/
RUN npm ci && npm run build

COPY . .

RUN composer run-script post-autoload-dump && \
    chmod -R 775 storage bootstrap/cache

RUN echo "#!/bin/sh" > /docker-entrypoint.sh \
    && echo "php artisan migrate --force" >> /docker-entrypoint.sh \
    && echo "[ -z \"\$APP_URL\" ] && export APP_URL=\"https://\${RENDER_EXTERNAL_HOST}.onrender.com\"" >> /docker-entrypoint.sh \
    && echo "php artisan serve --host=0.0.0.0 --port=\$PORT" >> /docker-entrypoint.sh \
    && chmod +x /docker-entrypoint.sh

EXPOSE 8000

CMD ["/docker-entrypoint.sh"]
