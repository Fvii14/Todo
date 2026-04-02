FROM php:8.3-fpm-bullseye AS build

RUN apt-get update && apt-get install -y --no-install-recommends \
    bash \
    curl \
    git \
    unzip \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zlib1g-dev \
    gnupg2 \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        intl \
        pdo \
        pdo_mysql \
        zip \
        gd \
        bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN rm -rf node_modules package-lock.json \
    && npm install \
    && npm run build

RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

FROM php:8.3-cli-bullseye

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng16-16 \
    libjpeg62-turbo \
    libfreetype6 \
    libzip4 \
    libicu67 \
    libonig5 \
    libxml2 \
    bash \
    curl \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=build /usr/local /usr/local
COPY --from=build /var/www /var/www

WORKDIR /var/www

RUN php artisan config:clear \
    && rm -f bootstrap/cache/services.php \
    && rm -f bootstrap/cache/packages.php

EXPOSE 8080

RUN mkdir -p /cloudsql && chown www-data:www-data /cloudsql

# Aumenta el límite de subida de archivos y memoria
RUN echo "upload_max_filesize=100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=120M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time=300" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_input_time=300" >> /usr/local/etc/php/conf.d/uploads.ini

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]