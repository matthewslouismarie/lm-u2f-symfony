# Parent
FROM php:7.2-apache

# Packages
RUN apt-get update
RUN apt-get install -y wget git zlib1g-dev gnupg2

# PHP configuration
RUN docker-php-ext-install pdo_mysql zip

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

# Install Composer
WORKDIR /usr/local/bin
COPY scripts/install-composer .
COPY scripts/composer .
RUN chmod 755 install-composer composer
RUN ./install-composer
RUN rm install-composer

# Install NPM and Grunt
RUN curl -sL https://deb.nodesource.com/setup_8.x | bash -
RUN apt-get install -y nodejs
RUN npm install -g grunt-cli

# Set working directory
WORKDIR /var/www/html
