#!/bin/sh

if [ ! -f "package.json" ]; then
    echo "Vue não encontrado. Criando projeto com Vite em pasta temporária..."
    npm create vite@latest temp_install -- --template vue
    echo "Movendo arquivos para a raiz..."
    cp -r temp_install/. .
    rm -rf temp_install
    echo "Instalando dependências..."
    npm install
else
    echo "Vue já instalado. Verificando dependências..."
    npm install
fi

echo "Iniciando servidor de desenvolvimento..."
exec npm run dev -- --host