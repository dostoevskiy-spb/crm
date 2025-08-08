#!/bin/bash
set -e

# Create directories if they don't exist
mkdir -p /var/www/storage/logs
mkdir -p /var/www/storage/framework/cache
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/bootstrap/cache

# Fix permissions for Laravel storage and bootstrap cache
chown -R www:www /var/www/storage
chown -R www:www /var/www/bootstrap/cache
chmod -R 775 /var/www/storage
chmod -R 775 /var/www/bootstrap/cache

# If running php-fpm, start it as root (it will spawn worker processes as www user)
if [ "$1" = 'php-fpm' ]; then
    exec php-fpm
else
    # For other commands, switch to www user
    exec gosu www "$@"
fi
