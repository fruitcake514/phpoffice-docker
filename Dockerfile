FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libgd-dev \
    nginx \
    supervisor

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) zip xml gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Clone the repository
RUN git clone https://github.com/fruitcake514/phpoffice-docker.git .

# Install PHPOffice libraries
RUN composer install --no-dev --optimize-autoloader

# Copy configuration files
COPY nginx.conf /etc/nginx/sites-available/default
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create mnt directory
RUN mkdir -p /app/mnt

# Set ownership and permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app \
    && chmod -R 777 /app/mnt

# Configure PHP-FPM to run as www-data
RUN sed -i 's/user = www-data/user = www-data/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/group = www-data/group = www-data/g' /usr/local/etc/php-fpm.d/www.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
