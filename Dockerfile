FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    git \
    pkg-config \
    libbrotli-dev \
    && docker-php-ext-install zip \
    && pecl install swoole \
    && docker-php-ext-enable swoole \
    && pecl install pcov \
    && docker-php-ext-enable pcov

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . /app

CMD ["php", "-a"]
