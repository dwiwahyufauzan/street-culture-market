# Pengembangan Sistem Informasi E-Commerce Menggunakan Pendekatan Upselling pada Brand Street Culture Market

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.5-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Tailwind_CSS-v3-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind">
  <img src="https://img.shields.io/badge/Filament-v3-F59E0B?style=for-the-badge" alt="Filament">
  <img src="https://img.shields.io/badge/Metodologi-RUP-4F46E5?style=for-the-badge" alt="RUP">
</p>

---

## 📖 Latar Belakang

Perkembangan teknologi informasi telah memberikan perubahan dalam cara pelaku usaha melakukan kegiatan penjualan dan memberikan pelayanan kepada pelanggan. Salah satu penerapannya adalah melalui sistem informasi e-commerce yang memungkinkan proses pemasaran dan transaksi dilakukan secara digital. Dalam e-commerce, selain menyediakan informasi produk dan memudahkan proses pembelian, sistem juga dapat dikembangkan dengan fitur rekomendasi produk untuk membantu pelanggan menemukan produk yang sesuai dengan kebutuhannya. Salah satu pendekatan yang dapat diterapkan adalah **upselling**, yaitu memberikan rekomendasi produk dengan nilai atau tingkatan yang lebih tinggi dari produk yang sedang dipilih pelanggan. Penerapan pendekatan tersebut diharapkan dapat memberikan pilihan produk yang lebih sesuai sekaligus mendukung peningkatan nilai transaksi.

Brand Street Culture merupakan salah satu usaha yang bergerak di bidang fashion atau streetwear yang menyediakan berbagai produk seperti kaos, celana, dan aksesoris. Berdasarkan hasil pengamatan pada proses penjualan, kaos menjadi salah satu produk yang memiliki jumlah transaksi paling besar dibandingkan produk lainnya. Sistem e-commerce yang telah dikembangkan sebelumnya pada Brand Street Culture menggunakan pendekatan **cross-selling** untuk memberikan rekomendasi produk tambahan yang dapat melengkapi produk yang dipilih pelanggan. Namun, rekomendasi tersebut masih berfokus pada penawaran produk tambahan dan belum memberikan pilihan produk dengan tingkatan atau nilai yang lebih tinggi dari produk yang sedang dipilih. Kondisi tersebut menjadi dasar untuk mengembangkan sistem yang telah ada dengan menambahkan pendekatan upselling agar pelanggan dapat memperoleh alternatif produk yang memiliki kualitas, bahan, atau nilai produk yang lebih tinggi sesuai dengan produk yang sedang dipertimbangkan.

Dalam pengembangan sistem ini digunakan metodologi **Rational Unified Process (RUP)** sebagai metode pengembangan perangkat lunak. RUP merupakan metodologi yang membagi proses pengembangan sistem ke dalam beberapa fase dan workflow sehingga proses pengembangan dapat dilakukan secara terarah dan sistematis. Pengembangan pada penelitian ini dibatasi sampai pada fase **Construction**, yang meliputi proses analisis dan perancangan yang telah ditentukan, implementasi sistem, serta penyempurnaan fitur yang dibutuhkan. Pendekatan upselling kemudian diterapkan pada sistem e-commerce sebagai pengembangan dari fitur cross-selling yang telah tersedia sebelumnya, sehingga sistem dapat memberikan rekomendasi produk dengan nilai atau tingkatan yang lebih tinggi kepada pelanggan.

---

## 🎯 Tujuan Penelitian

Mengembangkan sistem informasi e-commerce pada Brand Street Culture dengan menambahkan fitur **upselling** sebagai pelengkap fitur **cross-selling** yang sudah ada, sehingga sistem dapat:
- Memberikan rekomendasi produk dengan nilai/kualitas lebih tinggi (upselling)
- Memberikan rekomendasi produk pelengkap (cross-selling)
- Meningkatkan pengalaman berbelanja pelanggan
- Membantu Brand Street Culture mengoptimalkan nilai transaksi

---

## 🏗️ Metodologi Pengembangan

**Rational Unified Process (RUP)** — dibatasi hingga fase **Construction**:

```
Inception → Elaboration → Construction (✅ batasan penelitian)
```

| Fase | Aktivitas |
|------|-----------|
| Inception | Analisis kebutuhan, studi kelayakan |
| Elaboration | Analisis & perancangan sistem (UML, ERD) |
| Construction | Implementasi, pengujian, penyempurnaan |

---

## 🛠️ Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Backend Framework | Laravel 13 (PHP 8.5) |
| Template Engine | Blade |
| CSS Framework | Tailwind CSS v3 |
| JavaScript | Alpine.js v3 |
| Build Tool | Vite |
| Database | SQLite (dev) / MySQL (prod) |
| Admin Panel | Filament v3 |
| Payment Gateway | Midtrans |
| Search Engine | Laravel Scout |
| Image Processing | Intervention Image |

---

## 👥 Peran Pengguna (User Roles)

| Role | Akses | Panel |
|------|-------|-------|
| **Tamu (Guest)** | Browse produk, lihat detail, cari produk | Storefront public |
| **Customer** | Semua fitur tamu + beli, wishlist, riwayat order | Storefront + My Account |
| **Admin** | Kelola produk, kategori, order, banner, upsell/cross-sell config | Panel Admin (`/admin`) |
| **Owner** | Semua akses Admin + laporan revenue, kelola admin, analytics | Panel Owner (`/owner`) |

---

## ✨ Fitur Utama

### 🏪 Storefront (Customer/Tamu)
- Homepage dengan hero banner slideshow
- Product listing dengan filter & sort
- Live search produk
- **Product Detail** dengan:
  - 🔼 **Upselling** — rekomendasi produk lebih premium dari produk yang dilihat
  - 🔄 **Cross-selling** — rekomendasi produk pelengkap ("Complete the Look")
- Cart drawer (slide-in, Alpine.js)
- Checkout multi-step dengan validasi stok real-time
- Payment via **Midtrans** (GoPay, Virtual Account, QRIS, dll)
- Akun customer (riwayat order, wishlist, profil)
- Notifikasi email otomatis (konfirmasi order, pembayaran, pengiriman)

### 🔧 Admin Panel (`/admin`)
- Dashboard statistik (total order, revenue, produk terlaris)
- CRUD Produk + galeri gambar + varian ukuran
- **Konfigurasi Upselling** — tentukan produk yang direkomendasikan sebagai upgrade
- **Konfigurasi Cross-selling** — tentukan produk pelengkap
- Manajemen kategori & banner homepage
- Manajemen & update status order

### 👑 Owner Panel (`/owner`)
- Semua fitur Admin
- Laporan penjualan & revenue
- Analitik efektivitas upselling dan cross-selling
- Manajemen user & akun admin

### 🔐 Keamanan
- Verifikasi signature webhook Midtrans
- Kalkulasi harga checkout selalu dari database (anti price manipulation)
- Laravel Policies untuk authorization per-resource
- Rate limiting pada endpoint login, cart, dan search
- Validasi & sanitasi upload gambar (UUID filename, max 2MB)
- Security headers (X-Frame-Options, HSTS, CSP)
- Mass assignment protection di semua Model

---

## 📁 Struktur Proyek

```
street-culture-market/
├── app/
│   ├── Http/Controllers/
│   ├── Mail/                        ← Mailable classes email
│   ├── Models/
│   ├── Observers/                   ← OrderObserver (trigger email)
│   ├── Policies/                    ← Authorization per resource
│   ├── Services/
│   │   ├── CartService.php          ← Cart berbasis session
│   │   ├── MidtransService.php      ← Integrasi payment
│   │   ├── UpsellService.php        ← Logika upselling
│   │   └── CrossSellService.php     ← Logika cross-selling
│   └── Filament/
│       ├── Admin/                   ← Resource panel /admin
│       └── Owner/                   ← Resource panel /owner
├── resources/views/
│   ├── emails/                      ← Template email (Markdown)
│   └── ...
├── database/
├── plan/                            ← 17 file dokumentasi implementasi
└── .env
```

---

## 📚 Dokumentasi Implementasi (17 File)

| File | Topik |
|------|-------|
| [01-tech-stack.md](./plan/01-tech-stack.md) | Detail semua teknologi yang dipakai |
| [02-database-schema.md](./plan/02-database-schema.md) | ERD + struktur semua tabel |
| [03-instalasi.md](./plan/03-instalasi.md) | Panduan install dari awal |
| [04-fitur-homepage.md](./plan/04-fitur-homepage.md) | Hero slider, new arrivals |
| [05-fitur-produk.md](./plan/05-fitur-produk.md) | Product listing + detail page |
| [06-fitur-cart.md](./plan/06-fitur-cart.md) | Cart + checkout (harga dari DB) |
| [07-fitur-auth.md](./plan/07-fitur-auth.md) | Login, register, akun customer |
| [08-fitur-admin.md](./plan/08-fitur-admin.md) | Filament Admin resources |
| [09-fitur-payment.md](./plan/09-fitur-payment.md) | Midtrans + verifikasi webhook |
| [10-fitur-search.md](./plan/10-fitur-search.md) | Live search + filter produk |
| [11-desain-sistem.md](./plan/11-desain-sistem.md) | Tailwind, layout, komponen UI |
| [12-deployment.md](./plan/12-deployment.md) | Deploy ke VPS / Railway |
| [13-fitur-upselling.md](./plan/13-fitur-upselling.md) | **Upselling** — DB, Service, View |
| [14-fitur-crossselling.md](./plan/14-fitur-crossselling.md) | **Cross-selling** — DB, Service, View |
| [15-sistem-role.md](./plan/15-sistem-role.md) | Multi-role, panel Admin & Owner |
| [16-keamanan.md](./plan/16-keamanan.md) | **Keamanan** — policies, rate limit, headers |
| [17-fitur-email.md](./plan/17-fitur-email.md) | **Email notifikasi** — queue, Mailable |

### Prasyarat
- PHP >= 8.2
- Composer >= 2.0
- Node.js >= 18
- NPM >= 9

### Instalasi

```bash
# 1. Clone repository
git clone https://github.com/username/street-culture-market.git
cd street-culture-market

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Migrasi database
php artisan migrate --seed

# 5. Link storage
php artisan storage:link

# 6. Buat user admin
php artisan make:filament-user

# 7. Jalankan server
php artisan serve      # Terminal 1
npm run dev            # Terminal 2
```

### Akses
| URL | Keterangan |
|-----|------------|
| `http://localhost:8000` | Storefront (customer) |
| `http://localhost:8000/login` | Login customer |
| `http://localhost:8000/admin` | Panel Admin |
| `http://localhost:8000/owner` | Panel Owner |

---

## 📚 Dokumentasi Implementasi

Lihat folder [`plan/`](./plan/) untuk dokumentasi detail setiap fitur.

---

## 👨‍💻 Informasi Skripsi

| | |
|-|-|
| **Judul** | Pengembangan Sistem Informasi E-Commerce Menggunakan Pendekatan Upselling pada Brand Street Culture |
| **Metodologi** | Rational Unified Process (RUP) |
| **Fase** | Construction (Analisis, Perancangan, Implementasi) |
| **Brand** | Street Culture Market |

---

## 📄 Lisensi

Project ini dibuat untuk keperluan penelitian skripsi. Hak cipta dilindungi.
