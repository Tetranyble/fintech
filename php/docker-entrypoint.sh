#!/usr/bin/env sh
set -e

# Fix Laravel permissions on container start
echo "Fixing Laravel folder permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache

# Run the main container command
exec "$@"
