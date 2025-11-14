FROM phpdockerio/php:8.2-fpm
WORKDIR /app

RUN apt-get update; \
    apt-get -y --no-install-recommends install \
        php8.2-intl \
        php8.2-xdebug \
        php8.2-pgsql; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /app

RUN composer install --no-interaction --optimize-autoloader