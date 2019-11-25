FROM php:7.2-fpm

# Copy phpfpm config
COPY docker/php-fpm/app.ini /usr/local/etc/php/conf.d/

# Install dependencies
RUN curl -sL https://deb.nodesource.com/setup_8.x | bash -
RUN apt-get update && apt-get install -y \
    git \
    nodejs \
    npm \
    zip \
    chromium \
    libpng-dev \
    && docker-php-ext-install pdo_mysql exif gd \
    ## APCu
    && pecl install apcu \
    && pecl install apcu_bc-1.0.3 \
    && docker-php-ext-enable apcu --ini-name 10-docker-php-ext-apcu.ini \
    && docker-php-ext-enable apc --ini-name 20-docker-php-ext-apc.ini

# Composer
RUN curl -sL https://getcomposer.org/installer | php -- --install-dir /usr/bin --filename composer

# Npm (this is a temporarily solution and should be fixed '--force --unsafe-perm')
RUN npm cache clean --force --unsafe-perm
RUN mkdir /.npm
RUN chmod -R a+w /.npm

# Cleanup
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

WORKDIR /var/www

# Copy keys and config
COPY ci/travis/files/ /etc/openconext
