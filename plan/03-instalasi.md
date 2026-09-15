# 03 — Panduan Instalasi Lengkap

> Semua langkah ini SUDAH dijalankan secara otomatis. Dokumen ini sebagai referensi jika perlu install ulang atau setup di mesin baru.

---

## Prasyarat
- PHP >= 8.2 (sudah: PHP 8.5)
- Composer >= 2.0 (sudah: 2.8.12)
- Node.js >= 18 (sudah: v24.19.0)
- NPM >= 9 (sudah: 11.17.0)

---

## Step 1: Buat Proyek Laravel

```bash
composer create-project laravel/laravel . --prefer-dist
```

Ini otomatis akan:
- Install Laravel 13
- Generate APP_KEY
- Buat SQLite database
- Jalankan migrasi awal (users, cache, jobs table)

---

## Step 2: Install Package Composer

```bash
# Auth scaffolding
composer require laravel/breeze

# Image processing
composer require intervention/image

# Payment gateway Indonesia
composer require midtrans/midtrans-php

# SEO meta tags
composer require artesaos/seotools

# Full-text search
composer require laravel/scout

# Admin panel (gunakan -W karena butuh downgrade symfony/console)
composer require filament/filament:"^3.3" -W
```

---

## Step 3: Install Breeze (Auth + Tailwind)

```bash
php artisan breeze:install blade
# pilih: blade, dark mode (no), testing (PHPUnit)
```

Ini akan:
- Buat views: login, register, dashboard, profile
- Install & konfigurasi Tailwind CSS
- Install Alpine.js
- Setup Vite
- Auto-run `npm install && npm run build`

---

## Step 4: Install Filament Admin Panel

```bash
php artisan filament:install --panels
# ID panel: admin
```

Ini akan membuat:
- `app/Providers/Filament/AdminPanelProvider.php`
- Asset CSS/JS di `public/`

---

## Step 5: Publish Config Files

```bash
# SEO Tools config
php artisan vendor:publish --provider="Artesaos\SEOTools\Providers\SEOToolsServiceProvider"

# Scout config
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"

# Intervention Image config
php artisan vendor:publish --provider="Intervention\Image\Providers\LaravelServiceProvider"
```

---

## Step 6: Setup Environment (.env)

```env
APP_NAME="Street Culture Market"
APP_ENV=local
APP_KEY=base64:...  # sudah digenerate
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (SQLite untuk dev)
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_DATABASE=street_culture_market

# Midtrans
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxx
MIDTRANS_IS_PRODUCTION=false

# Mail (opsional, untuk notifikasi order)
MAIL_MAILER=log
```

---

## Step 7: Jalankan Migrasi & Seeder

```bash
php artisan migrate
php artisan db:seed
```

---

## Step 8: Buat Admin User (Filament)

```bash
php artisan make:filament-user
# Name: Admin
# Email: admin@streetculturemarket.com
# Password: password
```

---

## Step 9: Build Assets

```bash
# Development (dengan hot reload)
npm run dev

# Production
npm run build
```

---

## Step 10: Jalankan Server

```bash
php artisan serve
# Akses: http://localhost:8000
# Admin: http://localhost:8000/admin
```

---

## Troubleshooting

### Error: "No application encryption key has been specified"
```bash
php artisan key:generate
```

### Error: "Class X not found"
```bash
composer dump-autoload
```

### Error: Tailwind tidak ter-compile
```bash
npm install
npm run build
```

### Reset database
```bash
php artisan migrate:fresh --seed
```

### Clear semua cache
```bash
php artisan optimize:clear
```
