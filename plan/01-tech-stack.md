# 01 — Tech Stack Detail: Street Culture Market

## Overview
Street Culture Market adalah e-commerce streetwear berbasis **Laravel 13** dengan tampilan terinspirasi canalize.asia — minimalis, hitam-putih, dan premium.

---

## Backend

### Laravel 13 (PHP 8.5)
- **Versi**: Laravel 13 (framework v13.x)
- **Mengapa**: Full-featured PHP framework dengan MVC, Eloquent ORM, routing, middleware, job queue, event system
- **Fitur yang dipakai**:
  - Eloquent ORM (relasi product–variant–category)
  - Route model binding
  - Middleware (auth, guest, admin)
  - Service container & dependency injection
  - Queue untuk email notifikasi order
  - Storage facade untuk upload gambar

### Artisan Commands
```bash
php artisan make:model Product -mcr   # Model + migration + controller + resource
php artisan make:model Category -mc
php artisan make:model Order -mc
php artisan make:model OrderItem -m
php artisan make:model ProductVariant -m
php artisan make:model Wishlist -m
php artisan make:model Banner -mc
```

---

## Frontend

### Blade Templates
- Engine template bawaan Laravel
- Dipakai untuk semua halaman public + layout
- Component-based dengan `@component`, `x-` prefix
- Tidak perlu build step tambahan (sudah terintegrasi Laravel)

### Tailwind CSS v3
- **File konfigurasi**: `tailwind.config.js`
- **Warna custom**:
  ```js
  colors: {
    'scm-black': '#0a0a0a',
    'scm-white': '#fafafa',
    'scm-gray': { 100: '#f5f5f5', 200: '#e5e5e5', ... }
  }
  ```
- **Font**: Inter (Google Fonts) — sama dengan canalize.asia
- **Dark mode**: class-based

### Alpine.js v3
- Ditambahkan via CDN di `app.blade.php`
- Dipakai untuk:
  - Cart drawer (x-data, x-show, @click)
  - Mobile menu toggle
  - Image gallery switcher
  - Quantity stepper
  - Toast notification
  - Accordion FAQ

### Vite (Build Tool)
- **File**: `vite.config.js` (bawaan Laravel)
- **Entry points**: `resources/css/app.css`, `resources/js/app.js`
- Command dev: `npm run dev`
- Command build: `npm run build`

---

## Database

### SQLite (Development)
- File: `database/database.sqlite`
- Sudah auto-create saat `composer create-project`
- Tidak perlu konfigurasi tambahan

### MySQL (Production)
- Update `.env`:
  ```
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=street_culture_market
  DB_USERNAME=root
  DB_PASSWORD=secret
  ```

---

## Package Composer yang Terinstall

| Package | Versi | Fungsi |
|---------|-------|--------|
| `laravel/breeze` | ^2.4 | Scaffolding auth (login, register, profile) |
| `filament/filament` | ^3.3 | Admin panel CRUD |
| `livewire/livewire` | ^3.8 | Reaktivitas UI untuk Filament |
| `intervention/image` | ^4.3 | Resize & optimasi gambar upload |
| `midtrans/midtrans-php` | ^2.6 | Payment gateway Indonesia |
| `artesaos/seotools` | ^1.4 | Meta tag, OG, Twitter Card |
| `laravel/scout` | ^11.7 | Full-text search abstraction |

---

## NPM Packages yang Terinstall

| Package | Fungsi |
|---------|--------|
| `tailwindcss` | CSS framework |
| `@tailwindcss/forms` | Form styling reset |
| `@tailwindcss/typography` | Prose styling untuk deskripsi produk |
| `autoprefixer` | CSS vendor prefix otomatis |
| `vite` | Build tool |
| `laravel-vite-plugin` | Integrasi Vite ↔ Laravel |

---

## Auth System (Breeze)
- **Routes**: `/login`, `/register`, `/forgot-password`, `/profile`
- **Middleware**: `auth` untuk halaman protected
- **Guard**: Default Laravel auth guard
- **Admin guard**: Filament punya guard sendiri

---

## Struktur Folder Utama

```
street-culture-market/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php
│   │   │   ├── ProductController.php
│   │   │   ├── CartController.php
│   │   │   ├── CheckoutController.php
│   │   │   ├── OrderController.php
│   │   │   └── WishlistController.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── ProductVariant.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── Wishlist.php
│   │   └── Banner.php
│   ├── Filament/
│   │   └── Resources/  ← Admin panel resources
│   └── Providers/
│       └── Filament/
│           └── AdminPanelProvider.php
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   └── app.blade.php        ← Layout utama
│   │   ├── components/
│   │   │   ├── navbar.blade.php
│   │   │   ├── footer.blade.php
│   │   │   ├── cart-drawer.blade.php
│   │   │   ├── product-card.blade.php
│   │   │   └── toast.blade.php
│   │   ├── home/
│   │   │   └── index.blade.php
│   │   ├── products/
│   │   │   ├── index.blade.php
│   │   │   └── show.blade.php
│   │   ├── cart/
│   │   │   └── index.blade.php
│   │   ├── checkout/
│   │   │   └── index.blade.php
│   │   └── orders/
│   │       ├── index.blade.php
│   │       └── show.blade.php
│   ├── css/
│   │   └── app.css
│   └── js/
│       └── app.js
├── database/
│   ├── migrations/
│   └── seeders/
├── plan/                ← Folder dokumentasi ini
├── .env
├── tailwind.config.js
└── vite.config.js
```
