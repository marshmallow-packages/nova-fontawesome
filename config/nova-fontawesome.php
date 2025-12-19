<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Font Awesome Version
    |--------------------------------------------------------------------------
    |
    | The default Font Awesome version to use when searching icons.
    | Use semantic versions like "6.x" or specific versions like "6.5.1".
    |
    */
    'version' => env('FONTAWESOME_VERSION', '6.x'),

    /*
    |--------------------------------------------------------------------------
    | Cache Duration
    |--------------------------------------------------------------------------
    |
    | How long to cache icon search results (in seconds).
    | Default: 3600 (1 hour)
    |
    */
    'cache_duration' => env('FONTAWESOME_CACHE_DURATION', 3600),

    /*
    |--------------------------------------------------------------------------
    | Free Icons Only
    |--------------------------------------------------------------------------
    |
    | By default, only free icons are shown. Set to false to include
    | Pro icons (requires Font Awesome Pro subscription).
    |
    */
    'free_only' => env('FONTAWESOME_FREE_ONLY', false),

    /*
    |--------------------------------------------------------------------------
    | Default Styles
    |--------------------------------------------------------------------------
    |
    | The icon styles to show by default.
    | Options: solid, regular, light, thin, duotone, brands
    |
    */
    'styles' => ['solid', 'regular', 'brands'],

    /*
    |--------------------------------------------------------------------------
    | Max Search Results
    |--------------------------------------------------------------------------
    |
    | Maximum number of icons to return in a search query.
    |
    */
    'max_results' => env('FONTAWESOME_MAX_RESULTS', 50),

    /*
    |--------------------------------------------------------------------------
    | API Token
    |--------------------------------------------------------------------------
    |
    | Optional Font Awesome API token for authenticated requests.
    | This is required if you want to access Pro icons.
    | Get your token from: https://fontawesome.com/account
    |
    */
    'api_token' => env('FONTAWESOME_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Backwards Compatibility - Legacy Configuration
    |--------------------------------------------------------------------------
    |
    | These settings are kept for backwards compatibility with previous versions.
    |
    */

    /**
     * These are the JavaScript files that will be loaded into the Nova application.
     * If you need to load more JS files or from a different location, you can add them here.
     * Please make sure the files are in the public directory.
     */
    'js' => [
        '/vendor/nova-fontawesome/all.min.js',
        // '/vendor/fontawesome/all.js',
        // add more if you need to...
    ],

    /**
     * If you have a Pro license, you can set this to true to load the Pro version of FontAwesome.
     * Make sure you download the Pro version when you add the JS files to the public directory.
     */
    'pro' => false,
];
