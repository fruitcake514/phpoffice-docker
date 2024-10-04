# Use the official PHP image from the Docker Hub
FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    nginx \
    && docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /app

# Copy the composer.json and composer.lock (if it exists) to the working directory
COPY composer.json composer.lock* ./

# Install project dependencies using Composer
RUN composer install --no-interaction --prefer-dist

# Clone the GitHub repository (replace with your actual GitHub repo URL)
RUN git clone https://github.com/fruitcake514/phpoffice-docker.git .

# Copy Nginx configuration
COPY nginx.conf /etc/nginx/sites-available/default

# Expose the port
EXPOSE 80

# Start Nginx and PHP-FPM
CMD ["sh", "-c", "service nginx start && php-fpm"]
