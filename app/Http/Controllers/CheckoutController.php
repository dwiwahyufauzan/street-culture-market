<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use App\Services\MidtransService;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart) {}

    /**
     * Display the checkout form with cart items and shipping options.
     */
    public function index(): View|RedirectResponse
    {
        if ($this->cart->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Your shopping bag is empty. Please select garments first.');
        }

        SEOMeta::setTitle('Checkout — Street Culture Market');
        SEOMeta::setDescription('Review shipping address and proceed to secure payment for your Street Culture Market garments.');

        $cart = $this->cart->summary();
        $user = auth()->user();

        $couriers = [
            [
                'code' => 'JNE_REG',
                'name' => 'JNE Express (Regular)',
                'estimate' => '2-3 Business Days',
                'cost' => 25000,
            ],
            [
                'code' => 'SICEPAT_BEST',
                'name' => 'SiCepat BEST (Next Day)',
                'estimate' => '1-2 Business Days',
                'cost' => 35000,
            ],
            [
                'code' => 'JNT_STD',
                'name' => 'J&T Express (Standard)',
                'estimate' => '2-4 Business Days',
                'cost' => 22000,
            ],
        ];

        $freeShippingThreshold = 1000000;
        $isFreeShipping = $cart['total'] >= $freeShippingThreshold;

        return view('checkout.index', compact(
            'cart',
            'user',
            'couriers',
            'freeShippingThreshold',
            'isFreeShipping'
        ));
    }

    /**
     * Store a newly created order and order items in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($this->cart->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Your shopping bag is empty.');
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'shipping_city' => 'required|string|max:100',
            'shipping_province' => 'required|string|max:100',
            'shipping_postal' => 'required|string|max:10',
            'shipping_method' => 'required|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $subtotal = $this->cart->total();

        // Calculate shipping cost (Free over Rp 1.000.000)
        $courierCosts = [
            'JNE_REG' => 25000,
            'SICEPAT_BEST' => 35000,
            'JNT_STD' => 22000,
        ];

        $shippingCost = ($subtotal >= 1000000)
            ? 0
            : ($courierCosts[$validated['shipping_method']] ?? 25000);

        $total = $subtotal + $shippingCost;

        $order = DB::transaction(function () use ($validated, $subtotal, $shippingCost, $total) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => Order::generateOrderNumber(),
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_city' => $validated['shipping_city'],
                'shipping_province' => $validated['shipping_province'],
                'shipping_postal' => $validated['shipping_postal'],
                'shipping_method' => $validated['shipping_method'],
                'shipping_cost' => $shippingCost,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($this->cart->all() as $item) {
                $product = Product::find($item['product_id']);
                $variant = $product?->variants()->where('size', $item['size'])->first();

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $variant?->id,
                    'product_name' => $item['name'],
                    'product_sku' => $product?->sku,
                    'size' => $item['size'],
                    'quantity' => (int) $item['quantity'],
                    'price' => (float) $item['price'],
                    'subtotal' => (float) ($item['price'] * $item['quantity']),
                ]);
            }

            // Generate Midtrans Snap token
            $snapToken = app(MidtransService::class)->createSnapToken($order);
            $order->update(['midtrans_snap_token' => $snapToken]);

            $this->cart->clear();

            session(['placed_order_id' => $order->id]);

            return $order;
        });

        return redirect()->route('checkout.payment', $order)
            ->with('success', 'Order created successfully! Please proceed to complete payment.');
    }

    /**
     * Display the payment gateway checkout page with Midtrans Snap.
     */
    public function payment(Order $order): View|RedirectResponse
    {
        // Access control: only order owner or session placer can view
        if ($order->user_id && auth()->check() && auth()->id() !== $order->user_id) {
            abort(403, 'Unauthorized access to this order payment.');
        }

        if ($order->user_id && ! auth()->check() && session('placed_order_id') !== $order->id) {
            return redirect()->route('login');
        }

        // If order is already paid, redirect to success
        if ($order->payment_status === 'paid') {
            return redirect()->route('checkout.success', $order)
                ->with('info', 'This order has already been paid.');
        }

        // Generate snap token if not yet generated
        if (! $order->midtrans_snap_token) {
            $snapToken = app(MidtransService::class)->createSnapToken($order);
            $order->update(['midtrans_snap_token' => $snapToken]);
        }

        $order->load(['items.product.primaryImage']);

        SEOMeta::setTitle("Payment for Order #{$order->order_number} — Street Culture Market");
        SEOMeta::setDescription("Complete your payment for Street Culture Market order #{$order->order_number}.");

        return view('checkout.payment', compact('order'));
    }

    /**
     * Display order confirmation and summary after checkout.
     */
    public function success(Order $order): View|RedirectResponse
    {
        // Access control: only order owner or session placer can view
        if ($order->user_id && auth()->check() && auth()->id() !== $order->user_id) {
            abort(403, 'Unauthorized access to this order.');
        }

        if ($order->user_id && ! auth()->check() && session('placed_order_id') !== $order->id) {
            return redirect()->route('login');
        }

        $order->load(['items.product.primaryImage']);

        SEOMeta::setTitle("Order #{$order->order_number} — Street Culture Market");

        return view('checkout.success', compact('order'));
    }
}
