<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Str;

class CartService
{
    private string $key = 'shopping_cart';

    /**
     * Get all cart items.
     *
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return session($this->key, []);
    }

    /**
     * Add a product to the cart.
     *
     * @return array<string, mixed>
     */
    public function add(int $productId, string $size, int $quantity = 1): array
    {
        $cart = $this->all();
        $product = Product::with('primaryImage')->findOrFail($productId);

        $rowId = Str::uuid()->toString();

        // Check if item with identical product and size exists
        foreach ($cart as $key => $item) {
            if ($item['product_id'] === $productId && $item['size'] === $size) {
                $cart[$key]['quantity'] += $quantity;
                session([$this->key => $cart]);

                return $this->summary();
            }
        }

        $cart[$rowId] = [
            'row_id' => $rowId,
            'product_id' => $productId,
            'name' => $product->name,
            'slug' => $product->slug,
            'size' => $size,
            'quantity' => $quantity,
            'price' => (float) ($product->sale_price ?? $product->price),
            'image' => $product->primaryImage?->image_path,
        ];

        session([$this->key => $cart]);

        return $this->summary();
    }

    /**
     * Update quantity of an item.
     *
     * @return array<string, mixed>
     */
    public function update(string $rowId, int $quantity): array
    {
        $cart = $this->all();
        if (isset($cart[$rowId])) {
            if ($quantity <= 0) {
                unset($cart[$rowId]);
            } else {
                $cart[$rowId]['quantity'] = $quantity;
            }
            session([$this->key => $cart]);
        }

        return $this->summary();
    }

    /**
     * Remove an item from the cart.
     *
     * @return array<string, mixed>
     */
    public function remove(string $rowId): array
    {
        $cart = $this->all();
        unset($cart[$rowId]);
        session([$this->key => $cart]);

        return $this->summary();
    }

    /**
     * Calculate total price directly from database to prevent price manipulation.
     */
    public function total(): float
    {
        $total = 0;
        foreach ($this->all() as $item) {
            $product = Product::find($item['product_id']);
            if (! $product) {
                continue;
            }
            $price = $product->sale_price ?? $product->price;
            $total += (float) $price * $item['quantity'];
        }

        return (float) $total;
    }

    /**
     * Get total quantity of all items in cart.
     */
    public function count(): int
    {
        return (int) collect($this->all())->sum('quantity');
    }

    /**
     * Return summary of cart state.
     *
     * @return array{items: list<array<string, mixed>>, count: int, total: float}
     */
    public function summary(): array
    {
        return [
            'items' => array_values($this->all()),
            'count' => $this->count(),
            'total' => $this->total(),
        ];
    }

    /**
     * Clear all items in cart.
     */
    public function clear(): void
    {
        session()->forget($this->key);
    }
}
