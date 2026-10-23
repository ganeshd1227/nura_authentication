FROM php:8.2-apache

# System deps + the MongoDB PHP extension (not available on shared hosts).
RUN apt-get update && apt-get install -y libssl-dev unzip git \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && docker-php-ext-install mysqli \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Never serve secrets / source-only files over HTTP.
RUN printf '<FilesMatch "(^\\.|\\.(sql|json|lock|md|example)$|^Dockerfile$)">\n    Require all denied\n</FilesMatch>\n<DirectoryMatch "/(vendor|sql)/">\n    Require all denied\n</DirectoryMatch>\n' > /etc/apache2/conf-available/protect.conf \
    && a2enconf protect

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . /var/www/html
RUN composer install --no-dev --optimize-autoloader \
    && chown -R www-data:www-data /var/www/html

# Hosts like Render tell the app which port to listen on via $PORT.
ENV PORT=80
EXPOSE 80
CMD ["sh", "-c", "sed -i \"s/Listen 80/Listen ${PORT}/\" /etc/apache2/ports.conf && sed -i \"s/:80>/:${PORT}>/\" /etc/apache2/sites-available/000-default.conf && apache2-foreground"]
