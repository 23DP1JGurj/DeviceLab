# DeviceLab

DeviceLab ir Laravel 12 + Vue 3 projekts servisa centra pieteikumu pārvaldībai. Klienti var reģistrēties, pievienot savas ierīces un izveidot remonta pieteikumus, bet darbinieki pārvalda visus pasūtījumus atsevišķā panelī.

## Auth un lomas

Projektā tiek izmantota Laravel session autentifikācija SPA stilā:
- `client` var redzēt tikai savus pasūtījumus un savas ierīces
- `staff` var redzēt un apstrādāt visus pasūtījumus
- `admin` var redzēt un apstrādāt visus pasūtījumus

Frontend pieprasījumi uz auth/API izmanto `credentials: 'same-origin'`.

## Testa konti

- `admin@devicelab.local` / `password`
- `staff@devicelab.local` / `password`
- `client@devicelab.local` / `password`

## Palaišana

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
npm run dev
```

## Galvenie auth endpointi

- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/auth/me`
- `PATCH /api/auth/profile`

## Pasūtījumu endpointi

- `GET /api/client/orders`
- `POST /api/client/orders`
- `GET /api/staff/orders`
- `PATCH /api/staff/orders/{id}`
- `DELETE /api/staff/orders/{id}`

## Ierīču endpointi

- `GET /api/my/devices`
- `POST /api/my/devices`
- `DELETE /api/my/devices/{id}`
