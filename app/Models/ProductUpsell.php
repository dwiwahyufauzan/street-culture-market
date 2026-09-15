<?php

namespace App\Models;

use Database\Factories\ProductUpsellFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUpsell extends Model
{
    /** @use HasFactory<ProductUpsellFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'upsell_id',
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
     * Recommended upsell product.
     *
     * @return BelongsTo<Product, $this>
     */
    public function upsell(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'upsell_id');
    }
}
