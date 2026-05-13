# DeviceLab

**DeviceLab** ir tīmekļa sistēma elektrotehnikas servisa centra darba organizēšanai.

Sistēma paredzēta klientu remonta pieteikumu izveidei, ierīču uzskaitei, pasūtījumu apstrādei, demonstrācijas apmaksai, atsauksmju pievienošanai un administratora pārvaldībai.

Tiešsaistes versija:  
https://devicelab-production.up.railway.app/

---

## Izmantotās tehnoloģijas

Projektā izmantotas šādas tehnoloģijas:

- Laravel 12
- Vue 3
- Vite
- MySQL
- Railway
- Laravel Storage
- Gmail SMTP e-pasta paziņojumiem

---

## Projekta struktūra

```text
DeviceLab/
├─ app/                 Laravel modeļi, servisi un kontrolieri
├─ database/            migrācijas un seeders
├─ public/              publiskie faili, logo un favicon
├─ resources/
│  ├─ js/               Vue komponentes un lapas
│  └─ views/            Blade skati un e-pasta šabloni
├─ routes/              Laravel maršruti
├─ storage/             logi un augšupielādētie faili
├─ .env.example         vides konfigurācijas piemērs
├─ composer.json
├─ package.json
└─ README.md
```

---

## Lokālā palaišana

### Prasības

Lai projektu palaistu lokāli, nepieciešams:

- PHP 8.2+
- Composer
- Node.js un npm
- MySQL, piemēram, XAMPP
- Git

---

### 1. Projekta lejupielāde

```bash
git clone <repozitorija-saite>
cd DeviceLab
```

Ja projekts jau ir datorā:

```bash
git pull
```

---

### 2. Atkarību uzstādīšana

```bash
composer install
npm install
```

---

### 3. `.env` faila sagatavošana

```bash
copy .env.example .env
php artisan key:generate
```

Linux/macOS gadījumā:

```bash
cp .env.example .env
php artisan key:generate
```

---

### 4. Datu bāzes sagatavošana

Izveido MySQL datu bāzi, piemēram:

```sql
CREATE DATABASE devicelab_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

`.env` failā norādi savus datu bāzes pieslēguma parametrus:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=devicelab_db
DB_USERNAME=root
DB_PASSWORD=
```

Pēc tam palaid migrācijas:

```bash
php artisan migrate
```

Ja nepieciešami demonstrācijas dati:

```bash
php artisan db:seed --class=PresentationDemoSeeder
```

---

### 5. Storage saites izveide

Lai darbotos pasūtījumu fotoattēlu augšupielāde:

```bash
php artisan storage:link
```

---

### 6. Projekta palaišana

Laravel serveris:

```bash
php artisan serve
```

Frontend izstrādes serveris:

```bash
npm run dev
```

Lokāli projekts būs pieejams:

```text
http://127.0.0.1:8000
```

---

## Noderīgas komandas

### Laravel

```bash
php artisan serve
php artisan migrate
php artisan db:seed --class=PresentationDemoSeeder
php artisan storage:link
php artisan route:list
php artisan config:clear
php artisan cache:clear
```

### Frontend

```bash
npm run dev
npm run build
```

---


## Drošības piezīmes

Projektā tiek izmantoti vairāki drošības mehānismi:

- paroles tiek glabātas hashētā veidā;
- piekļuve sistēmas sadaļām tiek ierobežota pēc lietotāja lomas;
- klients var skatīt tikai savus pasūtījumus un ierīces;
- servisa darbinieks var apstrādāt pasūtījumus savas lomas ietvaros;
- administrators var pārvaldīt lietotājus, pasūtījumus un atsauksmes;
- formās tiek izmantota datu validācija;
- īstais `.env` fails netiek glabāts GitHub repozitorijā.

---

## Biežākās problēmas

### Nevar pieslēgties datu bāzei

Pārbaudi:

- vai MySQL serveris ir palaists;
- vai datu bāze `devicelab_db` eksistē;
- vai `.env` failā ir pareizi `DB_*` parametri;
- vai pēc `.env` izmaiņām palaista komanda:

```bash
php artisan config:clear
```

---

### Nestrādā attēlu augšupielāde

Pārbaudi, vai ir izveidota storage saite:

```bash
php artisan storage:link
```

---

### E-pasti netiek nosūtīti

Pārbaudi:

- vai `.env` failā ir pareizi `MAIL_*` parametri;
- vai izmantots derīgs SMTP pieslēgums;
- vai nav saglabāts vecs konfigurācijas kešs:

```bash
php artisan config:clear
php artisan cache:clear
```

---

### Frontend izmaiņas nav redzamas

Izstrādes laikā pārbaudi, vai darbojas:

```bash
npm run dev
```

Produkcijas versijai jāpalaiž:

```bash
npm run build
```

---

## Nākotnes uzlabojumi

Nākotnē sistēmu var papildināt ar:

- pilnvērtīgu detaļu noliktavas pārvaldību;
- detalizētāku cenu aprēķinu pēc ierīces modeļa;
- reālu bankas maksājumu integrāciju;
- automātisku rēķinu ģenerēšanu;
- SMS paziņojumiem;
- finanšu un servisa darba analītiku;
- plašāku darbinieku noslodzes pārskatu.