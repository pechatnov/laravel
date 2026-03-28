FROM phpdockerio/php:8.4-fpm

LABEL org.opencontainers.image.source="local/laravel-docker"

RUN apt-get update \
    && apt-get -y --no-install-recommends install \
    php8.4-bcmath \
    php8.4-curl \
    php8.4-dev \
    php8.4-gd \
    php8.4-imagick \
    php8.4-intl \
    php8.4-interbase \
    php8.4-mbstring \
    php8.4-mcrypt \
    php8.4-memcache \
    php8.4-memcached \
    php8.4-mysql \
    php8.4-opcache \
    php8.4-soap \
    php8.4-xml \
    php8.4-zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

COPY ./php.ini /etc/php/8.4/fpm/conf.d/90-php.ini
COPY ./php.ini /etc/php/8.4/cli/conf.d/90-php.ini

RUN usermod -u 2000 ubuntu
RUN usermod -u 1000 www-data

WORKDIR "/var/www/html"

EXPOSE 9000
