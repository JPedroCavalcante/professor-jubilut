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

echo "Aguardando banco de dados..."
until php artisan migrate:status > /dev/null 2>&1; do
    echo "Banco de dados indisponivel. Tentando novamente em 3s..."
    sleep 3
done

echo "Publicando configs do JWT..."
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider" --no-interaction 2>/dev/null || true

if [ -z "$JWT_SECRET" ] || [ "$JWT_SECRET" = "" ]; then
    echo "Gerando JWT secret..."
    php artisan jwt:secret --no-interaction
fi

echo "Executando migrations..."
php artisan migrate --force

echo "Executando seeds..."
php artisan db:seed --force

echo "Iniciando PHP-FPM..."
exec php-fpm