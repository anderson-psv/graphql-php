FROM php:7.4-apache

RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libzip-dev \
    unzip

RUN docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    zip \
    && mkdir -p /var/www/graphql

COPY --from=composer /usr/bin/composer /usr/bin/composer
#COPY ./hcode_ecommerce.com.conf /etc/apache2/sites-available/hcode_ecommerce.com.conf

#RUN a2enmod rewrite
#RUN a2dissite 000-default.conf && a2ensite hcode_ecommerce.com.conf && /etc/init.d/apache2 restart