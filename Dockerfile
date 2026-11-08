FROM dunglas/frankenphp:1.13.2-php8.5.12

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    zip \
    intl \
    bcmath \
    opcache \
    redis \
    gd

# Naikkan max execution time untuk Filament
RUN echo "max_execution_time=120" > /usr/local/etc/php/conf.d/custom.ini

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
