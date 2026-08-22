# ==========================================
# Stage 1 - PHP + Composer
# ==========================================
FROM php:8.3-fpm AS vendor

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

RUN docker-php-ext-install \
    pdo_mysql \
    intl \
    zip \
    exif \
    gd \
    bcmath \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction

COPY . .

RUN composer dump-autoload --optimize


# ==========================================
# Stage 2 - Frontend Build
# ==========================================
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./

RUN npm ci

COPY . .

RUN npm run build


# ==========================================
# Stage 3 - Runtime (PHP + Nginx + Supervisor + Python/OCR)
# ==========================================
FROM php:8.3-fpm

ARG APP_ENV=production
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=${APP_ENV}

RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    git \
    unzip \
    curl \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    python3 \
    python3-pip \
    python3-venv \
    tesseract-ocr \
    poppler-utils \
    && rm -rf /var/lib/apt/lists/*

# Install Python imaging packages for OCR runner
RUN pip3 install --no-cache-dir Pillow --break-system-packages || true

RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

RUN docker-php-ext-install \
    pdo_mysql \
    intl \
    zip \
    exif \
    gd \
    bcmath \
    opcache

WORKDIR /var/www/html

COPY . .

COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

COPY docker/nginx.conf /etc/nginx/sites-enabled/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom-php.ini
COPY docker/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

# Ensure storage, cache, and public asset directories exist
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/views \
    storage/framework/sessions \
    storage/app/public \
    storage/app/private \
    storage/app/documents \
    storage/app/livewire-tmp \
    storage/logs \
    bootstrap/cache \
    public/js \
    public/css \
    public/vendor \
    public/fonts

# Pre-publish Filament and Livewire assets during build
RUN php artisan filament:assets --ansi || true
RUN php artisan livewire:publish --assets --ansi || true
RUN php artisan icons:cache || true
RUN php artisan filament:cache-components || true

RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 storage bootstrap/cache public

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 CMD curl -f http://localhost:${PORT:-80}/up || exit 1

ENTRYPOINT ["/entrypoint.sh"]