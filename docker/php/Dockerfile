FROM php:8.3-fpm-alpine

ENV MAKEFLAGS="-j1"

RUN apk add --no-cache bash curl git unzip libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev oniguruma-dev icu-dev linux-headers $PHPIZE_DEPS

RUN docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install -j1 pdo_mysql mysqli zip gd mbstring intl bcmath opcache pcntl sockets

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory.ini && echo "upload_max_filesize=20M" > /usr/local/etc/php/conf.d/upload.ini && echo "post_max_size=20M" > /usr/local/etc/php/conf.d/post.ini && echo "max_execution_time=300" > /usr/local/etc/php/conf.d/timeout.ini && echo "date.timezone=America/Sao_Paulo" > /usr/local/etc/php/conf.d/timezone.ini

RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini && echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/opcache.ini && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www

WORKDIR /var/www/html

COPY --chown=www:www backend/composer*.json ./

RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction 2>/dev/null || true

COPY --chown=www:www backend/ ./

RUN composer dump-autoload --optimize --no-dev 2>/dev/null || true

RUN chown -R www:www /var/www/html && (chmod -R 775 storage 2>/dev/null || true) && (chmod -R 775 bootstrap/cache 2>/dev/null || true)

USER www

EXPOSE 9000

CMD ["php-fpm"]
