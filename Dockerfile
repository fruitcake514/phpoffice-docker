# Use the official PHP image from the Docker Hub
FROM php:8.1-fpm

# Install dependencies and required PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    nginx \
    libzip-dev \
    libgd-dev \
    supervisor \
    && docker-php-ext-install pdo pdo_mysql zip gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory for the application
WORKDIR /app

# Clone the GitHub repository (this will pull everything into /app)
RUN git clone https://github.com/fruitcake514/phpoffice-docker.git .

# Copy the composer.json and composer.lock (if it exists) to the working directory
COPY composer.json composer.lock* ./

# Set the environment variable for Composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install project dependencies using Composer
RUN composer install --no-interaction --prefer-dist

# Copy Nginx configuration
COPY nginx.conf /etc/nginx/sites-available/default

# Create Nginx log directories and set permissions
RUN mkdir -p /var/log/nginx \
    && chown -R www-data:www-data /var/log/nginx \
    && chmod 755 /var/log/nginx

# Create supervisord.conf
RUN echo "[supervisord]\nnodaemon=true\n\n[program:nginx]\ncommand=nginx -g 'daemon off;'\nautostart=true\nautorestart=true\nstdout_logfile=/dev/stdout\nstdout_logfile_maxbytes=0\nstderr_logfile=/dev/stderr\nstderr_logfile_maxbytes=0\n\n[program:php-fpm]\ncommand=php-fpm\nautostart=true\nautorestart=true\nstdout_logfile=/dev/stdout\nstdout_logfile_maxbytes=0\nstderr_logfile=/dev/stderr\nstderr_logfile_maxbytes=0" > /etc/supervisor/conf.d/supervisord.conf

# Expose the port
EXPOSE 80

# Start supervisord to manage Nginx and PHP-FPM
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
