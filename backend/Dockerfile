FROM nginx:latest AS webserver

FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
        nano vim zip unzip curl locales procps \
        libpq-dev libonig-dev libzip-dev \
        && apt-get clean

RUN docker-php-ext-install pdo_pgsql mbstring zip exif pcntl
RUN curl -sS -o installer https://getcomposer.org/installer \
	&& php ./installer --install-dir=/usr/local/bin --filename=composer

ADD https://github.com/nicolas-van/multirun/releases/download/1.1.3/multirun-x86_64-linux-gnu-1.1.3.tar.gz /tmp/multirun.tar.gz
RUN tar -xzf /tmp/multirun.tar.gz -C /usr/sbin/ \
            && rm /tmp/multirun.tar.gz \
            && chmod +x /usr/sbin/multirun

WORKDIR /var/www/backend

RUN groupadd -g 1000 www \
	&& useradd -u 1000 -ms /bin/bash -g www www

COPY ./backend .

COPY --from=webserver /usr/sbin/nginx /usr/sbin/nginx
COPY --from=webserver /etc/nginx /etc/nginx
COPY --from=webserver /usr/share/nginx /usr/share/nginx
COPY --from=webserver /usr/lib/nginx /usr/lib/nginx

RUN touch /var/run/nginx.pid \
        && mkdir -p /var/lib/nginx /var/log/nginx /var/cache/nginx \
        && chown -R www:www . /var/lib/nginx /var/log/nginx /var/cache/nginx /usr/lib/nginx /var/run/nginx.pid

COPY ./docker/nginx/conf.d/backend.conf /etc/nginx/conf.d/default.conf
USER www
RUN composer install --no-dev --optimize-autoloader \
	&& php artisan config:cache \
	&& php artisan route:cache \
	&& php artisan view:cache \
        && php artisan optimize \
        && php artisan migrate

EXPOSE 82

CMD ["multirun", "nginx -g \"daemon off;\"", "php-fpm"]