FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git unzip curl zip libzip-dev libicu-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    nodejs npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip intl gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN npm install && npm run build

RUN php artisan storage:link || true
RUN chmod -R 775 storage bootstrap/cache && chown -R www-data:www-data /var/www

EXPOSE 8000

CMD php artisan config:clear && php artisan view:clear && php artisan cache:clear && php artisan serve --host=0.0.0.0 --port=8000
