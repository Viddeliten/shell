FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        libcurl4-openssl-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" curl gd gettext mbstring mysqli \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY docker-entrypoint.sh /usr/local/bin/storybook-entrypoint
RUN chmod +x /usr/local/bin/storybook-entrypoint

WORKDIR /var/www/html
ENTRYPOINT ["storybook-entrypoint"]
CMD ["apache2-foreground"]