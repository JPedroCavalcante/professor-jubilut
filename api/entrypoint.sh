#!/bin/bash

if [ ! -f "artisan" ]; then
    echo "Laravel não encontrado. Instalando Laravel 5.8..."
    
    composer create-project --prefer-dist laravel/laravel /tmp/laravel_install "5.8.*"
    
    echo "Movendo arquivos para a raiz..."
    cp -a /tmp/laravel_install/. .
    
    rm -rf /tmp/laravel_install
    
    echo "Configurando .env..."
    if [ ! -f ".env" ]; then
        cp .env.example .env
    fi

    sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=pgsql/g' .env
    sed -i 's/DB_HOST=127.0.0.1/DB_HOST=db/g' .env
    sed -i 's/DB_PORT=3306/DB_PORT=5432/g' .env
    sed -i 's/DB_DATABASE=laravel/DB_DATABASE=laravel/g' .env
    sed -i 's/DB_USERNAME=root/DB_USERNAME=laravel/g' .env
    sed -i 's/DB_PASSWORD=/DB_PASSWORD=laravel/g' .env
    
else
    echo "Laravel já instalado. Instalando dependências..."
    composer install
fi

chmod -R 777 storage bootstrap/cache

echo "Iniciando PHP-FPM..."
exec php-fpm