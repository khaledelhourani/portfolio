# Portfolio CMS + Multi-Project Hosting Engine — single-container image.
# nginx + two php-fpm pools (portfolio @9000, jailed hosted @9001) + queue
# worker, all managed by supervisord.
FROM php:8.2-fpm-alpine

# ---- system deps ------------------------------------------------------------
RUN apk add --no-cache \
        nginx supervisor gettext bash \
        libzip-dev oniguruma-dev icu-dev libpng-dev mariadb-client \
    && docker-php-ext-install pdo_mysql mysqli zip gd intl opcache bcmath \
    && rm -rf /var/cache/apk/*

# ---- composer ---------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV HOSTING_PHP_FPM=127.0.0.1:9001 \
    HOSTING_CONTAINER_APP_PATH=/var/www/html \
    HOSTING_COMPOSER_PATH=/usr/bin/composer

WORKDIR /var/www/html

# ---- PHP dependencies (cached layer) ----------------------------------------
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# ---- application ------------------------------------------------------------
COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ---- service configs --------------------------------------------------------
COPY deployment/nginx.conf.template       /etc/nginx/nginx.conf.template
COPY deployment/php/hosted-pool.conf      /usr/local/etc/php-fpm.d/zz-hosted.conf
COPY deployment/supervisord.conf          /etc/supervisor/conf.d/supervisord.conf
COPY deployment/entrypoint.sh             /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
