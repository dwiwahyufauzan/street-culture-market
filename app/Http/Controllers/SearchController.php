<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Display search results using Laravel Scout.
     */
    public function index(Request $request): View
    {
        $query = trim((string) $request->input('q', ''));
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $availableSizes = ['S', 'M', 'L', 'XL', 'XXL'];

        if (empty($query)) {
            SEOMeta::setTitle('Search Catalog — Street Culture Market');
            SEOMeta::setDescription('Search our curated archive of streetwear garments, tees, hoodies, and cargo.');
            OpenGraph::setTitle('Search Catalog — Street Culture Market');

            $products = Product::whereRaw('1 = 0')->paginate(16)->withQueryString();

            return view('search.index', compact('products', 'query', 'categories', 'availableSizes'));
        }

        SEOMeta::setTitle("Search: \"{$query}\" — Street Culture Market");
        SEOMeta::setDescription("Search results for \"{$query}\" at Street Culture Market.");
        OpenGraph::setTitle("Search: \"{$query}\" — Street Culture Market");

        $search = Product::search($query)
            ->where('is_active', 1)
            ->query(function ($builder) use ($request) {
                $builder->with(['primaryImage', 'category', 'variants']);

                if ($request->filled('category')) {
                    $builder->whereHas('category', fn ($q) => $q->where('slug', $request->input('category')));
                }

                if ($request->filled('size')) {
                    $builder->whereHas('variants', fn ($q) => $q->where('size', $request->input('size'))->where('stock', '>', 0));
                }

                match ($request->input('sort')) {
                    'price_asc' => $builder->orderBy('price', 'asc'),
                    'price_desc' => $builder->orderBy('price', 'desc'),
                    'oldest' => $builder->oldest(),
                    default => $builder->latest(),
                };
            });

        $products = $search->paginate(16)->withQueryString();

        return view('search.index', compact('products', 'query', 'categories', 'availableSizes'));
    }

    /**
     * Provide real-time autocomplete suggestions for instant search.
     */
    public function suggest(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = Product::search($query)
            ->where('is_active', 1)
            ->query(fn ($builder) => $builder->with(['primaryImage', 'category']))
            ->take(6)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'category' => $product->category?->name,
                'price' => (float) ($product->sale_price ?? $product->price),
                'formatted_price' => 'Rp '.number_format($product->sale_price ?? $product->price, 0, ',', '.'),
                'image' => $product->primaryImage?->image_path,
            ]);

        return response()->json($suggestions);
    }
}
