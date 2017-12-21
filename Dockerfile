FROM php:7.2-apache
COPY config/php.ini /usr/local/etc/php/
COPY symfony/ /var/www/html/
RUN a2enmod rewrite
RUN service apache2 restart
RUN cd /var/www/html/
RUN chmod 777 -R .
RUN apt-get update
RUN apt-get install -y git
RUN apt-get install -y php-zip

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"