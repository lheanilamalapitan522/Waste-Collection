FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql

RUN printf 'DirectoryIndex index.php\n' > /etc/apache2/mods-enabled/dir.conf

COPY . /var/www/html/

EXPOSE 80
