<?php

echo "Setting up the project...\n";

if (! file_exists('.env')) {
    echo "Creating .env from .env.example...\n";
    copy('.env.example', '.env');
}

echo "Generating app key...\n";
passthru('php artisan key:generate');

$dbDir = dirname(__DIR__).DIRECTORY_SEPARATOR.'database';
if (! is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

$dbFile = $dbDir.DIRECTORY_SEPARATOR.'database.sqlite';
if (! file_exists($dbFile)) {
    echo "Creating SQLite database...\n";
    touch($dbFile);
}

echo "Running migrations...\n";
passthru('php artisan migrate --force');

echo "Setup completed successfully!\n";
