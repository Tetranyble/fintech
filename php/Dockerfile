FROM php:8.3-fpm-alpine

# Install dependencies
RUN apk add --no-cache \
    bash \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    icu-dev \
    git \
    curl \
    tzdata && \
    docker-php-ext-install pdo_mysql mbstring zip intl opcache

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html/backend

# Copy Laravel (you can skip this if using volume mount in docker-compose)
COPY ./src /var/www/html/credense

# Set permissions
#RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

USER www-data
CMD ["php-fpm"]
