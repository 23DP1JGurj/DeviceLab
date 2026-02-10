# DeviceLab

DeviceLab ir mūsdienīga tīmekļa platforma servisa centram, kas specializējas tehnikas remontā.  
Sistēma nodrošina ērtu pieteikumu noformēšanu, pasūtījumu pārvaldību un strukturētu darbu ar klientu pieprasījumiem.

---

## 📋 Projekta apraksts

DeviceLab ir tīmekļa risinājums, kas paredzēts tehnikas remonta servisa darba organizēšanai.  
Platforma ļauj klientiem ātri iesniegt pieteikumu remontam, bet servisa pusei — pārskatīt un apstrādāt pasūtījumus.

Sistēmas galvenais uzsvars ir uz:
- vienkāršu pieteikuma izveidi;
- saprotamu pasūtījumu struktūru;
- skaidru saskarni gan klientam, gan servisam.

---

## 🔧 Galvenās iespējas

### Klientam:
- 📝 Pieteikuma noformēšana tehnikas remontam  
- 📱 Ierīces tipa un filiāles izvēle  
- 🗒️ Problēmas apraksta iesniegšana  
- 🌐 Piekļuve sistēmai caur tīmekļa pārlūku  

### Servisa pusei:
- 📋 Pasūtījumu saraksta apskate  
- 🔍 Meklēšana un filtrēšana pēc parametriem  
- 🗄️ Pasūtījumu datu glabāšana datubāzē  
- 🔄 Pamatstruktūra turpmākai funkcionalitātes paplašināšanai  

---

## 🏗️ Projekta struktūra

DeviceLab/
├── backend/   # Laravel REST API
├── frontend/  # Vue lietotāja saskarne
└── README.md  # Projekta apraksts

---

## ⚙️ Backend

- **Framework:** Laravel  
- **Valoda:** PHP 8.2+  
- **Datubāze:** MySQL  
- **API tips:** REST  

### Galvenās mapes:
- `app/Http/Controllers/` — API kontrolieri  
- `app/Models/` — datu modeļi  
- `database/migrations/` — datubāzes migrācijas  
- `routes/api.php` — API maršrutēšana  

---

## 🎨 Frontend

- **Framework:** Vue 3  
- **Build rīks:** Vite  
- **Stili:** CSS  
- **Valoda:** JavaScript  

### Galvenās mapes:
- `src/components/` — Vue komponentes  
- `src/pages/` — lapas  
- `src/router/` — maršrutēšana  
- `src/assets/` — statiskie resursi  

---

## 🚀 Palaišana

### Priekšnoteikumi
- PHP 8.2 vai jaunāks  
- Node.js 18+  
- Composer  
- MySQL datubāze  

---

### Backend palaišana

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
