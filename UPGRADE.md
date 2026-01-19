# Upgrade Guide

## Upgrading from v1.x (Nova 4) to v2.x (Nova 5)

This is a major release with breaking changes. Please review carefully before upgrading.

### Requirements Changes

| Requirement | v1.x (Nova 4) | v2.x (Nova 5) |
|-------------|---------------|---------------|
| PHP | ^8.0 \| ^8.1 | ^8.2+ |
| Laravel Nova | ^4.0 | ^5.0 |
| Laravel | 9.x / 10.x | 11.x / 12.x |
| Asset Bundler | Laravel Mix | Vite |
| Font Awesome | 5.x / 6.x | 6.x / 7.x |

### Breaking Changes

#### 1. Nova 4 No Longer Supported

This version only supports Laravel Nova 5. If you're still on Nova 4, please continue using v1.x:

```bash
composer require marshmallow/nova-fontawesome:^1.0
```

#### 2. PHP 8.1 and Earlier No Longer Supported

Minimum PHP version is now 8.2. Upgrade your PHP version before updating this package.

#### 3. Laravel Mix Replaced with Vite

The package now uses Vite instead of Laravel Mix for asset bundling. The `webpack.mix.js` file has been removed and replaced with `vite.config.js`.

**Before (v1.x):**
```bash
npm run dev      # Used Laravel Mix
npm run prod     # Used Laravel Mix
```

**After (v2.x):**
```bash
npm run dev      # Uses Vite
npm run build    # Uses Vite
npm run watch    # Uses Vite with --watch
```

#### 4. CSS Configuration Changed

The `pro_css` configuration section has been replaced with a new `css` section that supports multiple loading strategies.

**Before (v1.x config):**
```php
'pro_css' => [
    'kit_id' => env('FONTAWESOME_KIT_ID'),
    'css_url' => env('FONTAWESOME_PRO_CSS_URL'),
    'local_css' => env('FONTAWESOME_LOCAL_CSS'),
],
```

**After (v2.x config):**
```php
'css' => [
    'strategy' => env('FONTAWESOME_CSS_STRATEGY', 'self-hosted'),
    'path' => env('FONTAWESOME_CSS_PATH', '/vendor/fontawesome/css/all.min.css'),
    'kit_id' => env('FONTAWESOME_KIT_ID'),
    'cdn_version' => env('FONTAWESOME_CDN_VERSION', '6.5.1'),
],
```

The package maintains backwards compatibility with the old `pro_css` config, but you should migrate to the new format.

#### 5. Icon Class Format Updated

Icons are now stored in modern FA6/FA7 format. Legacy FA5 shorthand classes are automatically converted.

| Legacy Format | Modern Format |
|--------------|---------------|
| `fas fa-home` | `fa-solid fa-home` |
| `far fa-user` | `fa-regular fa-user` |
| `fab fa-github` | `fa-brands fa-github` |
| `fal fa-star` | `fa-light fa-star` |
| `fat fa-circle` | `fa-thin fa-circle` |
| `fad fa-house` | `fa-duotone fa-solid fa-house` |

Enable automatic conversion in config:
```php
'convert_legacy_format' => true, // default
```

#### 6. Modal Opens Empty (No Popular Icons)

The icon picker modal now opens with an empty state and a search prompt instead of showing popular icons. Users must search to find icons.

### New Features in v2.x

#### CSS Loading Strategy

Three strategies with automatic fallback:

1. **Self-hosted** (default) - Load from your server
2. **Kit** - Use Font Awesome Kit
3. **CDN** - Load from CDN (free icons only)

```env
FONTAWESOME_CSS_STRATEGY=self-hosted
FONTAWESOME_CSS_PATH=/vendor/fontawesome/css/all.min.css
```

#### Infinite Scroll Pagination

Icons are now loaded progressively as you scroll, instead of loading all at once.

#### Legacy Format Converter

New `IconClassConverter` service automatically converts old FA5 class formats to modern FA6/FA7 format.

#### API Error Handling

Improved error handling with user-friendly messages when Font Awesome API is unavailable.

#### New API Endpoints

- `GET /nova-vendor/nova-fontawesome/config` - Get CSS configuration
- `GET /nova-vendor/nova-fontawesome/convert` - Convert legacy class format

### Migration Steps

1. **Check PHP version** - Ensure you're running PHP 8.2 or higher:
   ```bash
   php -v
   ```

2. **Check Laravel version** - Ensure you're running Laravel 11.x or 12.x:
   ```bash
   php artisan --version
   ```

3. **Upgrade Nova** - Ensure you're running Nova 5.x

4. **Update package**:
   ```bash
   composer require marshmallow/nova-fontawesome:^2.0
   ```

5. **(Package maintainers only)** If you're developing the package locally, update npm dependencies:
   ```bash
   rm -rf node_modules package-lock.json bun.lock yarn.lock
   npm install && npm run build
   ```
   > **Note:** Regular users don't need to rebuild assets - they're pre-compiled in the package.

6. **Update config file** (if published):
   ```bash
   php artisan vendor:publish --tag=nova-fontawesome-config --force
   ```

7. **Update environment variables** (if using Kit or Pro):
   ```env
   # Old format (still works but deprecated)
   FONTAWESOME_KIT_ID=abc123

   # New format (recommended)
   FONTAWESOME_CSS_STRATEGY=kit
   FONTAWESOME_KIT_ID=abc123
   ```

8. **Clear caches**:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

### Configuration Migration

If you have a published config file, update it with the new structure:

```php
<?php

return [
    'version' => env('FONTAWESOME_VERSION', '6.x'),
    'cache_duration' => env('FONTAWESOME_CACHE_DURATION', 3600),
    'free_only' => env('FONTAWESOME_FREE_ONLY', true),
    'styles' => ['solid', 'regular', 'light', 'thin', 'duotone', 'brands'],
    'families' => ['classic', 'sharp', 'duotone', 'sharp-duotone', 'brands'],
    'max_results' => env('FONTAWESOME_MAX_RESULTS', 100),
    'api_token' => env('FONTAWESOME_API_TOKEN'),

    // NEW: CSS loading strategy
    'css' => [
        'strategy' => env('FONTAWESOME_CSS_STRATEGY', 'self-hosted'),
        'path' => env('FONTAWESOME_CSS_PATH', '/vendor/fontawesome/css/all.min.css'),
        'kit_id' => env('FONTAWESOME_KIT_ID'),
        'cdn_version' => env('FONTAWESOME_CDN_VERSION', '6.5.1'),
    ],

    'fuzzy_search' => [
        'enabled' => true,
        'threshold' => 0.3,
    ],

    // NEW: Auto-convert legacy FA5 classes
    'convert_legacy_format' => true,
];
```

### Need Help?

If you encounter issues during upgrade, please:
1. Check the [debug endpoint](/nova-vendor/nova-fontawesome/debug)
2. Review the [documentation](https://github.com/marshmallow-packages/nova-fontawesome)
3. Open an issue on GitHub
