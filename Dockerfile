FROM php:8.3-fpm

# Install system dependencies (FFmpeg, libvips, supervisor, cron...)
RUN apt-get update && apt-get install -y \
    git curl zip unzip cron supervisor ffmpeg \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    libvips-dev libheif-dev libicu-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Enable pcntl functions (Mixpost yêu cầu pcntl_signal, pcntl_alarm)
RUN echo "disable_functions =" > /usr/local/etc/php/conf.d/enable-pcntl.ini

# Enable FFI cho libvips (HEIC/HEIF support)
RUN echo "ffi.enable = true" >> /usr/local/etc/php/conf.d/enable-pcntl.ini && \
    echo "zend.max_allowed_stack_size = -1" >> /usr/local/etc/php/conf.d/enable-pcntl.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --optimize-autoloader --no-dev

RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Copy supervisor config
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]