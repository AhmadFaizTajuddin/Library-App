# Production image for hosts that run Docker (for example Render).
FROM php:8.3-apache

ENV PORT=10000 \
    COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip libzip-dev libpq-dev \
 && docker-php-ext-install pdo_mysql pdo_pgsql zip \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache listens on the port the host gives us ($PORT) and serves Laravel's public/ folder.
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf

WORKDIR /var/www/html

# Install PHP dependencies first so Docker can cache this layer.
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev --no-interaction \
 && mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint && chmod +x /usr/local/bin/entrypoint

EXPOSE 10000
CMD ["entrypoint"]
