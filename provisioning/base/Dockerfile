FROM php:7.3-fpm

RUN apt update -y \
    && apt install -y \
        nginx \
        runit \
        libmcrypt-dev \
        libcurl4-gnutls-dev \
        libxml2-dev \
        libmhash-dev \
    && docker-php-ext-install -j$(nproc) \
        curl \
        fileinfo \
        exif \
        iconv \
        json \
        mbstring \
        pdo \
        pdo_mysql \
        opcache \
        posix \
        xml \
    && rm -rf /var/lib/apt/lists/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
RUN mkdir /var/www/.composer \
    && chown -R www-data: /var/www/.composer

# Run services
COPY files/runit /etc/service
COPY files/entrypoint.sh /usr/sbin/
ENTRYPOINT /usr/sbin/entrypoint.sh

# Change UID & GID to match users
RUN usermod -u 1000 www-data \
    && groupmod -g 1000 www-data

# Inject NGINX Global config files
#COPY ./files/nginx /etc/nginx/global

USER www-data
WORKDIR /var/www/html
