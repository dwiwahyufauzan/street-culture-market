<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display the specified collection/category with its products.
     */
    public function show(Request $request, string $slug): View
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        SEOMeta::setTitle($category->name.' Collection — Street Culture Market');
        SEOMeta::setDescription($category->description ?? 'Discover the '.$category->name.' collection at Street Culture Market.');
        OpenGraph::setTitle($category->name.' Collection — Street Culture Market');

        $query = Product::with(['primaryImage', 'category', 'variants'])
            ->where('category_id', $category->id)
            ->where('is_active', true);

        // Size filter
        if ($request->filled('size')) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where('size', $request->input('size'))->where('stock', '>', 0);
            });
        }

        // Sale filter
        if ($request->filled('sale')) {
            $query->whereNotNull('sale_price');
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
        $availableSizes = ['S', 'M', 'L', 'XL', 'XXL'];

        return view('categories.show', compact('category', 'products', 'categories', 'availableSizes'));
    }
}
