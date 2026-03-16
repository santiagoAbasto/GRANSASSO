# GranSasso

Sitio web corporativo para Gran Sasso Engranajes.

## Stack tecnológico

- Laravel 12
- React + Inertia
- Tailwind CSS v4
- Blade (web pública)
- MySQL
- Laravel Breeze (auth)
- Multilenguaje (ES / EN / PT)

## Arquitectura

Web pública:
- Blade

Panel de administración:
- React + Inertia
- Ruta: /admin

## Instalación

Clonar el proyecto:

git clone https://github.com/santiagoAbasto/GRANSASSO.git

Entrar al proyecto:

cd GRANSASSO

Instalar dependencias:

composer install
npm install

Copiar variables de entorno:

cp .env.example .env

Generar key:

php artisan key:generate

Migraciones:

php artisan migrate

Ejecutar:

php artisan serve
npm run dev