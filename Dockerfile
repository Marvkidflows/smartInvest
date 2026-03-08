FROM php:8.4-cli

WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring zip

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy project
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Laravel storage permissions
RUN chmod -R 777 storage bootstrap/cache

# Expose port
EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=$PORT