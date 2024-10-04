# Use the official PHP image
FROM php:8.1-fpm as base

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

# Clone the GitHub repository (replace with your actual GitHub repo URL)
RUN git clone https://github.com/your-username/your-repo-name.git .

# Install project dependencies using Composer
RUN composer install

# Copy Nginx configuration
COPY nginx.conf /etc/nginx/sites-available/default

# Expose the port
EXPOSE 80

# Start Nginx and PHP
CMD service nginx start && php-fpm
