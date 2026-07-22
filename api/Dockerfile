FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libzip-dev \
    && docker-php-ext-install pdo_mysql sockets zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN chmod +x docker/entrypoint.sh docker/worker.sh

EXPOSE 8000

ENTRYPOINT ["sh", "docker/entrypoint.sh"]
