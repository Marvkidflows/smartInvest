#!/bin/sh

# Exit immediately if a command fails
set -e

echo "Clearing config and cache..."
php artisan config:clear
php artisan cache:clear

echo "Running migrations..."
php artisan migrate --force

echo "Starting PHP server..."
# Use exec so this process replaces the shell (Railway needs this to detect server)
exec php -S 0.0.0.0:${PORT:-8000} -t public