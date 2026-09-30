FROM php:8.4-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    bash \
    curl \
    git \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    zip \
    unzip \
    nodejs \
    npm \
    nginx \
    supervisor \
    mysql-client \
    libsodium-dev \
    icu-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    sodium \
    intl

# Explicitly enable installed extensions so their ini files are present in conf.d
RUN docker-php-ext-enable \
    bcmath \
    exif \
    gd \
    opcache \
    pcntl \
    pdo_mysql \
    sodium \
    intl \
    zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dependencies stage
FROM base AS deps

COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist

COPY package.json package-lock.json ./
RUN npm ci

# Builder stage
FROM deps AS builder

COPY . .

RUN composer dump-autoload --optimize

# Build frontend assets (client + SSR)
RUN npm run build:ssr

# Production stage
FROM base AS production

WORKDIR /var/www/html

# Copy built application
COPY --from=builder /var/www/html /var/www/html

# Prepare runtime directories and permissions (Nixpacks-like build phase)
RUN mkdir -p /etc/supervisor/conf.d \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /var/log/nginx \
    && mkdir -p /var/log \
    && mkdir -p /var/www/html/storage/framework/cache/data \
    && mkdir -p /var/www/html/storage/framework/session \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy nginx config
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Copy supervisor config
COPY docker/supervisord.conf /etc/supervisord.conf

# Copy php-fpm config
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy opcache config
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copy entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
