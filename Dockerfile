FROM php:8.4-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP requeridas por Symfony
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar composer.json y composer.lock primero (aprovecha el cache de Docker)
COPY composer.json composer.lock ./

# Instalar dependencias PHP (sin scripts para no fallar sin el código completo)
RUN composer install --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs

# Copiar el resto del proyecto
COPY . .

# Generar el autoloader optimizado
RUN composer dump-autoload --optimize

# Crear directorios necesarios y dar permisos
RUN mkdir -p var/cache var/log public/uploads/productos \
    && chown -R www-data:www-data var public/uploads \
    && chmod -R 775 var public/uploads

# Configurar OPcache para producción
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=1" >> /usr/local/etc/php/conf.d/opcache.ini

# Aumentar límites de PHP
RUN echo "upload_max_filesize=20M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=20M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit=256M" >> /usr/local/etc/php/conf.d/uploads.ini

EXPOSE 9000

CMD ["php-fpm"]
