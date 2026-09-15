<?php

namespace App\Models;

use Database\Factories\ProductCrossSellFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCrossSell extends Model
{
    /** @use HasFactory<ProductCrossSellFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'cross_sell_id',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Trigger product.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Recommended cross-sell product.
     *
     * @return BelongsTo<Product, $this>
     */
    public function crossSell(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'cross_sell_id');
    }
}
