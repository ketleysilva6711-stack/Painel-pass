FROM php:8.3-fpm-alpine

ENV MAKEFLAGS="-j1"

# Instalar dependências base + Java 17 + mysql-client (para mysqldump)
RUN apk add --no-cache bash curl git unzip libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev oniguruma-dev icu-dev linux-headers $PHPIZE_DEPS \
    openjdk17-jdk \
    mysql-client

# Instalar apktool e apksigner via Android SDK
ENV APKTOOL_VERSION=2.9.3
ENV ANDROID_SDK_ROOT=/opt/android-sdk
ENV PATH=${PATH}:/usr/local/bin:${ANDROID_SDK_ROOT}/cmdline-tools/latest/bin:${ANDROID_SDK_ROOT}/platform-tools:${ANDROID_SDK_ROOT}/build-tools/34.0.0

RUN mkdir -p /usr/local/bin && \
    # apktool - baixar jar apenas (usar java -jar direto no job)
    curl -L -o /usr/local/bin/apktool.jar "https://github.com/iBotPeaches/Apktool/releases/download/v${APKTOOL_VERSION}/apktool_${APKTOOL_VERSION}.jar" && \
    # Android SDK command-line tools para apksigner
    mkdir -p ${ANDROID_SDK_ROOT}/cmdline-tools && \
    curl -L -o /tmp/cmdline-tools.zip "https://dl.google.com/android/repository/commandlinetools-linux-11076708_latest.zip" && \
    unzip -q /tmp/cmdline-tools.zip -d ${ANDROID_SDK_ROOT}/cmdline-tools && \
    mv ${ANDROID_SDK_ROOT}/cmdline-tools/cmdline-tools ${ANDROID_SDK_ROOT}/cmdline-tools/latest && \
    rm /tmp/cmdline-tools.zip && \
    # Aceitar licenças e instalar build-tools (inclui apksigner)
    yes | ${ANDROID_SDK_ROOT}/cmdline-tools/latest/bin/sdkmanager --licenses > /dev/null 2>&1 && \
    ${ANDROID_SDK_ROOT}/cmdline-tools/latest/bin/sdkmanager "build-tools;34.0.0" "platform-tools" > /dev/null 2>&1 && \
    # Verificar instalações (usar paths completos)
    java -version && \
    java -jar /usr/local/bin/apktool.jar --version && \
    ${ANDROID_SDK_ROOT}/build-tools/34.0.0/apksigner --version

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
