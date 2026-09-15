<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of active products with filters and search.
     */
    public function index(Request $request): View
    {
        SEOMeta::setTitle('Catalog — Street Culture Market');
        SEOMeta::setDescription('Browse our curated collection of heavyweight streetwear, tees, hoodies, outerwear, and cargo.');
        OpenGraph::setTitle('Catalog — Street Culture Market');

        $query = Product::with(['primaryImage', 'category', 'variants'])
            ->where('is_active', true);

        // Search filter
        if ($request->filled('q')) {
            $searchTerm = $request->input('q');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('sku', 'like', "%{$searchTerm}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->input('category'));
            });
        }

        // Sale filter
        if ($request->filled('sale')) {
            $query->whereNotNull('sale_price');
        }

        // Size filter
        if ($request->filled('size')) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where('size', $request->input('size'))->where('stock', '>', 0);
            });
        }

        // Sorting
        match ($request->input('sort')) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Display single product detail with upselling and cross-selling recommendations.
     */
    public function show(string $slug): View
    {
        $product = Product::with([
            'category',
            'images',
            'variants',
            'upsellProducts.primaryImage',
            'crossSells.primaryImage',
        ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        SEOMeta::setTitle($product->name.' — Street Culture Market');
        SEOMeta::setDescription($product->description ?? 'Premium streetwear release by Street Culture Market.');
        OpenGraph::setTitle($product->name.' — Street Culture Market');

        // Dynamic automatic upselling fallback if no manual upsell configured
        $upsells = $product->upsellProducts;
        if ($upsells->isEmpty() && $product->category_id) {
            $upsells = Product::with(['primaryImage', 'category'])
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('price', '>', $product->price)
                ->where('is_active', true)
                ->orderBy('price', 'asc')
                ->take(4)
                ->get();
        }

        // Dynamic automatic cross-sell fallback if no manual cross-sells configured
        $crossSells = $product->crossSells;
        if ($crossSells->isEmpty()) {
            $crossSells = Product::with(['primaryImage', 'category'])
                ->where('id', '!=', $product->id)
                ->where('is_active', true)
                ->inRandomOrder()
                ->take(4)
                ->get();
        }

        return view('products.show', compact('product', 'upsells', 'crossSells'));
    }
}
