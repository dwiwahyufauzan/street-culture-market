<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'title' => 'AUTUMN / WINTER 2026',
                'subtitle' => 'TACTICAL MONOCHROME ESSENTIALS',
                'image' => 'banners/hero-aw26.jpg',
                'link' => '/products',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'LIMITED DROP: ACID WASH SERIES',
                'subtitle' => 'PREMIUM 280GSM COMBD COTTON',
                'image' => 'banners/hero-acidwash.jpg',
                'link' => '/products/scm-acid-wash-distressed-graphic-tee',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'TACTICAL OUTERWEAR',
                'subtitle' => 'TECHNICAL FLIGHT BOMBERS & WEATHERPROOF NYLON',
                'image' => 'banners/hero-outerwear.jpg',
                'link' => '/category/jackets-outerwear',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($banners as $data) {
            Banner::updateOrCreate(['title' => $data['title']], $data);
        }
    }
}
