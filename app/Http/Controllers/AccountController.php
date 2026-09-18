<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display the customer account dashboard overview.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        SEOMeta::setTitle('My Account — Street Culture Market');

        $recentOrders = $user->orders()
            ->with(['items.product.primaryImage'])
            ->latest()
            ->take(5)
            ->get();

        $ordersCount = $user->orders()->count();
        $wishlistCount = $user->wishlists()->count();

        return view('account.index', compact(
            'user',
            'recentOrders',
            'ordersCount',
            'wishlistCount'
        ));
    }

    /**
     * Display customer's full order history.
     */
    public function orders(Request $request): View
    {
        $user = $request->user();

        SEOMeta::setTitle('My Orders — Street Culture Market');

        $orders = $user->orders()
            ->with(['items.product.primaryImage'])
            ->latest()
            ->paginate(10);

        return view('account.orders', compact('user', 'orders'));
    }

    /**
     * Display detailed information for a specific customer order.
     */
    public function orderDetail(Request $request, Order $order): View
    {
        if ($order->user_id !== $request->user()->id && ! $request->user()->isAdmin() && ! $request->user()->isOwner()) {
            abort(403, 'Unauthorized access to this order.');
        }

        SEOMeta::setTitle("Order #{$order->order_number} — Street Culture Market");

        $order->load(['items.product.primaryImage', 'user']);

        return view('account.order-detail', compact('order'));
    }

    /**
     * Redirect or display wishlist for account.
     */
    public function wishlist(): RedirectResponse
    {
        return redirect()->route('wishlist.index');
    }
}
