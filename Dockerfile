FROM dunglas/frankenphp:1-php8.4-alpine AS base
WORKDIR /app
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && install-php-extensions pdo_mysql pdo_pgsql mbstring intl gd zip opcache

FROM base AS dependencies
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts --optimize-autoloader

FROM base AS runtime
COPY --from=dependencies /app/vendor ./vendor
COPY . .
COPY deploy/Caddyfile /etc/caddy/Caddyfile
ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr \
    APP_STORAGE_PATH=/tmp/tams-storage SESSION_DRIVER=database \
    CACHE_STORE=database QUEUE_CONNECTION=sync MEDIA_STORAGE_DRIVER=database \
    SESSION_SECURE_COOKIE=true PORT=80 XDG_CONFIG_HOME=/tmp/caddy/config XDG_DATA_HOME=/tmp/caddy/data
RUN mkdir -p bootstrap/cache && php artisan package:discover --ansi \
    && chmod +x deploy/start.sh
EXPOSE 80
CMD ["sh", "/app/deploy/start.sh"]
