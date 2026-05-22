FROM php:8.3-fpm-alpine

# تثبيت الإضافات المطلوبة للسيستم ولتوليد الـ PDF العربي
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    oniguruma-dev

RUN docker-cache-config \
    docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring gd xml

# ضبط مسار المشروع
WORKDIR /var/www/html
COPY . .

# تثبيت الـ Packages وبناء الـ Assets
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# ضبط الصلاحيات لمجلدات لارافل
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# إعدادات الـ Web Server
COPY ./nginx.conf /etc/nginx/nginx.conf

EXPOSE 80
CMD ["sh", "-c", "php artisan migrate --force && php artisan config:cache && php-fpm -D && nginx -g 'daemon off;'"]
