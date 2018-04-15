# Parent
FROM php:7.2-apache

# Update packages
RUN apt-get update

# Switch to standard user
RUN useradd -m louis
RUN apt-get install -y sudo
COPY config/sudoers /etc/sudoers.d/sudoers
RUN chmod 0444 /etc/sudoers.d/sudoers
USER louis

# Packages
RUN sudo apt-get install -y wget git zlib1g-dev gnupg2

# PHP configuration
RUN sudo docker-php-ext-install pdo_mysql zip

# Fix Apache warning
RUN sudo echo "ServerName 172.16.238.10" | sudo tee /etc/apache2/conf-available/servername.conf

# Enable HTTPS
COPY tls/apache-selfsigned.key /etc/ssl/private/apache-selfsigned.key
COPY tls/apache-selfsigned.crt /etc/ssl/certs/apache-selfsigned.crt
COPY tls/dhparam.pem /etc/ssl/certs/dhparam.pem
COPY tls/ssl-params.conf /etc/apache2/conf-available/ssl-params.conf
COPY tls/default-ssl.conf /etc/apache2/sites-available/default-ssl.conf
RUN sudo a2enmod rewrite
RUN sudo a2enmod ssl
RUN sudo a2enmod headers
RUN sudo a2ensite default-ssl
RUN sudo a2enconf ssl-params
RUN sudo a2enconf servername

# Install Composer
WORKDIR /usr/local/bin
COPY scripts/install-composer .
COPY scripts/composer .
RUN sudo chmod 755 install-composer composer
RUN sudo ./install-composer
RUN sudo rm install-composer

# Install NPM and Grunt
RUN sudo curl -sL https://deb.nodesource.com/setup_8.x | sudo bash -
RUN sudo apt-get install -y nodejs
RUN sudo npm install -g grunt-cli

# Set working directory
WORKDIR /var/www/html/symfony

# Set CHOWN
RUN sudo chown -cR louis: .

CMD sudo apache2-foreground