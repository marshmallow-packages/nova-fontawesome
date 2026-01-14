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
    | Pro icons (requires Font Awesome Pro subscription).
    |
    */
    'free_only' => env('FONTAWESOME_FREE_ONLY', true),

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
    | Available Icon Families
    |--------------------------------------------------------------------------
    |
    | The icon families available for selection.
    | Options: classic, brands, duotone, sharp, sharp-duotone
    | Note: duotone, sharp, and sharp-duotone require Font Awesome Pro.
    |
    */
    'families' => ['classic', 'brands'],

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
    | Pro CSS Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how to load Font Awesome Pro CSS. You can use a Kit ID from
    | your Font Awesome account, provide a direct CSS URL, or use self-hosted CSS.
    |
    */
    'pro_css' => [
        // Option 1: Use a Font Awesome Kit (recommended for Pro users)
        // Get your Kit ID from https://fontawesome.com/kits
        'kit_id' => env('FONTAWESOME_KIT_ID'),

        // Option 2: Direct CSS URL
        // Example: 'https://pro.fontawesome.com/releases/v6.5.0/css/all.css'
        'css_url' => env('FONTAWESOME_PRO_CSS_URL'),

        // Option 3: Self-hosted CSS path (relative to public folder)
        // Example: 'css/fontawesome-pro.css'
        'local_css' => env('FONTAWESOME_LOCAL_CSS'),
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
];
