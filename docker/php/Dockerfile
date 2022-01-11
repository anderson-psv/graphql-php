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
    && mkdir -p /var/www/graphql \
    && chmod -R 775 /var/www/graphql

#Realiza a copia do conf que define a pasta root do apache como /var/www/graphql
COPY ./conf /etc/apache2/sites-available

#Realiza a copia do composer, e conforme configurado no container do mesmo, executa no repositorio
COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite
#RUN a2dissite 000-default.conf && a2ensite hcode_ecommerce.com.conf && /etc/init.d/apache2 restart
EXPOSE 80