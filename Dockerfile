FROM php:8.4-fpm-alpine

RUN apk add --no-cache nginx curl libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev zip unzip nodejs npm supervisor postgresql-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install pdo pdo_pgsql pgsql gd zip opcache bcmath pcntl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --optimize-autoloader --no-dev --no-interaction
RUN npm ci && npm run build && rm -rf node_modules
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 storage bootstrap/cache

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

EXPOSE 8080
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]