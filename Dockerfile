FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev zip nodejs npm \
    && docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN npm install && npm run build

RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

EXPOSE 8080

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8080
