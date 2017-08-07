FROM php:5.6-apache

# Enable Apache Rewrite Module
RUN a2enmod rewrite

RUN apt-get update && apt-get install -y --force-yes --no-install-recommends \
	wget \
	libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng12-dev \
	&& rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
	&& docker-php-ext-install -j$(nproc) gd

RUN apt-get update && apt-get install -y libmemcached-dev zlib1g-dev \
    && pecl install memcached-2.2.0 \
    && docker-php-ext-enable memcached

RUN echo 'date.timezone="Europe/Berlin"' > /usr/local/etc/php/conf.d/php-timezone.ini

VOLUME /var/www/html

CMD ["apache2-foreground"]
