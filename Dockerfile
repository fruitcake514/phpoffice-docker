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

# Clone the GitHub repository (replace with your actual GitHub repo URL)
RUN git clone https://github.com/PHPOffice/PhpSpreadsheet.git /app/PhpSpreadsheet \
    && git clone https://github.com/PHPOffice/PHPWord.git /app/PHPWord \
    && git clone https://github.com/PHPOffice/PHPPresentation.git /app/PHPPresentation

# Install project dependencies using Composer
RUN composer install --no-interaction --prefer-dist

# Copy Nginx configuration
COPY nginx.conf /etc/nginx/sites-available/default

# Expose the port
EXPOSE 80

# Start Nginx and PHP-FPM
CMD ["sh", "-c", "service nginx start && php-fpm"]
