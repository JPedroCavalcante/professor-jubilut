#!/bin/sh

if [ ! -f "package.json" ]; then
    echo "🚀 Vue não encontrado. Criando projeto com Vite..."
    npm create vite@latest . -- --template vue
    echo "📦 Instalando dependências..."
    npm install

else
    echo "✅ Vue já instalado. Verificando dependências..."
    npm install
fi

echo "🏁 Iniciando servidor de desenvolvimento..."
exec npm run dev -- --host