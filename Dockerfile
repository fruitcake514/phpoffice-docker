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

# Create mnt directory and set permissions
RUN mkdir -p /app/mnt && chown -R www-data:www-data /app/mnt && chmod -R 755 /app/mnt

# Set permissions for the entire app directory
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
