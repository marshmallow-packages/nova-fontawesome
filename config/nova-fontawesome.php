<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Font Awesome Version
    |--------------------------------------------------------------------------
    |
    | The default Font Awesome version to use when searching icons.
    | Supports both 6.x and 7.x versions.
    | Use semantic versions like "7.x" or specific versions like "7.0.1".
    |
    */
    'version' => env('FONTAWESOME_VERSION', '7.0.1'),

    /*
    |--------------------------------------------------------------------------
    | Cache Duration
    |--------------------------------------------------------------------------
    |
    | How long to cache icon search results (in seconds).
    | Set to 0 to disable caching entirely.
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
    | Pro icons (requires Font Awesome Pro subscription and API token).
    |
    */
    'free_only' => env('FONTAWESOME_FREE_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Default Styles
    |--------------------------------------------------------------------------
    |
    | The icon styles to show by default.
    | Free options: solid, regular, brands
    | Pro options: light, thin, duotone (require Font Awesome Pro)
    |
    */
    'styles' => ['solid', 'regular', 'brands'],

    /*
    |--------------------------------------------------------------------------
    | Available Icon Families
    |--------------------------------------------------------------------------
    |
    | The icon families available for selection.
    | Free options: classic, brands
    | Pro options: duotone, sharp, sharp-duotone (require Font Awesome Pro)
    |
    */
    'families' => ['classic', 'brands'],

    /*
    |--------------------------------------------------------------------------
    | Max Search Results
    |--------------------------------------------------------------------------
    |
    | Maximum number of icons to return per search request (per page).
    | Used for pagination - more results will be loaded as user scrolls.
    | Recommended: 100 for optimal performance with infinite scroll.
    |
    */
    'max_results' => env('FONTAWESOME_MAX_RESULTS', 100),

    /*
    |--------------------------------------------------------------------------
    | API Token
    |--------------------------------------------------------------------------
    |
    | Optional Font Awesome API token for authenticated requests.
    | This is required if you want to access Pro icons via the GraphQL API.
    | Get your token from: https://fontawesome.com/account
    |
    */
    'api_token' => env('FONTAWESOME_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | CSS Loading Strategy
    |--------------------------------------------------------------------------
    |
    | How Font Awesome CSS should be loaded for icon rendering.
    |
    | Priority Order:
    | 1. 'self-hosted' (default) - Load CSS from your own server
    | 2. 'kit' - Use a Font Awesome Kit (requires kit_id)
    | 3. 'cdn' - Load free icons from Font Awesome CDN (fallback)
    |
    | Strategies:
    | - 'self-hosted': Best for Pro users. Download CSS from fontawesome.com
    |                  and place in public/vendor/fontawesome/
    | - 'kit': Use Font Awesome Kit for automatic Pro CSS loading
    | - 'cdn': Free icons only via CDN (no Pro support)
    |
    */
    'css' => [
        'strategy' => env('FONTAWESOME_CSS_STRATEGY', 'self-hosted'),

        // Path to self-hosted CSS (relative to public folder)
        // Default: /vendor/fontawesome/css/all.min.css
        'path' => env('FONTAWESOME_CSS_PATH', '/vendor/fontawesome/css/all.min.css'),

        // Base path for individual CSS files (relative to public folder)
        // Used by @fontawesome Blade directive for loading individual styles
        'base_path' => env('FONTAWESOME_CSS_BASE_PATH', 'vendor/fontawesome/css'),

        // Path for webfonts (relative to public folder)
        'webfonts_path' => env('FONTAWESOME_WEBFONTS_PATH', 'vendor/fontawesome/webfonts'),

        // Vite path for CSS files (relative to resources folder)
        // Used when loading FontAwesome via Vite instead of public folder
        'vite_path' => env('FONTAWESOME_VITE_PATH', 'resources/css/fontawesome'),

        // Font Awesome Kit ID (only used if strategy = 'kit')
        // Get your Kit ID from https://fontawesome.com/kits
        'kit_id' => env('FONTAWESOME_KIT_ID'),

        // CDN version to use when strategy = 'cdn'
        'cdn_version' => env('FONTAWESOME_CDN_VERSION', '7.0.1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Client-Side Fuzzy Search
    |--------------------------------------------------------------------------
    |
    | Enable client-side fuzzy search as a fallback when the API is slow
    | or unavailable. This provides instant results for common icons.
    |
    */
    'fuzzy_search' => [
        'enabled' => true,
        'threshold' => 0.3, // 0-1, lower = stricter matching
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy Format Conversion
    |--------------------------------------------------------------------------
    |
    | Automatically convert legacy FA5 shorthand classes (fas, far, fab, etc.)
    | to modern FA6/FA7 format when saving. This ensures consistency and
    | future compatibility.
    |
    */
    'convert_legacy_format' => true,
];
