FROM mcr.microsoft.com/devcontainers/php:1-8.2-bullseye

# Install dependencies
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install -y \
        libpng-dev \
        mariadb-client \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions driver
RUN docker-php-ext-install gd mysqli pdo pdo_mysql
