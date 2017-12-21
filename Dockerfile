FROM php:7.2-apache
COPY config/php.ini /usr/local/etc/php/
RUN a2enmod rewrite
RUN service apache2 restart
#todo /etc/apache2/apache2.conf
#todo run composer not as root!