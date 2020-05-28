FROM php:7-apache

RUN apt-get update \
    && apt-get install -y \
		wget zip unzip \
        libzip-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        sqlite3 libsqlite3-dev \
        libssl-dev \
    && pecl install mongodb \
    && pecl install redis \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) iconv gd pdo zip opcache pdo_sqlite \
    && a2enmod rewrite expires
RUN echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/mongodb.ini && \
    echo "extension=redis.so" > /usr/local/etc/php/conf.d/redis.ini

WORKDIR /var/www/html
COPY . .

RUN chown -R www-data:www-data /var/www/html

CMD ["/bin/bash", "-c", "set -m; apache2-foreground & for i in {1..10}; do curl http://localhost/install/ -v && s=$$? && break || s=$$?; sleep 1; done; fg %1"]
