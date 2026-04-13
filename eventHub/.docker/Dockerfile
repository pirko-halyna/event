FROM php:8.3-fpm-alpine

RUN \
  apk add --no-cache linux-headers icu libssh2 libpng git zip libzip-dev libgomp supervisor && \
  docker-php-ext-install bcmath pdo_mysql sockets opcache pcntl zip exif && \
  apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS libssh2-dev icu-dev libpng-dev libtool openssl-dev && \
  docker-php-ext-install intl gd && \
  /usr/local/bin/pecl install apcu ssh2-1.4 redis && \
  docker-php-ext-enable apcu ssh2 intl gd redis && \
  apk del .phpize-deps && \
  { \
    echo '[PHP]'; \
    echo 'expose_php=0'; \
    echo 'memory_limit=-1'; \
    echo 'post_max_size=25M'; \
    echo 'upload_max_filesize=25M'; \
    echo 'max_execution_time=300'; \
  } | tee /usr/local/etc/php/php.ini && \
  mkdir -p /usr/local/etc/php-fpm.d && \
  { \
    echo '[global]'; \
    echo 'daemonize = no'; \
    echo '[www]'; \
    echo "listen = [::]:9000"; \
  } | tee /usr/local/etc/php-fpm.d/zz-docker.conf

# Install composer
RUN \
  EXPECTED_CHECKSUM="$(wget -q -O - https://composer.github.io/installer.sig)" && \
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"  && \
  ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")" && \
  if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; then echo 'ERROR: Invalid installer checksum' && exit 1; fi &&  \
  php composer-setup.php --install-dir=/usr/local/bin --filename=composer --version=2.6.5 && \
  php -r "unlink('composer-setup.php');"

ADD .. /srv/events-api
WORKDIR /srv/events-api

RUN mkdir -p /srv/events-api/bootstrap/cache /srv/events-api/storage/framework \
    && chmod -R 777 /srv/events-api/bootstrap/cache \
    && chmod -R 777 /srv/events-api/storage

COPY .docker/supervisord.conf /etc/supervisord.conf
RUN chmod 0644 /etc/supervisord.conf

COPY .docker/cronjobs /etc/cron.d/cronjobs
RUN chmod 0644 /etc/cron.d/cronjobs && crontab /etc/cron.d/cronjobs

COPY .docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod 0644 /usr/local/bin/docker-entrypoint.sh

RUN composer install --no-interaction --no-progress --no-suggest --optimize-autoloader

ENTRYPOINT ["sh", "/usr/local/bin/docker-entrypoint.sh"]
