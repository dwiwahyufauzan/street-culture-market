# 15 — Sistem Role & Panel Terpisah

## Struktur Role

| Role | Deskripsi | Login URL | Panel |
|------|-----------|-----------|-------|
| **Tamu (Guest)** | Tidak login, hanya browse | — | Storefront public |
| **Customer** | User terdaftar, bisa belanja | `/login` | Storefront + `/account` |
| **Admin** | Kelola produk, order, upsell | `/admin/login` | `/admin` |
| **Owner** | Akses penuh + laporan | `/owner/login` | `/owner` |

---

## Strategi Implementasi

### Opsi: Filament Multi-Panel (Recommended)
- **Panel 1**: `/admin` → AdminPanelProvider (untuk role: admin)
- **Panel 2**: `/owner` → OwnerPanelProvider (untuk role: owner)
- **Storefront**: Laravel standard dengan Breeze auth (untuk role: customer + tamu)

Keuntungan:
- Login page terpisah otomatis
- UI dan fitur bisa sangat berbeda antar panel
- Permission per-panel sudah dihandle Filament

---

## Step 1: Install Spatie Laravel Permission

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\LaravelPermission\PermissionServiceProvider"
php artisan migrate
```

---

## Step 2: Setup Model User

```php
// app/Models/User.php
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasRoles;

    public function canAccessPanel(Panel $panel): bool
    {
        return match($panel->getId()) {
            'admin' => $this->hasRole('admin') || $this->hasRole('owner'),
            'owner' => $this->hasRole('owner'),
            default => false,
        };
    }
}
```

---

## Step 3: Buat Owner Panel Provider

```bash
php artisan make:filament-panel owner
```

```php
// app/Providers/Filament/OwnerPanelProvider.php
public function panel(Panel $panel): Panel
{
    return $panel
        ->id('owner')
        ->path('owner')
        ->login()
        ->colors(['primary' => Color::Emerald])  // warna berbeda dari admin
        ->font('Inter')
        ->brandName('SCM — Owner')
        ->navigationGroups([
            'Reports',
            'Catalog',
            'Orders',
            'User Management',
        ])
        ->resources([
            // Semua resource admin
            ProductResource::class,
            CategoryResource::class,
            OrderResource::class,
            BannerResource::class,
            // Tambahan khusus owner
            UserResource::class,
            RevenueReportResource::class,
            UpsellAnalyticsResource::class,
        ])
        ->widgets([
            OwnerStatsOverview::class,
            RevenueChartWidget::class,
            UpsellPerformanceWidget::class,
            RecentOrdersWidget::class,
        ]);
}
```

---

## Step 4: Update Admin Panel Provider

```php
// app/Providers/Filament/AdminPanelProvider.php
public function panel(Panel $panel): Panel
{
    return $panel
        ->id('admin')
        ->path('admin')
        ->login()
        ->colors(['primary' => Color::Gray])  // hitam/abu untuk admin
        ->font('Inter')
        ->brandName('SCM — Admin')
        ->navigationGroups([
            'Catalog',
            'Orders',
            'Marketing',
        ])
        ->resources([
            ProductResource::class,
            CategoryResource::class,
            OrderResource::class,
            BannerResource::class,
        ])
        ->widgets([
            AdminStatsOverview::class,
            RecentOrdersWidget::class,
        ]);
}
```

---

## Step 5: Seeder untuk Roles & Permissions

```php
// database/seeders/RolePermissionSeeder.php
<?php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            // Products
            'view products', 'create products', 'edit products', 'delete products',
            // Categories
            'view categories', 'create categories', 'edit categories', 'delete categories',
            // Orders
            'view orders', 'edit order status', 'delete orders',
            // Users
            'view users', 'create users', 'edit users', 'delete users',
            // Banners
            'manage banners',
            // Upsell/Cross-sell
            'manage upsells', 'manage crosssells',
            // Reports (owner only)
            'view reports', 'view revenue', 'view analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Role: customer
        $customer = Role::firstOrCreate(['name' => 'customer']);
        // customer tidak punya permission admin

        // Role: admin
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'view products', 'create products', 'edit products', 'delete products',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view orders', 'edit order status',
            'manage banners',
            'manage upsells', 'manage crosssells',
        ]);

        // Role: owner (semua permission)
        $owner = Role::firstOrCreate(['name' => 'owner']);
        $owner->givePermissionTo(Permission::all());
    }
}
```

```bash
php artisan db:seed --class=RolePermissionSeeder
```

---

## Step 6: Assign Role ke User

```bash
# Via Tinker
php artisan tinker

# Buat owner
>>> $owner = App\Models\User::create(['name' => 'Owner', 'email' => 'owner@scm.com', 'password' => bcrypt('password')]);
>>> $owner->assignRole('owner');

# Buat admin
>>> $admin = App\Models\User::create(['name' => 'Admin', 'email' => 'admin@scm.com', 'password' => bcrypt('password')]);
>>> $admin->assignRole('admin');

# Customer dibuat otomatis saat register
# Di RegisteredUserController.php: $user->assignRole('customer');
```

---

## Step 7: Update RegisteredUserController

```php
// app/Http/Controllers/Auth/RegisteredUserController.php
public function store(Request $request): RedirectResponse
{
    $request->validate([...]);

    $user = User::create([...]);

    // Auto-assign customer role
    $user->assignRole('customer');

    event(new Registered($user));

    Auth::login($user);

    return redirect(route('home', absolute: false)); // redirect ke homepage, bukan dashboard
}
```

---

## Step 8: Redirect Setelah Login (Berdasarkan Role)

Buat middleware atau update `AuthenticatedSessionController`:

```php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php
public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();
    $request->session()->regenerate();

    $user = auth()->user();

    // Redirect berdasarkan role
    if ($user->hasRole('owner')) {
        return redirect('/owner');
    }

    if ($user->hasRole('admin')) {
        return redirect('/admin');
    }

    // Customer → kembali ke intended URL atau homepage
    return redirect()->intended(route('home', absolute: false));
}
```

---

## Tampilan Login yang Berbeda

### Customer Login (`/login`)
- Desain storefront: putih, minimalis, logo SCM
- Link ke register
- "Forgot password"

### Admin Login (`/admin/login`)
- Dihandle otomatis oleh Filament
- Bisa dikustomisasi via `->loginRouteSlug('login')` di panel provider
- Warna abu/hitam (warna panel admin)

### Owner Login (`/owner/login`)
- Dihandle otomatis oleh Filament
- Warna emerald/hijau (warna panel owner)

---

## Middleware Protection

```php
// routes/web.php

// Storefront — bisa diakses tamu
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

// Customer only
Route::middleware(['auth', 'role:customer|admin|owner'])->group(function () {
    Route::get('/account', [AccountController::class, 'index'])->name('account.index');
    Route::get('/account/orders', [AccountController::class, 'orders']);
    Route::post('/wishlist/toggle/{product}', [WishlistController::class, 'toggle']);
});

// Panel admin & owner sudah diproteksi otomatis oleh Filament
// melalui canAccessPanel() di model User
```

---

## Tabel Perbandingan Panel

| Fitur | Tamu | Customer | Admin Panel | Owner Panel |
|-------|------|----------|-------------|-------------|
| Browse produk | ✅ | ✅ | ✅ | ✅ |
| Beli produk | ❌ | ✅ | ❌ | ❌ |
| Wishlist | ❌ | ✅ | ❌ | ❌ |
| Riwayat order | ❌ | ✅ | ❌ | ❌ |
| Kelola produk | ❌ | ❌ | ✅ | ✅ |
| Kelola upsell | ❌ | ❌ | ✅ | ✅ |
| Kelola order | ❌ | ❌ | ✅ | ✅ |
| Kelola user | ❌ | ❌ | ❌ | ✅ |
| Laporan revenue | ❌ | ❌ | ❌ | ✅ |
| Analitik upsell | ❌ | ❌ | ❌ | ✅ |

---

## Artisan Commands

```bash
# Install Spatie Permission
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\LaravelPermission\PermissionServiceProvider"
php artisan migrate

# Buat Owner Panel
php artisan make:filament-panel owner

# Buat Widgets Owner
php artisan make:filament-widget OwnerStatsOverview --stats-overview --panel=owner
php artisan make:filament-widget RevenueChart --chart --panel=owner
php artisan make:filament-widget UpsellPerformance --table --panel=owner

# Seed roles
php artisan db:seed --class=RolePermissionSeeder
```
