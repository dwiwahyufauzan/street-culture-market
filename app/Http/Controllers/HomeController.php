<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

class HomeController extends Controller
{
    /**
     * Display the storefront homepage.
     */
    public function index(): View
    {
        SEOMeta::setTitle('Street Culture Market — Curated Streetwear & Urban Apparel');
        SEOMeta::setDescription('Discover minimalist heavyweight streetwear, limited acid wash drops, and tactical accessories.');
        OpenGraph::setTitle('Street Culture Market — Curated Streetwear & Urban Apparel');
        OpenGraph::setDescription('Discover minimalist heavyweight streetwear, limited acid wash drops, and tactical accessories.');

        $banners = Banner::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $newArrivals = Product::with(['primaryImage', 'category'])
            ->where('is_active', true)
            ->latest()
            ->take(8)
            ->get();

        $featuredProducts = Product::with(['primaryImage', 'category'])
            ->where('is_featured', true)
            ->where('is_active', true)
            ->take(4)
            ->get();

        /** @var Collection<int, Product> $editorialProducts */
        $editorialProducts = Product::with(['primaryImage', 'category'])
            ->where('is_active', true)
            ->inRandomOrder()
            ->take(3)
            ->get();

        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        return view('home.index', compact(
            'banners',
            'newArrivals',
            'featuredProducts',
            'editorialProducts',
            'categories'
        ));
    }
}
