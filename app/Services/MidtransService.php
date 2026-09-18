<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Throwable;

class MidtransService
{
    /**
     * Initialize Midtrans configurations.
     */
    public function __construct()
    {
        Config::$serverKey = (string) config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Create Snap Token for an Order.
     */
    public function createSnapToken(Order $order): string
    {
        $order->loadMissing('items');

        $itemDetails = [];

        foreach ($order->items as $item) {
            $itemDetails[] = [
                'id' => (string) $item->product_id,
                'price' => (int) round($item->price),
                'quantity' => (int) $item->quantity,
                'name' => mb_substr($item->product_name.' ('.$item->size.')', 0, 50),
            ];
        }

        // Include shipping cost as line item if greater than 0
        if ((float) $order->shipping_cost > 0) {
            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => (int) round($order->shipping_cost),
                'quantity' => 1,
                'name' => mb_substr('Shipping: '.($order->shipping_method ?? 'Courier'), 0, 50),
            ];
        }

        // Include discount as negative line item if applicable
        if ((float) $order->discount_amount > 0) {
            $itemDetails[] = [
                'id' => 'DISCOUNT',
                'price' => -(int) round($order->discount_amount),
                'quantity' => 1,
                'name' => 'Voucher Discount',
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) round($order->total),
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone ?? '',
            ],
            'shipping_address' => [
                'first_name' => $order->customer_name,
                'phone' => $order->customer_phone ?? '',
                'address' => $order->shipping_address ?? '',
                'city' => $order->shipping_city ?? '',
                'postal_code' => $order->shipping_postal ?? '',
                'country_code' => 'IDN',
            ],
            'item_details' => $itemDetails,
            'callbacks' => [
                'finish' => route('checkout.success', $order),
            ],
        ];

        try {
            // If dummy or placeholder key in local/testing, provide mock token
            if (empty(Config::$serverKey) || str_contains(Config::$serverKey, 'your-key-here')) {
                return 'snap-sandbox-'.$order->order_number;
            }

            return Snap::getSnapToken($params);
        } catch (Throwable $e) {
            Log::error('Midtrans Snap token generation error: '.$e->getMessage(), [
                'order_number' => $order->order_number,
                'total' => $order->total,
            ]);

            if (app()->environment('local', 'testing')) {
                return 'snap-fallback-'.$order->order_number;
            }

            throw $e;
        }
    }
}
