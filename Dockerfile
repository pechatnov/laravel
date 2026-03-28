FROM phpdockerio/php:8.4-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        php8.4-bcmath \
        php8.4-cli \
        php8.4-curl \
        php8.4-mbstring \
        php8.4-mysql \
        php8.4-opcache \
        php8.4-xml \
        php8.4-zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN id -u www-data &>/dev/null || usermod -u 1000 www-data || true

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
