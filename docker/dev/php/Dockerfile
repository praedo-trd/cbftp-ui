# ./docker/php/Dockerfile
FROM php:7.3-fpm

RUN apt-get update && \
apt-get install -y \
        libzip-dev \
        zip git \
        libicu-dev \
  && docker-php-ext-install zip

RUN docker-php-ext-install pdo_mysql ctype intl iconv
RUN pecl install apcu

RUN docker-php-ext-configure zip --with-libzip
RUN docker-php-ext-install zip
RUN docker-php-ext-enable apcu

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === 'e0012edf3e80b6978849f5eff0d4b4e4c79ff1609dd1e613307e16318854d24ae64f26d17af3ef0bf7cfb710ca74755a') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && mv composer /usr/local/bin/composer

WORKDIR /usr/src/trd
COPY composer.json /usr/src/trd/
COPY composer.lock /usr/src/trd/

RUN PATH=$PATH:/usr/src/app/vendor/bin:bin

RUN composer install
