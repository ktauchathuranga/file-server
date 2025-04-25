FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    nginx \
    libmagic-dev \
    && docker-php-ext-install pdo_mysql fileinfo \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html
WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p /var/www/html/uploads/files \
    && chown www-data:www-data /var/www/html/uploads \
    && chmod 750 /var/www/html/uploads \
    && chown www-data:www-data /var/www/html/uploads/files \
    && chmod 750 /var/www/html/uploads/files \
    && chown www-data:www-data /var/www/html/logs \
    && chmod 750 /var/www/html/logs

EXPOSE 80

CMD service nginx start && php-fpm