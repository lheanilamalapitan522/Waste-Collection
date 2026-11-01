FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql

RUN printf 'DirectoryIndex index.php\n' > /etc/apache2/mods-enabled/dir.conf

RUN printf 'ServerName localhost\n' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

COPY apache-security.conf /etc/apache2/conf-available/deny-hidden.conf
RUN a2enconf deny-hidden

COPY . /var/www/html/

EXPOSE 80
