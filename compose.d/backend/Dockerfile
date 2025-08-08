FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    librabbitmq-dev \
    libssh-dev \
    unzip \
    zip \
    gosu \
    iotop \
    gedit \
    net-tools \
    && rm -rf /var/lib/apt/lists/*

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        xml \
        zip \
        intl \
        bcmath \
        opcache

# Install PECL extensions
RUN pecl install \
    amqp \
    xdebug \
    && docker-php-ext-enable \
        amqp \
        xdebug

# Note: mcrypt is deprecated since PHP 7.2, using sodium instead (built-in)
# curl is already built-in in PHP 8.4

# Copy custom PHP-FPM pool configuration
COPY www.conf /usr/local/etc/php-fpm.d/

# Copy custom PHP configuration
COPY php.ini /usr/local/etc/php/

# Set working directory
WORKDIR /var/www

# Create user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Create necessary directories after creating user
RUN mkdir -p /var/lib/php/sessions \
    && mkdir -p /var/lib/php/wsdlcache \
    && chown -R www:www /var/lib/php

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
