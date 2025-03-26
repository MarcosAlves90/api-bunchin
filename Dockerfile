# Usar imagem PHP com Apache
FROM php:7.4-apache

# Instalar a extensão para conectar ao PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Habilitar o mod_rewrite do Apache
RUN a2enmod rewrite

# Copiar os arquivos da aplicação para o contêiner
COPY . /var/www/html/

# Expor a porta 80 para o Apache
EXPOSE 80

# Iniciar o Apache no primeiro plano
CMD ["apache2-foreground"]
