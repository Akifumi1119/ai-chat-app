FROM php:8.3-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libsqlite3-dev \
    sqlite3 \
    npm

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install

RUN npm install

RUN npm run build

RUN touch database/database.sqlite

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000