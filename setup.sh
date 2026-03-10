#!/bin/bash
set -e

echo "Setting up the project..."

if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

echo "Generating app key..."
php artisan key:generate

if [ ! -f database/database.sqlite ]; then
    echo "Creating SQLite database..."
    touch database/database.sqlite
fi

echo "Running migrations..."
php artisan migrate --force

echo "Setup completed successfully!"
