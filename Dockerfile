FROM php:7.2-fpm

# Copy composer.lock and composer.json from local dir to docker /var/www/ dir
COPY composer.lock composer.json /var/www/

WORKDIR "/var/www"

# Install dependencies
RUN apt-get update && apt-get install -y \
    nano \
    libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pgsql \
    # Clear cache
    && apt-get clean && rm -rf /var/lib/apt/lists/*

