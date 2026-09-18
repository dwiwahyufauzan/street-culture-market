<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Display customer's wishlist.
     */
    public function index(Request $request): View
    {
        SEOMeta::setTitle('My Wishlist — Street Culture Market');

        $user = $request->user();
        $wishlists = $user->wishlists()
            ->with(['product.primaryImage', 'product.category'])
            ->latest()
            ->paginate(12);

        return view('wishlist.index', compact('wishlists'));
    }

    /**
     * Toggle product in user's wishlist.
     */
    public function toggle(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'unauthenticated',
                    'message' => 'Please login to save items to your wishlist.',
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect()->guest(route('login'))->with('error', 'Please login to save items to your wishlist.');
        }

        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $status = 'removed';
            $message = 'Item removed from wishlist.';
            $isWishlisted = false;
        } else {
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
            $status = 'added';
            $message = 'Item saved to wishlist.';
            $isWishlisted = true;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => $status,
                'message' => $message,
                'is_wishlisted' => $isWishlisted,
                'count' => $user->wishlists()->count(),
            ]);
        }

        return back()->with('success', $message);
    }
}
