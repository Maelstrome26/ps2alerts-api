FROM ps2alerts-api:base

USER root

RUN apt update -y \
    && apt install -y \
        wget \
        git \
        htop \
    && rm -rf /var/lib/apt/lists/*

USER www-data

# Install symfony client
RUN mkdir /var/www/.symfony && wget https://get.symfony.com/cli/installer -O - | bash

USER root

RUN mv /var/www/.symfony/bin/symfony /usr/local/bin/symfony
COPY files/nginx /etc/nginx/conf.d

USER www-data

RUN git config --global user.email "maelstrome26@gmail.com" \
    && git config --global user.name "Matt Cavanagh"

