#!/bin/bash

# Применяем миграции
php artisan migrate --force

# Оптимизируем кэш приложения
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Запускаем PHP-FPM
exec php-fpm
