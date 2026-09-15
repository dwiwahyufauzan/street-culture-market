<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    /**
     * Display the full shopping cart page.
     */
    public function index(): View
    {
        $summary = $this->cart->summary();
        $items = $summary['items'];
        $total = $summary['total'];
        $count = $summary['count'];

        return view('cart.index', compact('items', 'total', 'count'));
    }

    /**
     * Add an item to the shopping cart.
     */
    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'size' => 'required|string',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $summary = $this->cart->add(
            (int) $validated['product_id'],
            $validated['size'],
            (int) ($validated['quantity'] ?? 1)
        );

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Item successfully added to shopping bag.',
                'cart' => $summary,
            ]);
        }

        return back()->with('success', 'Item successfully added to shopping bag.');
    }

    /**
     * Update item quantity in cart.
     */
    public function update(Request $request, string $rowId): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $summary = $this->cart->update($rowId, (int) $validated['quantity']);

        if ($request->wantsJson()) {
            return response()->json($summary);
        }

        return back()->with('success', 'Shopping bag updated.');
    }

    /**
     * Remove item from cart.
     */
    public function remove(Request $request, string $rowId): JsonResponse|RedirectResponse
    {
        $summary = $this->cart->remove($rowId);

        if ($request->wantsJson()) {
            return response()->json($summary);
        }

        return back()->with('success', 'Item removed from bag.');
    }

    /**
     * Get JSON summary of current cart.
     */
    public function count(): JsonResponse
    {
        return response()->json($this->cart->summary());
    }
}
