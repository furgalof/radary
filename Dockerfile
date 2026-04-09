FROM php:8.2-apache

RUN a2enmod rewrite


RUN mkdir -p /tmp/apache2 \
 && chown -R www-data:www-data /tmp/apache2

ENV APACHE_PID_FILE=/tmp/apache2/apache.pid
ENV APACHE_RUN_DIR=/tmp/apache2
ENV APACHE_LOCK_DIR=/tmp/apache2

COPY public/ /var/www/html/
