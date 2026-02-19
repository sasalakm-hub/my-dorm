FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    sqlite3 \
    && docker-php-ext-install pdo pdo_sqlite \
    && apt-get clean

COPY . /var/www/html/

EXPOSE 80
