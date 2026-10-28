FROM node:22-bookworm-slim AS assets

WORKDIR /app

COPY package.json ./
RUN npm install --no-audit --no-fund

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

FROM php:8.3-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libonig-dev \
        libpq-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" mbstring opcache pdo_pgsql xml zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .
RUN composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts \
    && composer dump-autoload --no-dev --optimize --no-scripts \
    && php artisan package:discover --ansi

COPY --from=assets /app/public/build ./public/build

RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
        > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel \
    && printf 'upload_max_filesize=8M\npost_max_size=10M\n' \
        > /usr/local/etc/php/conf.d/uploads.ini \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    SESSION_DRIVER=database \
    SESSION_SECURE_COOKIE=true \
    CACHE_STORE=database \
    QUEUE_CONNECTION=sync

EXPOSE 80
