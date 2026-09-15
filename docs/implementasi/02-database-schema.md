# Hasil Implementasi: Plan 02 — Database Schema, Models, Migrations, Seeders & Factories

## Ringkasan Eksekusi
- **Nomor Plan**: 02
- **Nama Fitur/Modul**: Database Schema, Models, Migrations, Seeders & Factories
- **Status**: ✅ Selesai & Terverifikasi (100% Tests Passed)
- **Waktu Eksekusi**: 16 September 2026

---

## 1. Arsitektur Database & ERD

Skema database telah diimplementasikan secara komprehensif untuk mendukung seluruh kebutuhan e-commerce streetwear, termasuk relasi multi-varian, upload galeri gambar, checkout guest/user, serta modul penelitian utama: **Upselling** dan **Cross-selling**.

```
                        ┌──────────────┐
                        │    users     │
                        └──────┬───────┘
                               │
            ┌──────────────────┼──────────────────┐
            ▼                  ▼                  ▼
     ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
     │  wishlists  │    │   orders    │    │ model_has_  │
     └──────┬──────┘    └──────┬──────┘    │    roles    │
            │                  ▼           └─────────────┘
            │           ┌─────────────┐
            │           │ order_items │
            │           └──────┬──────┘
            ▼                  ▼
     ┌─────────────────────────────────┐
     │            products             │
     └──────┬──────┬──────┬──────┬─────┘
            │      │      │      │
            ▼      │      │      ▼
     ┌──────────┐  │      │  ┌───────────────────────┐
     │categories│  │      │  │    product_upsells    │
     └──────────┘  │      │  │ (rekomendasi upgrade) │
                   ▼      │  └───────────────────────┘
     ┌─────────────────┐  ▼
     │product_variants │ ┌───────────────────────────┐
     │ (size, color,   │ │    product_cross_sells    │
     │     stock)      │ │   (rekomendasi pelengkap) │
     └─────────────────┘ └───────────────────────────┘
```

---

## 2. Tabel & Migrasi yang Telah Diimplementasikan

| Nama Tabel | File Migrasi | Deskripsi & Fitur Kunci |
|---|---|---|
| `users` | `0001_01_01_000000_create_users_table.php` | Menambahkan kolom `role` (`customer`, `admin`, `owner`), `phone`, `address`, `city`, `province`, `postal_code`. |
| `permission_tables` | `2026_09_15_164613_create_permission_tables.php` | Tabel standar Spatie (`roles`, `permissions`, `model_has_roles`, dll). |
| `categories` | `2026_09_15_164653_create_categories_table.php` | Kategori produk (`name`, `slug` unique, `description`, `image`, `is_active`, `sort_order`). |
| `products` | `2026_09_15_164654_create_products_table.php` | Katalog utama streetwear (`price`, `sale_price`, `sku`, `weight`, `is_active`, `is_featured`, SEO meta). |
| `product_variants` | `2026_09_15_164655_create_product_variants_table.php` | Kombinasi ukuran (`size`: S, M, L, XL), warna (`color`), dan stok inventori independen per SKU. |
| `product_images` | `2026_09_15_164656_create_product_images_table.php` | Galeri foto produk (`image_path`, `is_primary`, `sort_order`, `alt_text`). |
| `orders` | `2026_09_15_164657_create_orders_table.php` | Transaksi (`order_number` unique, snapshot customer & pengiriman, status pembayaran, token Midtrans Snap). |
| `order_items` | `2026_09_15_164658_create_order_items_table.php` | Snapshot produk saat transaksi (`product_name`, `size`, `color`, `price`, `quantity`, `subtotal`). |
| `wishlists` | `2026_09_15_164659_create_wishlists_table.php` | Produk favorit per user (`user_id`, `product_id`, unique composite key). |
| `banners` | `2026_09_15_164700_create_banners_table.php` | Slider hero homepage (`title`, `subtitle`, `image`, `link`, `is_active`, `sort_order`). |
| `product_upsells` | `2026_09_15_164701_create_product_upsells_table.php` | **Fitur Upselling**: relasi produk trigger ↔ produk tier lebih tinggi (`product_id`, `upsell_id`, unique composite). |
| `product_cross_sells` | `2026_09_15_164702_create_product_cross_sells_table.php` | **Fitur Cross-selling**: relasi produk trigger ↔ produk pelengkap (`product_id`, `cross_sell_id`, unique composite). |

---

## 3. Model Eloquent & Logika Bisnis

### A. `App\Models\User`
- Mengimplementasikan `FilamentUser` untuk pembagian akses panel.
- Menggunakan trait `Spatie\Permission\Traits\HasRoles`.
- Helper methods:
  - `isAdmin()`: Memeriksa apakah user bertindak sebagai Admin.
  - `isOwner()`: Memeriksa apakah user bertindak sebagai Owner.
  - `isCustomer()`: Memeriksa user reguler.
  - `canAccessPanel(Panel $panel)`:
    - Panel `admin` dapat diakses oleh role `admin` & `owner`.
    - Panel `owner` khusus hanya untuk role `owner`.
    - Customer dilarang mengakses panel admin maupun owner.
- Relasi: `orders()`, `wishlists()`, `wishlistProducts()`.

### B. `App\Models\Product`
- Terintegrasi dengan `Laravel\Scout\Searchable` (`toSearchableArray()` terindeks nama, deskripsi, SKU, dan kategori).
- Relasi Eloquent:
  - `category(): BelongsTo`
  - `variants(): HasMany`
  - `images(): HasMany`
  - `primaryImage(): HasOne`
  - `upsellProducts(): BelongsToMany` (relasi ke produk upgrade)
  - `upsellOf(): BelongsToMany` (relasi balik produk upsell)
  - `crossSells(): BelongsToMany` (relasi ke produk komplementer)
  - `crossSellOf(): BelongsToMany` (relasi balik produk cross-sell)
- Scopes & Accessors:
  - `scopeActive()`, `scopeFeatured()`
  - `effective_price`: Menghasilkan `sale_price` jika ada diskon aktif, jika tidak kembali ke `price`.
  - `has_discount`: Mengembalikan boolean jika sedang diskon.
  - `total_stock`: Menghitung akumulasi seluruh stok varian.

### C. `App\Models\Order` & `App\Models\OrderItem`
- Helper unik `Order::generateOrderNumber()` dengan format `SCM-YYYYMMDD-XXXXXX`.
- Status helpers: `isPaid()`, `isPending()`, `isCancelled()`.
- Relasi terpadu ke `User`, `OrderItem`, `Product`, dan `ProductVariant`.

### D. `App\Models\ProductVariant`, `ProductImage`, `Wishlist`, `Banner`
- Menyediakan accessor status stok `isInStock()`, scope banner `scopeActive()`, dan proteksi mass-assignment `$fillable`.

---

## 4. Factory & Seeder Data Realistis

Telah dibuat 10 Factory dan 4 Seeder utama:

1. **`RolePermissionSeeder`**:
   - Mendaftarkan permissions manajemen produk, upsell, cross-sell, order, dan laporan.
   - Membuat akun default siap pakai:
     - **Admin**: `admin@streetculturemarket.com` / `password`
     - **Owner**: `owner@streetculturemarket.com` / `password`
     - **Customer**: `customer@streetculturemarket.com` / `password`
2. **`CategorySeeder`**:
   - 6 Kategori streetwear terkurasi: `T-Shirts & Tops`, `Hoodies & Sweats`, `Jackets & Outerwear`, `Pants & Cargo`, `Headwear & Caps`, `Accessories`.
3. **`ProductSeeder`**:
   - Mengisi katalog produk lengkap dengan varian ukuran (S, M, L, XL), warna, stok, dan gambar.
   - **Data Upselling Nyata**:
     - *SCM Classic Box Logo Tee (Rp 220k)* ➔ Upsell ke *SCM Acid Wash Distressed Graphic Tee (Rp 350k)*.
     - *SCM Essential Heavyweight Hoodie (Rp 450k)* ➔ Upsell ke *SCM Luxe Loopback French Terry Hoodie (Rp 680k)*.
     - *SCM Lightweight Nylon Windbreaker (Rp 380k)* ➔ Upsell ke *SCM Technical Tactical MA-1 Bomber (Rp 850k)*.
   - **Data Cross-selling Nyata**:
     - *SCM Classic Box Logo Tee* ➔ Cross-sell ke *Cargo Pants* & *5-Panel Camp Cap*.
     - *SCM Essential Heavyweight Hoodie* ➔ Cross-sell ke *Cargo Pants* & *Tactical Crossbody Bag*.
4. **`BannerSeeder`**:
   - 3 Hero editorial banners untuk tampilan beranda streetwear minimalis.

---

## 5. Hasil Verifikasi & Automated Testing

### A. Eksekusi Migrasi & Seeding
```bash
php artisan migrate:fresh --seed --no-interaction
```
*Hasil:* Seluruh 14 file migrasi dan 4 seeder tereksekusi mulus tanpa kendala.

### B. Pengujian Feature Test (`tests/Feature/DatabaseSchemaTest.php`)
```bash
php artisan test --filter=DatabaseSchemaTest --compact
```
*Hasil:* **6 tests passed, 23 assertions passed** (100%):
- `test_categories_and_products_relationship` ✅
- `test_product_variants_and_stock_aggregation` ✅
- `test_upsell_relationships` ✅
- `test_cross_sell_relationships` ✅
- `test_order_and_order_items_relationship` ✅
- `test_user_roles_and_panel_access` ✅

### C. Pengujian Seluruh Test Suite
```bash
php artisan test --compact
```
*Hasil:* **31 passed, 84 assertions** (0 failures, 0 errors).

### D. Format Kode (Laravel Pint)
```bash
vendor/bin/pint app database tests --format agent
```
*Hasil:* Seluruh file PHP terformat rapi sesuai panduan Laravel.

---

## 6. Daftar File yang Dibuat / Dimodifikasi

| Status | File | Deskripsi |
|---|---|---|
| `[MODIFY]` | `database/migrations/0001_01_01_000000_create_users_table.php` | Kolom `role`, `phone`, `address`, `city`, dll |
| `[NEW]` | `database/migrations/2026_09_15_164613_create_permission_tables.php` | Migrasi tabel Spatie Permission |
| `[NEW]` | `database/migrations/2026_09_15_164653_create_categories_table.php` | Migrasi tabel categories |
| `[NEW]` | `database/migrations/2026_09_15_164654_create_products_table.php` | Migrasi tabel products |
| `[NEW]` | `database/migrations/2026_09_15_164655_create_product_variants_table.php` | Migrasi tabel product_variants |
| `[NEW]` | `database/migrations/2026_09_15_164656_create_product_images_table.php` | Migrasi tabel product_images |
| `[NEW]` | `database/migrations/2026_09_15_164657_create_orders_table.php` | Migrasi tabel orders |
| `[NEW]` | `database/migrations/2026_09_15_164658_create_order_items_table.php` | Migrasi tabel order_items |
| `[NEW]` | `database/migrations/2026_09_15_164659_create_wishlists_table.php` | Migrasi tabel wishlists |
| `[NEW]` | `database/migrations/2026_09_15_164700_create_banners_table.php` | Migrasi tabel banners |
| `[NEW]` | `database/migrations/2026_09_15_164701_create_product_upsells_table.php` | Migrasi tabel product_upsells |
| `[NEW]` | `database/migrations/2026_09_15_164702_create_product_cross_sells_table.php` | Migrasi tabel product_cross_sells |
| `[MODIFY]` | `app/Models/User.php` | Model User dengan FilamentUser, Spatie, profile & roles |
| `[NEW]` | `app/Models/Category.php` | Model Category |
| `[NEW]` | `app/Models/Product.php` | Model Product dengan Scout, Scopes, Accessor, Upsell & Cross-sell relations |
| `[NEW]` | `app/Models/ProductVariant.php` | Model ProductVariant |
| `[NEW]` | `app/Models/ProductImage.php` | Model ProductImage |
| `[NEW]` | `app/Models/Order.php` | Model Order |
| `[NEW]` | `app/Models/OrderItem.php` | Model OrderItem |
| `[NEW]` | `app/Models/Wishlist.php` | Model Wishlist |
| `[NEW]` | `app/Models/Banner.php` | Model Banner |
| `[NEW]` | `app/Models/ProductUpsell.php` | Model ProductUpsell |
| `[NEW]` | `app/Models/ProductCrossSell.php` | Model ProductCrossSell |
| `[NEW]` | `database/factories/*Factory.php` (10 files) | Model Factories lengkap |
| `[NEW]` | `database/seeders/RolePermissionSeeder.php` | Seeder roles, permissions & users default |
| `[NEW]` | `database/seeders/CategorySeeder.php` | Seeder kategori streetwear |
| `[NEW]` | `database/seeders/ProductSeeder.php` | Seeder produk, varian, gambar, upselling & cross-selling |
| `[NEW]` | `database/seeders/BannerSeeder.php` | Seeder hero banners |
| `[MODIFY]` | `database/seeders/DatabaseSeeder.php` | Runner seeder gabungan |
| `[NEW]` | `tests/Feature/DatabaseSchemaTest.php` | Unit & Feature tests database schema & logic |
| `[NEW]` | `docs/implementasi/02-database-schema.md` | Dokumentasi lengkap hasil implementasi Plan 02 |

---

## 7. Langkah Berikutnya
Melanjutkan ke plan berikutnya:
- **Plan 04 / Layout & Fitur Homepage (Storefront)**: Pembuatan layout master canalize.asia (`navbar`, `announcement bar`, `cart-drawer`, `footer`), hero slider banner, featured products, category showcase.
- **Plan 07 / Auth System & Multi-role Routing**: Halaman login/register customer, proteksi route per role, dan pemisahan panel admin & owner.
