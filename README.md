# Unick System

## Backend (Laravel)

Requirements: PHP 8.2+, Composer, Node 20+

Setup:

- cd `unick-backend`
- composer install
- cp .env.example .env (if exists) and set `DB_CONNECTION=sqlite` (or your RDBMS). For sqlite: `touch database/database.sqlite`.
- php artisan key:generate
- php artisan migrate
- php artisan serve (serves on http://127.0.0.1:8000)

## Frontend (React)

Requirements: Node 20+

Setup:

- cd `unick-frontend`
- npm install
- Create `.env` if needed with `REACT_APP_API_URL=http://localhost:8000/api`
- npm start (serves on http://localhost:3000)

## Notes

- Login/register endpoints: `/api/login`, `/api/register`
- Authenticated APIs require Bearer token via Laravel Sanctum
- Example roles: `admin`, `staff`, `customer`
