<?php
/**
 * @see https://github.com/artesaos/seotools
 */

return [
    'inertia' => env('SEO_TOOLS_INERTIA', true),
    'meta' => [
        /*
         * The default configurations to be used by the meta generator.
         */
        'defaults'       => [
            'title'        => env('APP_NAME', 'Suptix'), // set false to total remove
            'titleBefore'  => false, // Put defaults.title before page title, like 'Suptix - Dashboard'
            'description'  => 'Ghana\'s Premier Event Management and Ticketing Platform. Discover, book, and organize amazing events across Ghana.', // set false to total remove
            'separator'    => ' - ',
            'keywords'     => ['events', 'tickets', 'ghana', 'event management', 'ticketing platform', 'concerts', 'conferences', 'entertainment'],
            'canonical'    => 'current', // Set to null or 'full' to use Url::full(), set to 'current' to use Url::current(), set false to total remove
            'robots'       => 'index,follow', // Set to 'all', 'none' or any combination of index/noindex and follow/nofollow
        ],
        /*
         * Webmaster tags are always added.
         */
        'webmaster_tags' => [
            'google'    => null,
            'bing'      => null,
            'alexa'     => null,
            'pinterest' => null,
            'yandex'    => null,
            'norton'    => null,
        ],

        'add_notranslate_class' => false,
    ],
    'opengraph' => [
        /*
         * The default configurations to be used by the opengraph generator.
         */
        'defaults' => [
            'title'       => env('APP_NAME', 'Suptix'), // set false to total remove
            'description' => 'Ghana\'s Premier Event Management and Ticketing Platform. Discover, book, and organize amazing events across Ghana.', // set false to total remove
            'url'         => null, // Set null for using Url::current(), set false to total remove
            'type'        => 'website',
            'site_name'   => env('APP_NAME', 'Suptix'),
            'images'      => [],
        ],
    ],
    'twitter' => [
        /*
         * The default values to be used by the twitter cards generator.
         */
        'defaults' => [
            'card'        => 'summary_large_image',
            'site'        => '@suptle_social',
            'creator'     => '@suptle_social',
        ],
    ],
    'json-ld' => [
        /*
         * The default configurations to be used by the json-ld generator.
         */
        'defaults' => [
            'title'       => env('APP_NAME', 'Suptix'), // set false to total remove
            'description' => 'Ghana\'s Premier Event Management and Ticketing Platform. Discover, book, and organize amazing events across Ghana.', // set false to total remove
            'url'         => 'current', // Set to null or 'full' to use Url::full(), set to 'current' to use Url::current(), set false to total remove
            'type'        => 'WebSite',
            'images'      => [],
        ],
    ],
];
