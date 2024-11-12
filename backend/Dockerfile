FROM php:8.3-fpm

# COPY . /var/www/backend
# COPY --chown=www:www . /var/www/backend
WORKDIR /var/www/backend

# COPY composer.lock composer.json /var/www/backend/

RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libpq-dev \
    libonig-dev \
    libzip-dev 

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_pgsql mbstring zip exif pcntl

RUN curl -sS -o installer https://getcomposer.org/installer && php ./installer --install-dir=/usr/local/bin --filename=composer

RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

COPY . /var/www/backend/
COPY --chown=www:www . /var/www
USER www

# Устанавливаем зависимости через Composer
RUN composer install --no-dev --optimize-autoloader

# Оптимизация Laravel
RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

# Копируем и настраиваем entrypoint
COPY --chown=www:www ./backend/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Переключаемся на пользователя www
USER www

# Открываем порт и устанавливаем ENTRYPOINT
EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
