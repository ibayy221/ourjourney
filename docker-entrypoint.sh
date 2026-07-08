#!/bin/bash
set -e

# Create storage symlink if not exists
php artisan storage:link || true

# Run migrations if database connection is available
php artisan migrate --force || true

# Start Apache in foreground
exec apache2-foreground
