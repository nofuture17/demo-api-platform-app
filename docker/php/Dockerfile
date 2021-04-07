ARG VERSION
FROM php:${VERSION}-fpm-alpine

RUN set -ex \
  	&& apk add --update --no-cache autoconf g++ git wget make \
    && apk add --no-cache tzdata acl file gettext bash curl \
    && apk add --no-cache icu-dev libzip-dev mc postgresql-dev imagemagick-dev imagemagick oniguruma-dev jpeg-dev zlib-dev \
    && apk add --no-cache curl openssh-client freetype-dev libpng-dev libjpeg-turbo-dev libxml2-dev libressl-dev
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-configure zip \
    && docker-php-ext-install -j$(nproc) intl pdo pdo_pgsql mbstring sockets opcache zip xml simplexml gd bcmath calendar \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug opcache \
    && apk del autoconf g++

COPY php/conf.d/php.ini /usr/local/etc/php/conf.d/10-php.ini
COPY php/conf.d/php-dev.ini /usr/local/etc/php/conf.d/20-php-dev.ini
COPY php/conf.d/php-fpm.ini /usr/local/etc/php/conf.d/30-php-fpm.ini
COPY php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
RUN wget "https://getcomposer.org/composer-2.phar" -O /usr/local/bin/composer && chmod +x /usr/local/bin/composer

COPY php/start.sh /usr/local/bin/php-start
WORKDIR /srv/app/
RUN chmod +x /usr/local/bin/php-start
CMD ["php-start"]