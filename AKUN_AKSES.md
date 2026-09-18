# 🔐 Informasi Akses Akun - Street Culture Market

Dokumen ini berisi informasi kredensial dan hak akses akun default yang tersedia pada sistem **Street Culture Market** hasil dari seeder (`RolePermissionSeeder`).

---

## 📋 Ringkasan Kredensial Akun Default

| Role | Nama | Email | Password | Panel / Akses URL |
| :--- | :--- | :--- | :--- | :--- |
| **Owner** | Owner SCM | `owner@streetculturemarket.com` | `password` | `/admin` & `/login` |
| **Admin** | Admin SCM | `admin@streetculturemarket.com` | `password` | `/admin` & `/login` |
| **Customer** | John Customer | `customer@streetculturemarket.com` | `password` | `/login` (Storefront) |

> ⚠️ **Catatan Keamanan (Production):**  
> Password default `password` hanya diperuntukkan bagi lingkungan pengembangan (**Development / Local**). Pastikan untuk mengubah seluruh kata sandi sebelum melakukan rilis ke lingkungan **Production**.

---

## 🔑 Detail Akun & Hak Akses

### 1. 👑 Super Admin / Owner
* **Role:** `owner`
* **Nama:** Owner SCM
* **Email:** `owner@streetculturemarket.com`
* **Password:** `password`
* **URL Akses:** `http://localhost:8000/admin`
* **Hak Akses & Fitur:**
  * Akses penuh ke Filament Admin Panel.
  * Manajemen Katalog Produk (lihat, tambah, ubah, hapus).
  * Manajemen Varian Produk & Stok.
  * Manajemen Upselling & Cross-selling.
  * Manajemen Pesanan (Orders) & Pembaruan Status Pengiriman/Pembayaran.
  * Manajemen Pengguna / Karyawan (Admin, Staff, Customer).
  * Akses Laporan & Analitik Penjualan.
  * Pengaturan Sistem & Konfigurasi Toko.

---

### 2. 🛡️ Administrator
* **Role:** `admin`
* **Nama:** Admin SCM
* **Email:** `admin@streetculturemarket.com`
* **Password:** `password`
* **URL Akses:** `http://localhost:8000/admin`
* **Hak Akses & Fitur:**
  * Akses ke Filament Admin Panel.
  * Manajemen Katalog Produk (lihat, tambah, ubah, hapus).
  * Manajemen Varian Produk & Stok.
  * Manajemen Strategi Upselling & Cross-selling.
  * Melihat dan memproses Pesanan Pelanggan (Orders).
  * *Batasan:* Tidak memiliki akses ke manajemen pengguna sistem (`manage users`) dan konfigurasi sistem tingkat lanjut (`manage settings`).

---

### 3. 🛍️ Customer (Pelanggan)
* **Role:** `customer`
* **Nama:** John Customer
* **Email:** `customer@streetculturemarket.com`
* **Password:** `password`
* **No. Telepon:** `085712345678`
* **Alamat:** Jl. Sudirman No. 45, Jakarta Selatan, DKI Jakarta 12190
* **URL Akses:** `http://localhost:8000/login`
* **Hak Akses & Fitur:**
  * Jelajah katalog, pencarian produk, dan filter kategori/ukuran/harga.
  * Mengelola Keranjang Belanja (Cart).
  * Checkout dengan integrasi pembayaran Midtrans Snap.
  * Manajemen Wishlist pribadi.
  * Akses Dashboard Pelanggan (`/account`):
    * Riwayat & Detail Pesanan (`/account/orders`).
    * Pengaturan Profil & Alamat Pengiriman.
    * Ubah Kata Sandi & Hapus Akun.

---

## 🌐 Tautan Penting Sistem

| Halaman | URL Lokal | Keterangan |
| :--- | :--- | :--- |
| **Storefront (Homepage)** | `http://localhost:8000/` | Halaman utama toko |
| **Katalog Produk** | `http://localhost:8000/products` | Jelajah produk dengan filter & pencarian |
| **Login Pelanggan** | `http://localhost:8000/login` | Masuk ke akun storefront |
| **Registrasi Pelanggan** | `http://localhost:8000/register` | Pendaftaran akun pelanggan baru |
| **Dashboard Pelanggan** | `http://localhost:8000/account` | Profil & histori belanja |
| **Admin Panel (Filament)** | `http://localhost:8000/admin` | Panel operasional toko untuk Owner & Admin |

---

## 🔄 Cara Menjalankan Ulang Seeder Kredensial

Jika database di-reset atau kredensial ingin dikembalikan ke kondisi awal, jalankan perintah berikut di terminal:

```bash
# Jalankan seeder role, permission, dan akun saja
php artisan db:seed --class=RolePermissionSeeder

# Atau jika melakukan refresh seluruh database beserta sampel produk
php artisan migrate:fresh --seed
```
