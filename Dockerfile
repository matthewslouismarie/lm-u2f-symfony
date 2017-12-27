# Parent
FROM php:7.2-apache

# Packages
RUN apt-get update
RUN apt install -y wget git

# PHP configuration
RUN docker-php-ext-install pdo pdo_mysql

# Enable HTTPS
COPY tls/apache-selfsigned.key /etc/ssl/private/apache-selfsigned.key
COPY tls/apache-selfsigned.crt /etc/ssl/certs/apache-selfsigned.crt
COPY tls/dhparam.pem /etc/ssl/certs/dhparam.pem
COPY tls/ssl-params.conf /etc/apache2/conf-available/ssl-params.conf
COPY tls/default-ssl.conf /etc/apache2/sites-available/default-ssl.conf
RUN a2enmod rewrite
RUN a2enmod ssl
RUN a2enmod headers
RUN a2ensite default-ssl
RUN a2enconf ssl-params
RUN apache2ctl configtest

# Install Composer and the dependencies
WORKDIR /usr/local/bin
COPY install-composer.sh .
RUN chmod 755 install-composer.sh
RUN ./install-composer.sh

# Set working directory
WORKDIR /var/www/html