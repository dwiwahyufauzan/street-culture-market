<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'sale_price',
        'sku',
        'weight',
        'is_active',
        'is_featured',
        'meta_title',
        'meta_description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'weight' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Category this product belongs to.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Product variants (size, color, stock).
     *
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Gallery images for this product.
     *
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Primary display image.
     *
     * @return HasOne<ProductImage, $this>
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Order items referencing this product.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Wishlist entries for this product.
     *
     * @return HasMany<Wishlist, $this>
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Upsell recommendation products (higher-tier alternatives).
     *
     * @return BelongsToMany<Product, $this>
     */
    public function upsellProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_upsells',
            'product_id',
            'upsell_id'
        )->withPivot('sort_order')->orderByPivot('sort_order')->withTimestamps();
    }

    /**
     * Products that recommend this product as an upsell.
     *
     * @return BelongsToMany<Product, $this>
     */
    public function upsellOf(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_upsells',
            'upsell_id',
            'product_id'
        )->withPivot('sort_order')->orderByPivot('sort_order')->withTimestamps();
    }

    /**
     * Cross-sell recommendation products (complementary items).
     *
     * @return BelongsToMany<Product, $this>
     */
    public function crossSells(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_cross_sells',
            'product_id',
            'cross_sell_id'
        )->withPivot('sort_order')->orderByPivot('sort_order')->withTimestamps();
    }

    /**
     * Products that recommend this product as a cross-sell.
     *
     * @return BelongsToMany<Product, $this>
     */
    public function crossSellOf(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_cross_sells',
            'cross_sell_id',
            'product_id'
        )->withPivot('sort_order')->orderByPivot('sort_order')->withTimestamps();
    }

    /**
     * Scope a query to only include active products.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured products.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * Get the final effective price considering sales discount.
     */
    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->sale_price !== null && $this->sale_price < $this->price ? $this->sale_price : $this->price);
    }

    /**
     * Check if product has an active discount.
     */
    public function getHasDiscountAttribute(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    /**
     * Total stock aggregated across all variants.
     */
    public function getTotalStockAttribute(): int
    {
        return (int) $this->variants()->sum('stock');
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Get the indexable data array for Scout search.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
        ];
    }
}
