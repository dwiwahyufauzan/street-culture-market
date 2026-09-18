# Hasil Implementasi: Plan 09 — Fitur Payment Gateway (Midtrans Snap & Webhook)

## Ringkasan Eksekusi
- **Nomor Plan**: 09
- **Nama Fitur/Modul**: Fitur Payment Gateway (Midtrans Snap, Notification Webhook, & Signature Verification)
- **Status**: ✅ Selesai & Terverifikasi (100% Tests Passed)
- **Waktu Eksekusi**: 18 September 2026

---

## 1. Arsitektur & Fungsionalitas yang Diimplementasikan

Mengacu pada blueprint `plan/09-fitur-payment.md`, integrasi payment gateway **Midtrans** dibangun untuk mendukung berbagai metode pembayaran lokal Indonesia (GoPay, QRIS seluruh e-wallet, Virtual Account BCA/Mandiri/BNI/BRI/Permata, ShopeePay, dan Kartu Kredit/Debit):

### A. Konfigurasi Kredensial & Environment
1. **Config Service (`config/services.php`)**:
   - Menambahkan konfigurasi `midtrans` meliputi `server_key`, `client_key`, `is_production`, dan `snap_url`.
2. **Environment Variables (`.env` & `.env.example`)**:
   - `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION`, dan `MIDTRANS_SNAP_URL`.

---

### B. Midtrans Service (`app/Services/MidtransService.php`)
1. **Inisialisasi Konfigurasi Midtrans SDK**:
   - Pengaturan `Midtrans\Config::$serverKey`, `Midtrans\Config::$isProduction`, `Config::$isSanitized = true`, dan `Config::$is3ds = true`.
2. **Pembuatan Snap Token (`createSnapToken(Order $order)`)**:
   - **Transaction Details**: `order_id` (nomor pesanan unik) dan `gross_amount` (total pembayaran bulat).
   - **Customer & Shipping Snapshot**: Nama, email, nomor kontak, serta alamat lengkap pengiriman berstandar ISO `country_code => 'IDN'`.
   - **Harmonisasi Item Details**: Mengelompokkan setiap item pakaian pesanan, biaya pengiriman (*Shipping Cost*) sebagai item positif terpisah, dan potongan voucher (*Discount Amount*) sebagai item pengurang agar akumulasi item presisi sama dengan `gross_amount` (menghindari error 400 Midtrans mismatch).
   - **Callback Redirection**: Mengarahkan status akhir transaksi ke `route('checkout.success', $order)`.
   - **Ketahanan Offline / Sandbox**: Menyediakan penanganan gracefully jika kredensial di lingkungan testing/local berupa sandbox placeholder sehingga alur pemesanan dan checkout tidak mengalami fatal error.

---

### C. Alur Checkout & Payment Controller

#### 1. Pembaharuan Checkout (`app/Http/Controllers/CheckoutController.php`)
- **Checkout Store (`store`)**:
  - Setelah transaksi pesanan dan item pakaian tersimpan di database, sistem langsung membuat Midtrans Snap Token melalui `MidtransService`.
  - Token disimpan pada kolom `midtrans_snap_token` di tabel `orders`.
  - Mengarahkan customer ke halaman pembayaran: `redirect()->route('checkout.payment', $order)`.
- **Halaman Pembayaran (`payment`)**:
  - **Access Control Guard**: Hanya pembeli pesanan (atau sesi tamu aktif yang melakukan checkout) yang diizinkan melihat halaman pembayaran; akses user lain diblokir dengan `HTTP 403`.
  - **Idempotensi Status**: Jika pesanan sudah lunas (`payment_status === 'paid'`), customer dialihkan langsung ke halaman faktur sukses.
  - Jika Snap token belum terbentuk, sistem melakukan auto-generate.

#### 2. Payment Controller & Webhook (`app/Http/Controllers/PaymentController.php`)
- **Webhook Notifikasi (`notification`)**:
  - **Verifikasi Keamanan Kritis (SHA-512 Signature Key)**:
    $$\text{Expected Signature} = \text{hash('sha512', } \text{order\_id} + \text{status\_code} + \text{gross\_amount} + \text{server\_key}\text{)}$$
    Request webhook dengan tanda tangan tidak valid langsung ditolak dengan respons `HTTP 403 Forbidden` dan dicatat ke log peringatan keamanan.
  - **Transisi Status Transaksi**:
    - `settlement` atau `capture` (dengan `fraud_status != 'deny'`): Mengubah `payment_status` menjadi `paid` dan `status` menjadi `processing`.
    - `capture` dengan `fraud_status == 'challenge'`: Status pesanan tetap `pending` (under review).
    - `cancel`, `expire`, atau `deny`: Mengubah status pesanan menjadi `cancelled` dan `payment_status` tetap `unpaid`.
    - `pending`: Menetapkan status `pending` dan mencatat metode pembayaran yang dipilih customer.
- **Callback Finish (`finish`)**:
  - Menerima redirect balik dari Midtrans Snap setelah customer selesai bertransaksi dan mengarahkannya kembali ke faktur pesanan.
- **Simulator Sandbox (`simulateSuccess`)**:
  - Endpoint utilitas khusus di environment `local`/`testing` untuk menyimulasikan transaksi lunas seketika tanpa memerlukan transfer sungguhan.

---

### D. Pengecualian CSRF Webhook (`bootstrap/app.php`)
Webhook Midtrans dipanggil langsung oleh server Midtrans (machine-to-machine), sehingga rutenya dikecualikan dari verifikasi token CSRF:
```php
$middleware->validateCsrfTokens(except: [
    'payment/notification',
]);
```

---

### E. Tampilan Antarmuka Monokromatik Streetwear

1. **Halaman Pembayaran (`resources/views/checkout/payment.blade.php`)**:
   - Indikator progres langkah checkout (*01. Bag &rarr; 02. Shipping &rarr; **03. Payment** &rarr; 04. Confirmation*).
   - Rincian total pembayaran dengan tipografi display tebal monokromatik.
   - Breakdown pakaian yang dibeli lengkap dengan thumbnail, ukuran varian, kuantitas, dan subtotal.
   - Snapshot alamat pengiriman dan jasa kurir logistik.
   - Tombol utama hitam tebal `Pay Now / Bayar Sekarang` dengan integrasi script `snap.js`.
   - Interaksi pop-up Midtrans Snap yang menangani event `onSuccess`, `onPending`, `onError`, dan `onClose`.
2. **Pintasan Pembayaran pada Faktur Pesanan**:
   - Ditambahkan tombol `Pay Now &rarr;` di halaman faktur pesanan customer ([order-detail.blade.php](file:///Users/pzn/street-culture-market/resources/views/account/order-detail.blade.php)) dan halaman checkout success ([success.blade.php](file:///Users/pzn/street-culture-market/resources/views/checkout/success.blade.php)) apabila pesanan masih berstatus `unpaid`.

---

## 2. Struktur Berkas yang Dibuat & Dimodifikasi

```
app/
├── Http/Controllers/
│   ├── CheckoutController.php     # Update: generate Snap token, redirect ke payment, method payment()
│   └── PaymentController.php      # Baru: Webhook notification, SHA-512 verification, finish, simulator
└── Services/
    └── MidtransService.php        # Baru: Integrasi Midtrans Snap SDK & format item breakdown
bootstrap/
└── app.php                        # Update: CSRF exemption untuk payment/notification
resources/views/
├── account/
│   └── order-detail.blade.php     # Update: Tombol Pay Now untuk order unpaid
└── checkout/
    ├── payment.blade.php          # Baru: Antarmuka pembayaran Midtrans Snap monokromatik
    └── success.blade.php          # Update: Banner dan tombol Pay Now untuk order unpaid
routes/
└── web.php                        # Update: Registrasi rute checkout.payment, payment.notification, payment.finish
tests/Feature/
├── CheckoutTest.php               # Update: Verifikasi redirect ke payment dan keberadaan snap token
└── PaymentFeatureTest.php         # Baru: 12 unit & feature tests lengkap untuk modul pembayaran
```

---

## 3. Hasil Pengujian & Verifikasi

Pengujian otomatis dijalankan menggunakan PHPUnit via `php artisan test`:

```bash
php artisan test --filter=PaymentFeatureTest
```
**Hasil Pengujian Payment Feature (12 tests)**:
- `test_customer_can_view_payment_page_for_their_order`: Passed ✅
- `test_guest_with_session_can_view_payment_page`: Passed ✅
- `test_other_customer_cannot_view_order_payment_page`: Passed ✅ (Status 403)
- `test_already_paid_order_redirects_to_checkout_success`: Passed ✅
- `test_midtrans_service_generates_snap_token`: Passed ✅
- `test_midtrans_webhook_signature_mismatch_returns_403`: Passed ✅
- `test_midtrans_webhook_order_not_found_returns_404`: Passed ✅
- `test_midtrans_webhook_settlement_updates_order_to_paid_and_processing`: Passed ✅
- `test_midtrans_webhook_challenge_keeps_order_pending`: Passed ✅
- `test_midtrans_webhook_cancel_or_expire_updates_order_to_cancelled`: Passed ✅
- `test_payment_finish_callback_redirects_to_checkout_success`: Passed ✅
- `test_payment_simulation_endpoint_in_testing_environment`: Passed ✅

**Seluruh Pengujian Aplikasi (`php artisan test --compact`)**:
- Total: **84 tests passed**, **287 assertions passed** (0 failures).

---

## 4. Standar Kode & Format
- Dijalankan `vendor/bin/pint --format agent` untuk menjaga kesesuaian standar Laravel Pint PSR-12 di seluruh berkas baru dan modifikasi.
