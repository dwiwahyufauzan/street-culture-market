# 02 — Database Schema: Street Culture Market

## ERD Overview

```
users ──────────────────────── orders
  │                              │
  └── wishlists                  └── order_items
        │                              │
        └── products ─────────────────┘
              │
              ├── categories
              ├── product_variants (size, color, stock)
              └── product_images

banners (homepage slider)
```

---

## Tabel: `users`
```sql
id              BIGINT PK
name            VARCHAR(255)
email           VARCHAR(255) UNIQUE
email_verified_at TIMESTAMP NULL
password        VARCHAR(255)
phone           VARCHAR(20) NULL
address         TEXT NULL
city            VARCHAR(100) NULL
province        VARCHAR(100) NULL
postal_code     VARCHAR(10) NULL
remember_token  VARCHAR(100) NULL
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

## Tabel: `categories`
```sql
id          BIGINT PK
name        VARCHAR(255)         -- "T-Shirts", "Hoodies", "Pants"
slug        VARCHAR(255) UNIQUE
description TEXT NULL
image       VARCHAR(255) NULL
is_active   BOOLEAN DEFAULT true
sort_order  INT DEFAULT 0
created_at  TIMESTAMP
updated_at  TIMESTAMP
```

## Tabel: `products`
```sql
id              BIGINT PK
category_id     BIGINT FK → categories.id
name            VARCHAR(255)
slug            VARCHAR(255) UNIQUE
description     TEXT NULL
price           DECIMAL(12,2)
sale_price      DECIMAL(12,2) NULL    -- harga diskon
sku             VARCHAR(100) NULL
weight          INT NULL               -- gram, untuk ongkos kirim
is_active       BOOLEAN DEFAULT true
is_featured     BOOLEAN DEFAULT false  -- tampil di homepage
meta_title      VARCHAR(255) NULL
meta_description TEXT NULL
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

## Tabel: `product_variants`
```sql
id          BIGINT PK
product_id  BIGINT FK → products.id
size        VARCHAR(20)          -- "XS", "S", "M", "L", "XL", "XXL"
color       VARCHAR(50) NULL     -- "Black", "White", "Olive"
stock       INT DEFAULT 0
sku         VARCHAR(100) NULL
created_at  TIMESTAMP
updated_at  TIMESTAMP
```

## Tabel: `product_images`
```sql
id          BIGINT PK
product_id  BIGINT FK → products.id
image_path  VARCHAR(255)
alt_text    VARCHAR(255) NULL
is_primary  BOOLEAN DEFAULT false
sort_order  INT DEFAULT 0
created_at  TIMESTAMP
updated_at  TIMESTAMP
```

## Tabel: `orders`
```sql
id                  BIGINT PK
user_id             BIGINT FK → users.id NULL  -- NULL = guest checkout
order_number        VARCHAR(50) UNIQUE           -- "SCM-2024-00001"
status              ENUM('pending','processing','shipped','delivered','cancelled')
payment_status      ENUM('unpaid','paid','refunded') DEFAULT 'unpaid'
payment_method      VARCHAR(50) NULL             -- "midtrans_gopay", dll
midtrans_order_id   VARCHAR(100) NULL
midtrans_snap_token VARCHAR(255) NULL

-- Data customer (snapshot saat order)
customer_name       VARCHAR(255)
customer_email      VARCHAR(255)
customer_phone      VARCHAR(20)

-- Alamat pengiriman
shipping_address    TEXT
shipping_city       VARCHAR(100)
shipping_province   VARCHAR(100)
shipping_postal     VARCHAR(10)
shipping_method     VARCHAR(100) NULL           -- "JNE REG", dll
shipping_cost       DECIMAL(12,2) DEFAULT 0

-- Harga
subtotal            DECIMAL(12,2)
discount_amount     DECIMAL(12,2) DEFAULT 0
total               DECIMAL(12,2)

notes               TEXT NULL
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

## Tabel: `order_items`
```sql
id                  BIGINT PK
order_id            BIGINT FK → orders.id
product_id          BIGINT FK → products.id
product_variant_id  BIGINT FK → product_variants.id NULL
product_name        VARCHAR(255)    -- snapshot nama
product_sku         VARCHAR(100) NULL
size                VARCHAR(20) NULL
color               VARCHAR(50) NULL
quantity            INT
price               DECIMAL(12,2)  -- harga per item saat order
subtotal            DECIMAL(12,2)
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

## Tabel: `wishlists`
```sql
id          BIGINT PK
user_id     BIGINT FK → users.id
product_id  BIGINT FK → products.id
created_at  TIMESTAMP
updated_at  TIMESTAMP

UNIQUE(user_id, product_id)
```

## Tabel: `product_upsells` *(fitur upselling)*
```sql
id          BIGINT PK
product_id  BIGINT FK → products.id    -- produk trigger
upsell_id   BIGINT FK → products.id    -- produk rekomendasi (harga lebih tinggi)
sort_order  INT DEFAULT 0
created_at  TIMESTAMP
updated_at  TIMESTAMP

UNIQUE(product_id, upsell_id)
```

## Tabel: `product_cross_sells` *(fitur cross-selling)*
```sql
id              BIGINT PK
product_id      BIGINT FK → products.id    -- produk trigger
cross_sell_id   BIGINT FK → products.id    -- produk pelengkap
sort_order      INT DEFAULT 0
created_at      TIMESTAMP
updated_at      TIMESTAMP

UNIQUE(product_id, cross_sell_id)
```

## Tabel: `roles` & `permissions` *(via Spatie Laravel Permission)*
```sql
-- roles: customer, admin, owner
-- permissions: view products, create products, manage upsells, view reports, dll
-- model_has_roles: relasi user ↔ role
-- role_has_permissions: relasi role ↔ permission
```

## Tabel: `banners`
```sql
id          BIGINT PK
title       VARCHAR(255) NULL
subtitle    VARCHAR(255) NULL
image       VARCHAR(255)
link        VARCHAR(255) NULL
is_active   BOOLEAN DEFAULT true
sort_order  INT DEFAULT 0
created_at  TIMESTAMP
updated_at  TIMESTAMP
```

---

## Relasi Eloquent (Model)

### Product.php
```php
public function category(): BelongsTo
{
    return $this->belongsTo(Category::class);
}

public function variants(): HasMany
{
    return $this->hasMany(ProductVariant::class);
}

public function images(): HasMany
{
    return $this->hasMany(ProductImage::class)->orderBy('sort_order');
}

public function primaryImage(): HasOne
{
    return $this->hasOne(ProductImage::class)->where('is_primary', true);
}
```

### Order.php
```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function items(): HasMany
{
    return $this->hasMany(OrderItem::class);
}
```

---

## Migration Commands
```bash
php artisan make:migration create_categories_table
php artisan make:migration create_products_table
php artisan make:migration create_product_variants_table
php artisan make:migration create_product_images_table
php artisan make:migration create_orders_table
php artisan make:migration create_order_items_table
php artisan make:migration create_wishlists_table
php artisan make:migration create_banners_table
php artisan make:migration create_product_upsells_table      # fitur upselling
php artisan make:migration create_product_cross_sells_table  # fitur cross-selling
# Tabel roles/permissions dibuat otomatis oleh Spatie Laravel Permission

php artisan migrate
php artisan db:seed
```
