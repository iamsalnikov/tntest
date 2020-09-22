FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
    libmemcached-dev \
    zlib1g-dev \
    git \
    zip unzip

RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer && \
    docker-php-ext-install bcmath && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    echo no | pecl install memcached && \
    docker-php-ext-enable memcached

WORKDIR /app