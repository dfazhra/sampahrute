FROM php:8.4-cli

# Install system dependencies & PHP extensions required by Laravel + SQLite
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libsqlite3-dev \
    libonig-dev \
    && docker-php-ext-install pdo pdo_sqlite mbstring zip bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist

# Copy application source
COPY . .

# Prepare .env, APP_KEY, writable directories, and pre-seed SQLite database during build
RUN cp .env.example .env \
    && sed -i 's/APP_NAME=Laravel/APP_NAME=SampahRute/' .env \
    && sed -i 's/APP_LOCALE=en/APP_LOCALE=id/' .env \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database public/uploads/reports public/uploads/pickups \
    && touch database/database.sqlite \
    && composer dump-autoload --optimize --no-dev \
    && php artisan key:generate --force \
    && php artisan app:deploy-init \
    && chmod -R 777 storage bootstrap/cache database public/uploads .env

EXPOSE 8000

CMD ["sh", "-c", "php artisan app:deploy-init && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
