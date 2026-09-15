<?php

/**
 * @see https://github.com/artesaos/seotools
 */

return [
    'inertia' => env('SEO_TOOLS_INERTIA', false),
    'meta' => [
        /*
         * The default configurations to be used by the meta generator.
         */
        'defaults' => [
            'title' => 'Street Culture Market', // set false to total remove
            'titleBefore' => false, // Put defaults.title before page title, like 'Street Culture Market - Products'
            'description' => 'Platform E-Commerce Streetwear & Urban Culture Terkurasi', // set false to total remove
            'separator' => ' — ',
            'keywords' => ['streetwear', 'fashion', 'street culture', 'sneakers', 'apparel', 'indonesia'],
            'canonical' => false, // Set to null or 'full' to use Url::full(), set to 'current' to use Url::current(), set false to total remove
            'robots' => false, // Set to 'all', 'none' or any combination of index/noindex and follow/nofollow
        ],
        /*
         * Webmaster tags are always added.
         */
        'webmaster_tags' => [
            'google' => null,
            'bing' => null,
            'alexa' => null,
            'pinterest' => null,
            'yandex' => null,
            'norton' => null,
        ],

        'add_notranslate_class' => false,
    ],
    'opengraph' => [
        /*
         * The default configurations to be used by the opengraph generator.
         */
        'defaults' => [
            'title' => 'Street Culture Market', // set false to total remove
            'description' => 'Platform E-Commerce Streetwear & Urban Culture Terkurasi', // set false to total remove
            'url' => false, // Set null for using Url::current(), set false to total remove
            'type' => 'website',
            'site_name' => 'Street Culture Market',
            'images' => [],
        ],
    ],
    'twitter' => [
        /*
         * The default values to be used by the twitter cards generator.
         */
        'defaults' => [
            // 'card'        => 'summary_large_image',
            // 'site'        => '@streetculturemarket',
        ],
    ],
    'json-ld' => [
        /*
         * The default configurations to be used by the json-ld generator.
         */
        'defaults' => [
            'title' => 'Street Culture Market', // set false to total remove
            'description' => 'Platform E-Commerce Streetwear & Urban Culture Terkurasi', // set false to total remove
            'url' => false, // Set to null or 'full' to use Url::full(), set to 'current' to use Url::current(), set false to total remove
            'type' => 'WebPage',
            'images' => [],
        ],
    ],
];
