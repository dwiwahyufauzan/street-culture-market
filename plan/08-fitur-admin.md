# 08 — Fitur Admin Panel (Filament v3)

## Overview
Admin panel menggunakan **Filament v3** yang sudah terinstall. Akses di `/admin`.

---

## Setup Admin User

```bash
# Buat user admin
php artisan make:filament-user

# Atau via tinker
php artisan tinker
>>> App\Models\User::create(['name'=>'Admin','email'=>'admin@scm.com','password'=>bcrypt('password')])->assignRole('admin');
```

---

## AdminPanelProvider

File: `app/Providers/Filament/AdminPanelProvider.php`

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->login()
        ->colors(['primary' => Color::Gray])
        ->font('Inter')
        ->navigationGroups([
            'Catalog',
            'Orders',
            'Marketing',
            'Settings',
        ])
        ->resources([
            ProductResource::class,
            CategoryResource::class,
            OrderResource::class,
            UserResource::class,
            BannerResource::class,
        ])
        ->widgets([
            StatsOverview::class,
            RecentOrdersWidget::class,
        ]);
}
```

---

## Filament Resources yang Dibuat

### 1. ProductResource

```bash
php artisan make:filament-resource Product --generate
```

```php
// app/Filament/Resources/ProductResource.php
class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationGroup = 'Catalog';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Product Info')->schema([
                TextInput::make('name')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn($state, Set $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Select::make('category_id')->relationship('category', 'name')->required(),
                RichEditor::make('description')->columnSpanFull(),
            ])->columns(2),

            Section::make('Pricing')->schema([
                TextInput::make('price')->numeric()->prefix('Rp')->required(),
                TextInput::make('sale_price')->numeric()->prefix('Rp'),
                TextInput::make('weight')->numeric()->suffix('gram'),
            ])->columns(3),

            Section::make('Images')->schema([
                SpatieMediaLibraryFileUpload::make('images')
                    ->multiple()
                    ->reorderable()
                    ->image()
                    ->imageEditor()
                    ->columnSpanFull(),
            ]),

            Section::make('Visibility')->schema([
                Toggle::make('is_active')->default(true),
                Toggle::make('is_featured'),
            ])->columns(2),

            Section::make('SEO')->schema([
                TextInput::make('meta_title'),
                Textarea::make('meta_description'),
            ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('primaryImage.image_path')->label('Photo')->disk('public')->square(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->badge(),
                TextColumn::make('price')->money('IDR')->sortable(),
                TextColumn::make('sale_price')->money('IDR'),
                IconColumn::make('is_active')->boolean(),
                IconColumn::make('is_featured')->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name'),
                TernaryFilter::make('is_active'),
                TernaryFilter::make('is_featured'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

---

### 2. CategoryResource

```bash
php artisan make:filament-resource Category --generate
```

Schema sederhana:
- name, slug, description
- image upload
- is_active toggle
- sort_order number

---

### 3. OrderResource

```bash
php artisan make:filament-resource Order --generate
```

```php
// Table columns:
TextColumn::make('order_number')->searchable(),
TextColumn::make('customer_name')->searchable(),
TextColumn::make('total')->money('IDR'),
BadgeColumn::make('status')
    ->colors([
        'warning' => 'pending',
        'primary' => 'processing',
        'info' => 'shipped',
        'success' => 'delivered',
        'danger' => 'cancelled',
    ]),
BadgeColumn::make('payment_status')
    ->colors(['danger' => 'unpaid', 'success' => 'paid']),

// Actions:
// Update status order
SelectAction::make('updateStatus')
    ->options(['pending', 'processing', 'shipped', 'delivered', 'cancelled'])

// View order items (RelationManager)
php artisan make:filament-relation-manager OrderResource items product_name
```

---

### 4. BannerResource

```bash
php artisan make:filament-resource Banner --generate
```

Schema:
- title, subtitle (optional)
- image upload (required)
- link URL
- is_active toggle
- sort_order (untuk urutan slider)

---

### 5. Dashboard Widgets

```bash
php artisan make:filament-widget StatsOverview --stats-overview
php artisan make:filament-widget RecentOrders --table
```

```php
// StatsOverview.php
protected function getStats(): array
{
    return [
        Stat::make('Total Orders', Order::count()),
        Stat::make('Revenue (Month)', 'Rp ' . number_format(Order::where('payment_status', 'paid')->whereMonth('created_at', now())->sum('total'), 0, ',', '.')),
        Stat::make('Active Products', Product::where('is_active', true)->count()),
        Stat::make('Total Customers', User::count()),
    ];
}
```

---

## Artisan Commands (Semua)

```bash
# Resources
php artisan make:filament-resource Product --generate
php artisan make:filament-resource Category --generate
php artisan make:filament-resource Order --generate
php artisan make:filament-resource User --generate
php artisan make:filament-resource Banner --generate

# Widgets
php artisan make:filament-widget StatsOverview --stats-overview
php artisan make:filament-widget RecentOrders --table

# Relation manager
php artisan make:filament-relation-manager OrderResource items product_name

# Publish assets
php artisan filament:assets
```

---

## URL Admin Panel
- **URL**: `http://localhost:8000/admin`
- **Login**: `admin@scm.com` / `password` (setelah `make:filament-user`)
