FROM php:7.3-fpm

RUN apt update -y \
    && apt install -y \
        nginx \
        runit \
        sudo \
        libmcrypt-dev \
        libcurl4-gnutls-dev \
        libxml2-dev \
        libmhash-dev \
        libzip-dev \
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
        zip \
    && rm -rf /var/lib/apt/lists/*

# Change UID & GID to match users
RUN usermod -u 1000 www-data \
    && groupmod -g 1000 www-data

# Chown user's home dir
RUN chown -R www-data: /var/www

# Install composer
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

# Configure NGINX log dir
RUN mkdir -p /var/log/nginx \
    && chown www-data: /var/log/nginx

# Add www-data to sudoers
RUN echo '%sudo ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers \
    && echo '%www-data ALL=(root) NOPASSWD:ALL' >> /etc/sudoers

# Run services
COPY files/runit /etc/service
COPY files/entrypoint.sh /usr/sbin/
ENTRYPOINT /usr/sbin/entrypoint.sh

# Inject NGINX Global config files
COPY ./files/nginx /etc/nginx/global

USER www-data

RUN mkdir /var/www/.composer
WORKDIR /var/www
