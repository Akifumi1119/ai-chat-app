FROM php:8.4-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    npm \
    libsqlite3-dev \
    sqlite3 \
    libonig-dev \
    libxml2-dev \
    zip

RUN docker-php-ext-install \
    pdo \
    pdo_sqlite \
    mbstring \
    xml \
    fileinfo

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN npm install

RUN npm run build

RUN chmod -R 777 storage bootstrap/cache database

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=${PORT:-10000}