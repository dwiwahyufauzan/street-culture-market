# 17 — Fitur Email Notifikasi

## Overview
Sistem mengirimkan email otomatis untuk kejadian-kejadian penting seperti konfirmasi order, pembayaran berhasil, dan update status pengiriman. Menggunakan **Laravel Mail** dengan **Mailable classes** dan **Queue** agar tidak memperlambat response.

---

## Konfigurasi Mail di `.env`

### Development (Mailtrap — tidak kirim email sungguhan)
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_FROM_ADDRESS="noreply@streetculturemarket.com"
MAIL_FROM_NAME="Street Culture Market"
```

### Production (Gmail SMTP)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your_app_password  # Google App Password (bukan password biasa)
MAIL_FROM_ADDRESS="noreply@streetculturemarket.com"
MAIL_FROM_NAME="Street Culture Market"
```

---

## Setup Queue (Agar Email Tidak Block Response)

```env
# .env
QUEUE_CONNECTION=database
```

```bash
php artisan queue:table
php artisan migrate
# Jalankan worker (di production, pakai Supervisor/systemd)
php artisan queue:work
```

---

## Email yang Dikirim

| Trigger | Email | Penerima |
|---------|-------|----------|
| Order dibuat | Konfirmasi Order | Customer |
| Pembayaran berhasil | Pembayaran Diterima | Customer |
| Status → "shipped" | Order Dikirim + No. Resi | Customer |
| Status → "delivered" | Order Selesai + Review Request | Customer |
| Order baru masuk | Notifikasi Order Baru | Admin & Owner |

---

## Step 1: Buat Mailable Classes

```bash
php artisan make:mail OrderConfirmation --markdown=emails.orders.confirmation
php artisan make:mail PaymentSuccess --markdown=emails.orders.payment-success
php artisan make:mail OrderShipped --markdown=emails.orders.shipped
php artisan make:mail NewOrderAdmin --markdown=emails.admin.new-order
```

---

## Step 2: OrderConfirmation Mailable

```php
// app/Mail/OrderConfirmation.php
<?php
namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Konfirmasi Order #{$this->order->order_number} — Street Culture Market",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.confirmation',
            with: ['order' => $this->order],
        );
    }
}
```

---

## Step 3: Template Email (Markdown)

```markdown
{{-- resources/views/emails/orders/confirmation.blade.php --}}
<x-mail::message>
# Terima Kasih, {{ $order->customer_name }}! 🎉

Pesanan Anda telah kami terima dan sedang menunggu konfirmasi pembayaran.

**Detail Order:**

| | |
|--|--|
| No. Order | `{{ $order->order_number }}` |
| Tanggal | {{ $order->created_at->format('d M Y, H:i') }} WIB |
| Total | **Rp {{ number_format($order->total, 0, ',', '.') }}** |
| Status Pembayaran | {{ ucfirst($order->payment_status) }} |

---

**Daftar Produk:**

@foreach($order->items as $item)
- **{{ $item->product_name }}** (Size: {{ $item->size }})
  Qty: {{ $item->quantity }} × Rp {{ number_format($item->price, 0, ',', '.') }}
@endforeach

---

**Dikirim ke:**
{{ $order->customer_name }}
{{ $order->shipping_address }}, {{ $order->shipping_city }}
{{ $order->shipping_province }} {{ $order->shipping_postal }}

<x-mail::button :url="route('account.orders.show', $order)" color="dark">
Lihat Detail Order
</x-mail::button>

Jika ada pertanyaan, balas email ini atau hubungi kami.

Salam,<br>
**Street Culture Market**
</x-mail::message>
```

---

## Step 4: Template Email — Pembayaran Berhasil

```markdown
{{-- resources/views/emails/orders/payment-success.blade.php --}}
<x-mail::message>
# Pembayaran Berhasil ✅

Hei **{{ $order->customer_name }}**,

Pembayaran untuk order **#{{ $order->order_number }}** telah berhasil dikonfirmasi.

<x-mail::panel>
**Total Dibayar:** Rp {{ number_format($order->total, 0, ',', '.') }}
**Metode Bayar:** {{ $order->payment_method ?? 'Midtrans' }}
</x-mail::panel>

Pesanan Anda sedang kami proses dan akan segera dikirimkan. Kami akan memberitahu Anda saat pesanan dikirim.

<x-mail::button :url="route('account.orders.show', $order)" color="dark">
Lacak Pesanan
</x-mail::button>

Salam,<br>
**Street Culture Market**
</x-mail::message>
```

---

## Step 5: Template Email — Order Dikirim

```markdown
{{-- resources/views/emails/orders/shipped.blade.php --}}
<x-mail::message>
# Pesanan Anda Dikirim! 📦

Hei **{{ $order->customer_name }}**,

Pesanan **#{{ $order->order_number }}** sedang dalam perjalanan menuju Anda.

<x-mail::panel>
**Kurir:** {{ $order->shipping_method ?? 'JNE' }}
@if($order->tracking_number)
**No. Resi:** `{{ $order->tracking_number }}`
@endif
**Estimasi Tiba:** 2–5 hari kerja
</x-mail::panel>

<x-mail::button :url="route('account.orders.show', $order)" color="dark">
Lihat Detail Pengiriman
</x-mail::button>

Salam,<br>
**Street Culture Market**
</x-mail::message>
```

---

## Step 6: Kirim Email — Integrasi ke Controller & Observer

### Cara 1: Di Controller (Simple)

```php
// app/Http/Controllers/CheckoutController.php
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;

DB::transaction(function () use ($request, $total) {
    $order = Order::create([...]);

    foreach ($this->cart->all() as $item) { ... }

    $this->cart->clear();

    // Kirim email konfirmasi (via queue, tidak block response)
    Mail::to($order->customer_email)
        ->queue(new OrderConfirmation($order->load('items')));
});
```

### Cara 2: Observer (Lebih Clean — Recommended)

```bash
php artisan make:observer OrderObserver --model=Order
```

```php
// app/Observers/OrderObserver.php
<?php
namespace App\Observers;

use App\Mail\OrderConfirmation;
use App\Mail\PaymentSuccess;
use App\Mail\OrderShipped;
use App\Mail\NewOrderAdmin;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    // Dipanggil saat order baru dibuat
    public function created(Order $order): void
    {
        // Email ke customer
        Mail::to($order->customer_email)
            ->queue(new OrderConfirmation($order->load('items')));

        // Email ke admin & owner
        $adminEmails = User::role(['admin', 'owner'])->pluck('email')->toArray();
        Mail::to($adminEmails)->queue(new NewOrderAdmin($order));
    }

    // Dipanggil saat order diupdate
    public function updated(Order $order): void
    {
        // Pembayaran berhasil
        if ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
            Mail::to($order->customer_email)
                ->queue(new PaymentSuccess($order));
        }

        // Order dikirim
        if ($order->wasChanged('status') && $order->status === 'shipped') {
            Mail::to($order->customer_email)
                ->queue(new OrderShipped($order));
        }
    }
}
```

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    Order::observe(OrderObserver::class);
}
```

---

## Step 7: Kustomisasi Template Email Base

```bash
php artisan vendor:publish --tag=laravel-mail
```

Kemudian edit `resources/views/vendor/mail/html/themes/default.css`:

```css
/* Sesuaikan dengan branding SCM */
body { font-family: 'Helvetica', Arial, sans-serif; }
.button-dark { background-color: #0a0a0a; color: #ffffff; }
.panel { background: #f9f9f9; border-left: 4px solid #0a0a0a; }
```

---

## Step 8: Testing Email

```bash
# Test kirim email di development
php artisan tinker

>>> $order = App\Models\Order::first();
>>> Mail::to('test@example.com')->send(new App\Mail\OrderConfirmation($order));
>>> # Cek di Mailtrap inbox
```

---

## Queue Worker di Production

```bash
# Install Supervisor untuk menjaga queue worker tetap berjalan
sudo apt install supervisor

# Buat konfigurasi
sudo nano /etc/supervisor/conf.d/scm-queue.conf
```

```ini
[program:scm-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/street-culture-market/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasecs=0
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/street-culture-market/storage/logs/queue.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start scm-queue:*
```

---

## Artisan Commands

```bash
# Buat Mailables
php artisan make:mail OrderConfirmation --markdown=emails.orders.confirmation
php artisan make:mail PaymentSuccess --markdown=emails.orders.payment-success
php artisan make:mail OrderShipped --markdown=emails.orders.shipped
php artisan make:mail NewOrderAdmin --markdown=emails.admin.new-order

# Buat Observer
php artisan make:observer OrderObserver --model=Order

# Setup queue
php artisan queue:table
php artisan migrate

# Publish email template
php artisan vendor:publish --tag=laravel-mail

# Jalankan worker (development)
php artisan queue:work
```
