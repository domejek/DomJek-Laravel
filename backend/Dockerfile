FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libgd-dev \
    libzip-dev \
    libzip4

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www/html

# Install dependencies
RUN composer update --no-dev --optimize-autoloader

# Generate application key
RUN php artisan key:generate --force

# Create storage link
RUN php artisan storage:link

# Cache configuration
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Expose port 9000 and start php-fpm server
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
