<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCrossSell;
use App\Models\ProductImage;
use App\Models\ProductUpsell;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all()->keyBy('slug');

        $productsData = [
            // T-Shirts
            [
                'category_slug' => 't-shirts-tops',
                'name' => 'SCM Classic Box Logo Tee',
                'slug' => 'scm-classic-box-logo-tee',
                'description' => 'Heavyweight 240gsm cotton t-shirt with classic minimalist screen-printed SCM box logo on the chest. Relaxed streetwear fit designed for everyday endurance.',
                'price' => 220000,
                'sale_price' => null,
                'sku' => 'SCM-TEE-001',
                'weight' => 280,
                'is_featured' => true,
                'variants' => [
                    ['size' => 'S', 'color' => 'Black', 'stock' => 15],
                    ['size' => 'M', 'color' => 'Black', 'stock' => 25],
                    ['size' => 'L', 'color' => 'Black', 'stock' => 30],
                    ['size' => 'XL', 'color' => 'Black', 'stock' => 10],
                    ['size' => 'M', 'color' => 'White', 'stock' => 20],
                    ['size' => 'L', 'color' => 'White', 'stock' => 20],
                ],
                'image' => 'products/tee-box-logo.jpg',
            ],
            [
                'category_slug' => 't-shirts-tops',
                'name' => 'SCM Acid Wash Distressed Graphic Tee',
                'slug' => 'scm-acid-wash-distressed-graphic-tee',
                'description' => 'Premium 280gsm Japanese combed cotton with hand-treated acid wash finish and vintage puff print graphic. Higher density and exclusive artisanal distressing.',
                'price' => 350000,
                'sale_price' => 315000,
                'sku' => 'SCM-TEE-002',
                'weight' => 320,
                'is_featured' => true,
                'variants' => [
                    ['size' => 'S', 'color' => 'Washed Charcoal', 'stock' => 8],
                    ['size' => 'M', 'color' => 'Washed Charcoal', 'stock' => 15],
                    ['size' => 'L', 'color' => 'Washed Charcoal', 'stock' => 12],
                    ['size' => 'XL', 'color' => 'Washed Charcoal', 'stock' => 6],
                ],
                'image' => 'products/tee-acid-wash.jpg',
            ],

            // Hoodies
            [
                'category_slug' => 'hoodies-sweats',
                'name' => 'SCM Essential Heavyweight Hoodie',
                'slug' => 'scm-essential-heavyweight-hoodie',
                'description' => '380gsm brushed fleece hoodie with double-layered hood, kangaroo pocket, and tonal embroidery. Clean silhouette inspired by modern Tokyo streetwear.',
                'price' => 450000,
                'sale_price' => null,
                'sku' => 'SCM-HD-001',
                'weight' => 750,
                'is_featured' => true,
                'variants' => [
                    ['size' => 'M', 'color' => 'Black', 'stock' => 18],
                    ['size' => 'L', 'color' => 'Black', 'stock' => 22],
                    ['size' => 'XL', 'color' => 'Black', 'stock' => 15],
                    ['size' => 'L', 'color' => 'Heather Grey', 'stock' => 12],
                ],
                'image' => 'products/hoodie-essential.jpg',
            ],
            [
                'category_slug' => 'hoodies-sweats',
                'name' => 'SCM Luxe Loopback French Terry Oversized Hoodie',
                'slug' => 'scm-luxe-loopback-french-terry-oversized-hoodie',
                'description' => 'Ultra-heavyweight 500gsm French Terry loopback cotton. Pre-shrunk, drop shoulder silhouette with custom matte black metal hardware and interior stash pocket.',
                'price' => 680000,
                'sale_price' => 599000,
                'sku' => 'SCM-HD-002',
                'weight' => 950,
                'is_featured' => true,
                'variants' => [
                    ['size' => 'M', 'color' => 'Onyx Black', 'stock' => 10],
                    ['size' => 'L', 'color' => 'Onyx Black', 'stock' => 15],
                    ['size' => 'XL', 'color' => 'Onyx Black', 'stock' => 8],
                ],
                'image' => 'products/hoodie-luxe.jpg',
            ],

            // Jackets
            [
                'category_slug' => 'jackets-outerwear',
                'name' => 'SCM Lightweight Nylon Windbreaker',
                'slug' => 'scm-lightweight-nylon-windbreaker',
                'description' => 'Packable water-resistant nylon jacket with breathable mesh lining and adjustable bungee cords. The ultimate lightweight urban commuter outer layer.',
                'price' => 380000,
                'sale_price' => null,
                'sku' => 'SCM-JK-001',
                'weight' => 450,
                'is_featured' => false,
                'variants' => [
                    ['size' => 'M', 'color' => 'Black', 'stock' => 10],
                    ['size' => 'L', 'color' => 'Black', 'stock' => 15],
                    ['size' => 'XL', 'color' => 'Black', 'stock' => 8],
                ],
                'image' => 'products/jacket-windbreaker.jpg',
            ],
            [
                'category_slug' => 'jackets-outerwear',
                'name' => 'SCM Technical Tactical MA-1 Bomber',
                'slug' => 'scm-technical-tactical-ma1-bomber',
                'description' => 'Heavy flight nylon with PrimaLoft thermal insulation, multi-pocket utility sleeves, magnetic buckle closures, and emergency safety orange interior lining.',
                'price' => 850000,
                'sale_price' => 780000,
                'sku' => 'SCM-JK-002',
                'weight' => 1100,
                'is_featured' => true,
                'variants' => [
                    ['size' => 'M', 'color' => 'Olive Green', 'stock' => 7],
                    ['size' => 'L', 'color' => 'Olive Green', 'stock' => 12],
                    ['size' => 'XL', 'color' => 'Black', 'stock' => 5],
                ],
                'image' => 'products/jacket-tactical.jpg',
            ],

            // Pants
            [
                'category_slug' => 'pants-cargo',
                'name' => 'SCM Multi-Pocket Relaxed Cargo Pants',
                'slug' => 'scm-multi-pocket-relaxed-cargo-pants',
                'description' => 'Heavy cotton ripstop cargo trousers featuring 8 tactical 3D utility pockets, adjustable ankle drawstrings, and articulated knee darts for mobility.',
                'price' => 420000,
                'sale_price' => null,
                'sku' => 'SCM-PNT-001',
                'weight' => 600,
                'is_featured' => true,
                'variants' => [
                    ['size' => 'S (28-30)', 'color' => 'Charcoal Grey', 'stock' => 10],
                    ['size' => 'M (31-33)', 'color' => 'Charcoal Grey', 'stock' => 18],
                    ['size' => 'L (34-36)', 'color' => 'Charcoal Grey', 'stock' => 14],
                    ['size' => 'M (31-33)', 'color' => 'Black', 'stock' => 20],
                ],
                'image' => 'products/pants-cargo.jpg',
            ],

            // Caps
            [
                'category_slug' => 'headwear-caps',
                'name' => 'SCM Unstructured 5-Panel Camp Cap',
                'slug' => 'scm-unstructured-5-panel-camp-cap',
                'description' => 'Low-profile water-repellent nylon camp cap with webbing strap and buckle closure. High-density woven SCM branding label.',
                'price' => 180000,
                'sale_price' => null,
                'sku' => 'SCM-CAP-001',
                'weight' => 120,
                'is_featured' => false,
                'variants' => [
                    ['size' => 'All Size', 'color' => 'Black', 'stock' => 30],
                    ['size' => 'All Size', 'color' => 'Olive', 'stock' => 20],
                ],
                'image' => 'products/cap-5panel.jpg',
            ],

            // Accessories
            [
                'category_slug' => 'accessories',
                'name' => 'SCM Cordura Tactical Crossbody Bag',
                'slug' => 'scm-cordura-tactical-crossbody-bag',
                'description' => 'Abrasion-resistant Cordura 500D nylon sling bag with Fidlock magnetic buckle, waterproof YKK zippers, and modular molle attachment points.',
                'price' => 260000,
                'sale_price' => null,
                'sku' => 'SCM-ACC-001',
                'weight' => 250,
                'is_featured' => true,
                'variants' => [
                    ['size' => 'One Size', 'color' => 'Matte Black', 'stock' => 25],
                ],
                'image' => 'products/acc-crossbody.jpg',
            ],
        ];

        $createdProducts = [];

        foreach ($productsData as $data) {
            $category = $categories->get($data['category_slug']);

            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id' => $category?->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'sale_price' => $data['sale_price'],
                    'sku' => $data['sku'],
                    'weight' => $data['weight'],
                    'is_active' => true,
                    'is_featured' => $data['is_featured'],
                    'meta_title' => $data['name'].' — Street Culture Market',
                    'meta_description' => Str::limit($data['description'], 150),
                ]
            );

            // Create Variants
            foreach ($data['variants'] as $v) {
                ProductVariant::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'size' => $v['size'],
                        'color' => $v['color'],
                    ],
                    [
                        'stock' => $v['stock'],
                        'sku' => $product->sku.'-'.strtoupper(Str::slug($v['size'].'-'.$v['color'])),
                    ]
                );
            }

            // Create Primary Image
            ProductImage::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'is_primary' => true,
                ],
                [
                    'image_path' => $data['image'],
                    'alt_text' => $product->name,
                    'sort_order' => 0,
                ]
            );

            $createdProducts[$product->slug] = $product;
        }

        // Setup UPSELLING Relationships
        // 1. Classic Box Tee -> Acid Wash Graphic Tee (Higher tier: Rp 220k -> Rp 350k)
        if (isset($createdProducts['scm-classic-box-logo-tee'], $createdProducts['scm-acid-wash-distressed-graphic-tee'])) {
            ProductUpsell::updateOrCreate([
                'product_id' => $createdProducts['scm-classic-box-logo-tee']->id,
                'upsell_id' => $createdProducts['scm-acid-wash-distressed-graphic-tee']->id,
            ], ['sort_order' => 1]);
        }

        // 2. Essential Hoodie -> Luxe French Terry Hoodie (Higher tier: Rp 450k -> Rp 680k)
        if (isset($createdProducts['scm-essential-heavyweight-hoodie'], $createdProducts['scm-luxe-loopback-french-terry-oversized-hoodie'])) {
            ProductUpsell::updateOrCreate([
                'product_id' => $createdProducts['scm-essential-heavyweight-hoodie']->id,
                'upsell_id' => $createdProducts['scm-luxe-loopback-french-terry-oversized-hoodie']->id,
            ], ['sort_order' => 1]);
        }

        // 3. Nylon Windbreaker -> Tactical MA-1 Bomber (Higher tier: Rp 380k -> Rp 850k)
        if (isset($createdProducts['scm-lightweight-nylon-windbreaker'], $createdProducts['scm-technical-tactical-ma1-bomber'])) {
            ProductUpsell::updateOrCreate([
                'product_id' => $createdProducts['scm-lightweight-nylon-windbreaker']->id,
                'upsell_id' => $createdProducts['scm-technical-tactical-ma1-bomber']->id,
            ], ['sort_order' => 1]);
        }

        // Setup CROSS-SELLING Relationships
        // 1. Classic Box Tee -> Cargo Pants & 5-Panel Cap
        if (isset($createdProducts['scm-classic-box-logo-tee'], $createdProducts['scm-multi-pocket-relaxed-cargo-pants'])) {
            ProductCrossSell::updateOrCreate([
                'product_id' => $createdProducts['scm-classic-box-logo-tee']->id,
                'cross_sell_id' => $createdProducts['scm-multi-pocket-relaxed-cargo-pants']->id,
            ], ['sort_order' => 1]);
        }
        if (isset($createdProducts['scm-classic-box-logo-tee'], $createdProducts['scm-unstructured-5-panel-camp-cap'])) {
            ProductCrossSell::updateOrCreate([
                'product_id' => $createdProducts['scm-classic-box-logo-tee']->id,
                'cross_sell_id' => $createdProducts['scm-unstructured-5-panel-camp-cap']->id,
            ], ['sort_order' => 2]);
        }

        // 2. Essential Hoodie -> Cargo Pants & Crossbody Bag
        if (isset($createdProducts['scm-essential-heavyweight-hoodie'], $createdProducts['scm-multi-pocket-relaxed-cargo-pants'])) {
            ProductCrossSell::updateOrCreate([
                'product_id' => $createdProducts['scm-essential-heavyweight-hoodie']->id,
                'cross_sell_id' => $createdProducts['scm-multi-pocket-relaxed-cargo-pants']->id,
            ], ['sort_order' => 1]);
        }
        if (isset($createdProducts['scm-essential-heavyweight-hoodie'], $createdProducts['scm-cordura-tactical-crossbody-bag'])) {
            ProductCrossSell::updateOrCreate([
                'product_id' => $createdProducts['scm-essential-heavyweight-hoodie']->id,
                'cross_sell_id' => $createdProducts['scm-cordura-tactical-crossbody-bag']->id,
            ], ['sort_order' => 2]);
        }
    }
}
