# From
FROM php:8.4.16-fpm-alpine3.23

LABEL maintainer="manu"

ENV TZ=${TZ:-UTC}
ENV WORKDIR=/var/www

RUN set -eux \
  && apk add --no-cache \
  git vim zip unzip bash curl tzdata icu-libs \
  make ca-certificates gmp gettext libssh2 yaml \
  libintl libxslt libpng libwebp libjpeg-turbo freetype \
  linux-headers oniguruma libxslt libpq vips \
  gmp libzip libxml2 freetds

RUN set -eux \
  && apk add --no-cache --virtual .build_deps \
  libpng-dev libwebp-dev libjpeg-turbo-dev freetype-dev \
  linux-headers oniguruma-dev libxslt-dev postgresql-dev vips-dev \
  libssh2-dev gmp-dev libzip-dev libxml2-dev freetds-dev yaml-dev \
  $PHPIZE_DEPS \
  \
  # Php extensions
  && docker-php-ext-install \
  mysqli \
  pdo_mysql \
  pdo_pgsql \
  pgsql\
  bcmath \
  mbstring \
  xml \
  gd \
  exif \
  zip \
  soap \
  intl \
  xsl \
  pcntl \
  sockets \
  sysvmsg \
  sysvsem \
  sysvshm \
  opcache \
  gmp \
  \
  # Install xdebug
  && pecl install -o -f xdebug \
  && docker-php-ext-enable xdebug \
  \
  # Install YML
  && pecl install -o -f yaml \
  && docker-php-ext-enable yaml \
  \
  # Install redis
  && pecl install -o -f redis \
  && docker-php-ext-enable redis \
  \
  # Install mongodb
  && pecl install -o -f mongodb \
  && docker-php-ext-enable mongodb \
  \
  # Install apcu
  && pecl install -o -f apcu \
  && docker-php-ext-enable apcu \
  \
  # Install ssh2 \
  && pecl install -o -f ssh2 \
  && docker-php-ext-enable ssh2 \
  \
  # Clean php extensions
  && rm -rf /tmp/pear \
  && apk del --no-network .build_deps

# Install composer
COPY --from=composer:2.9.5 /usr/bin/composer /usr/local/bin/composer

# Copy php settings
COPY ./php.ini ${PHP_INI_DIR}/conf.d/99-php.ini
COPY ./entrypoint /usr/local/bin/docker-entrypoint

# Workdir
ENV WORKDIR=/var/www
RUN mkdir -p ${WORKDIR}
WORKDIR ${WORKDIR}

EXPOSE 9000
CMD ["docker-entrypoint"]
