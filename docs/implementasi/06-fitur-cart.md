# Hasil Implementasi: Plan 06 — Fitur Keranjang Belanja (Cart) & Checkout

## Ringkasan Eksekusi
- **Nomor Plan**: 06
- **Nama Fitur/Modul**: Fitur Keranjang Belanja (Cart) & Order Checkout
- **Status**: ✅ Selesai & Terverifikasi (100% Tests Passed)
- **Waktu Eksekusi**: 18 September 2026

---

## 1. Arsitektur & Fungsionalitas yang Diimplementasikan

Sesuai blueprint `plan/06-fitur-cart.md`, sistem keranjang belanja dan checkout dibangun dengan arsitektur ringan, tangguh, dan aman:

### A. Arsitektur Session-Based Shopping Cart (`CartService`)
1. **Penyimpanan PHP Session**:
   - Cart dikelola murni menggunakan PHP Session (`shopping_cart`) tanpa dependensi package eksternal, menjaga performa tinggi dan portabilitas lintas versi Laravel.
2. **Keamanan Harga Anti-Manipulasi (Database Re-verification)**:
   - Perhitungan subtotal dan total selalu diverifikasi ulang langsung ke database (`Product::find()`), memastikan user tidak dapat memanipulasi nominal harga melalui console DevTools maupun manipulasi payload HTTP request.
3. **Penyatuan Item Varian**:
   - Jika customer menambahkan produk yang sama dengan ukuran (`size`) yang sama, kuantitas item otomatis diakumulasikan.
4. **Metode Cart API**:
   - `all()`: Mengambil seluruh item dalam session keranjang.
   - `add($productId, $size, $quantity)`: Menambahkan item baru atau menambah kuantitas item yang sudah ada.
   - `update($rowId, $quantity)`: Memperbarui kuantitas (menghapus item jika kuantitas <= 0).
   - `remove($rowId)`: Menghapus item tertentu dari keranjang.
   - `total()`: Menghitung total harga valid dari database.
   - `count()`: Menghitung total kuantitas seluruh garment dalam keranjang.
   - `summary()`: Mengembalikan payload JSON/array gabungan (`items`, `count`, `total`).
   - `clear()`: Membersihkan cart saat order berhasil dibuat.

---

### B. Halaman Shopping Bag (`/cart`) & Offcanvas Drawer (`x-cart-drawer`)
1. **Halaman Keranjang Penuh (`/cart`)**:
   - Tabel garment monokromatik dengan thumbnail produk, nama, ukuran varian, harga satuan, dan kalkulasi subtotal.
   - Stepper penyesuaian kuantitas kuantitas (- / +) langsung terhubung dengan route patch `/cart/{rowId}`.
   - Tombol hapus item (`DELETE /cart/{rowId}`).
   - Sidebar ringkasan pesanan dengan tombol *Proceed To Checkout*.
   - Status keranjang kosong (*Empty Bag State*) dengan ajakan kembali ke katalog.
2. **Offcanvas Cart Drawer (Alpine.js)**:
   - Buka secara instan dari header atau tombol *Add to Bag* (`open-cart` event).
   - Menampilkan daftar produk dengan stepper AJAX kuantitas dan kalkulasi live tanpa refresh halaman.

---

### C. Halaman Checkout Pesanan (`/checkout`)
1. **Guard Validasi Keranjang Kosong**:
   - Jika keranjang kosong, pengunjung dialihkan ke `/cart` dengan pesan error flash.
2. **Indikator Langkah Pembelian**:
   - Breadcrumb visual: `1. Shopping Bag -> 2. Shipping & Review -> 3. Payment`.
3. **Pre-fill Data Pelanggan Terautentikasi**:
   - Jika customer telah login, data nama, email, nomor telepon, alamat, kota, provinsi, dan kode pos otomatis terisi dari profil user.
4. **Opsi Ekspedisi Pengiriman Interaktif (Alpine.js)**:
   - JNE Express (Regular): Rp 25.000 (2-3 hari kerja).
   - SiCepat BEST (Next Day): Rp 35.000 (1-2 hari kerja).
   - J&T Express (Standard): Rp 22.000 (2-4 hari kerja).
   - Pemilihan radio button ekspedisi langsung memperbarui nominal ongkir dan grand total secara reaktif di sisi client.
5. **Kebijakan Free Shipping**:
   - Pesanan dengan subtotal >= Rp 1.000.000 otomatis mendapatkan gratis ongkir (*Free Delivery Qualified*).
6. **Catatan Tambahan Pesanan (`notes`)**:
   - Field opsional untuk instruksi khusus pengantaran kurir.

---

### D. Transaksi Pembuatan Order (`POST /checkout`)
1. **Validasi Input Ketat**:
   - `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `shipping_city`, `shipping_province`, `shipping_postal`, `shipping_method`.
2. **Database Transaction (`DB::transaction`)**:
   - Mengenerate nomor order unik `SCM-YYYYMMDD-XXXXXX` via `Order::generateOrderNumber()`.
   - Menyimpan order ke tabel `orders` dengan status `pending` dan status pembayaran `unpaid`.
   - Memasukkan snapshot setiap produk dan varian ukuran ke tabel `order_items`.
   - Mengosongkan session cart (`$cartService->clear()`).
   - Menyimpan `placed_order_id` ke session untuk akses aman guest.

---

### E. Halaman Konfirmasi Pesanan (`/checkout/success/{order}`)
1. **Tampilan Struk Order Monokrom**:
   - Nomor referensi pesanan tebal dengan status `pending • unpaid`.
   - Rincian alamat tujuan dan ekspedisi pengiriman terpilih.
   - Ringkasan daftar pakaian (gambar, ukuran, kuantitas, harga, subtotal).
   - Rincian keuangan (Subtotal, Ongkos Kirim, Total Akhir).
   - Tombol navigasi kembali ke katalog atau menuju Customer Dashboard.
2. **Otorisasi Keamanan**:
   - Order milik user terdaftar hanya dapat diakses oleh user pemiliknya (mencegah IDOR attack).

---

## 2. Berkas yang Dibuat & Dimodifikasi

| Tipe File | Path Berkas | Deskripsi Perubahan |
|---|---|---|
| **Controller** | `app/Http/Controllers/CheckoutController.php` | **[BARU]** Menangani alur checkout (`index`, `store`, dan `success`) dengan validasi, kalkulasi ongkir, transaksi DB, dan kontrol otorisasi. |
| **Routes** | `routes/web.php` | Mendaftarkan rute publik `checkout.index`, `checkout.store`, dan `checkout.success`. |
| **View** | `resources/views/checkout/index.blade.php` | **[BARU]** Form checkout streetwear monokromatik dengan pemilihan ekspedisi live Alpine.js dan overview pesanan. |
| **View** | `resources/views/checkout/success.blade.php` | **[BARU]** Halaman konfirmasi dan struk rincian pesanan berhasil dibuat. |
| **View** | `resources/views/cart/index.blade.php` | Memperbarui link tombol checkout menggunakan named route `route('checkout.index')`. |
| **Test** | `tests/Feature/CheckoutTest.php` | **[BARU]** 7 pengujian komprehensif menguji redirect cart kosong, render checkout, pre-fill user, pembuatan order guest & auth, validasi form, dan halaman success. |

---

## 3. Hasil Pengujian & Verifikasi

### A. Pengujian Fitur Checkout (`CheckoutTest`)
```bash
php artisan test --filter=CheckoutTest
```
**Hasil:**
```json
{"tool":"phpunit","result":"passed","tests":7,"passed":7,"assertions":48,"duration_ms":502}
```
Seluruh 7 skenario pengujian lulus 100%:
1. `test_empty_cart_redirects_from_checkout_to_cart_index` ✅
2. `test_checkout_screen_renders_with_cart_items` ✅
3. `test_authenticated_customer_checkout_pre_fills_user_data` ✅
4. `test_guest_can_place_order_successfully` ✅
5. `test_authenticated_customer_order_is_associated_with_user_id` ✅
6. `test_checkout_validation_errors_when_fields_missing` ✅
7. `test_checkout_success_page_renders_order_details` ✅

### B. Pengujian Seluruh Test Suite Aplikasi
```bash
php artisan test --compact
```
**Hasil:**
```json
{"tool":"phpunit","result":"passed","tests":54,"passed":54,"assertions":195,"duration_ms":2805}
```
Total **54 tests** dan **195 assertions** lulus tanpa kegagalan (0 errors, 0 failures).

### C. Kompilasi Aset Frontend (Vite)
```bash
npm run build
```
- `public/build/assets/app-BY2YG-cU.css`: 70.13 kB (gzip: 12.35 kB)
- `public/build/assets/app-CuJtlCw-.js`: 54.33 kB (gzip: 19.13 kB)
- Berhasil dibangun dalam 1.70s.

### D. Standarisasi Format Kode (Laravel Pint)
```bash
vendor/bin/pint --dirty --format agent
```
Seluruh file PHP berstandar PSR-12 / Laravel Pint (passed).

---

## 4. Langkah Berikutnya
Melanjutkan ke **Plan 07: Fitur Autentikasi & Profil Customer (Breeze Customization)**.
