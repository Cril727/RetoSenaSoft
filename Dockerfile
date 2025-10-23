#Dockerfile sin .
# Imagen base con PHP 8 y extensiones necesarias
FROM php:8.2-cli

# Instala dependencias del sistema
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev zip \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Define directorio de trabajo
WORKDIR /app

# Copia archivos del proyecto
COPY . .

# Instala dependencias de Laravel (sin dev)
RUN composer install --no-dev --optimize-autoloader

# Expone el puerto 10000
EXPOSE 10000

# Comando de inicio
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]

