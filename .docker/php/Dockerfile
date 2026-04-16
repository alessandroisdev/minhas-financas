FROM php:8.3-fpm

# Instalar dependências de sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev

# Limpar cache do apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensões do PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Obter Composer mais recente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
