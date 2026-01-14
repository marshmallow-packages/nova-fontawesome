# Upgrade Guide

## Upgrading from v1.x (Nova 4) to v2.x (Nova 5)

This is a major release with breaking changes. Please review carefully before upgrading.

### Requirements Changes

| Requirement | v1.x (Nova 4) | v2.x (Nova 5) |
|-------------|---------------|---------------|
| PHP | ^8.0 \| ^8.1 | ^8.1 \| ^8.2 \| ^8.3 |
| Laravel Nova | ^4.0 \| ^5.0 | ^5.0 |
| Laravel | 9.x / 10.x | 10.x / 11.x |

### Breaking Changes

#### 1. Nova 4 No Longer Supported

This version only supports Laravel Nova 5. If you're still on Nova 4, please continue using v1.x:

```bash
composer require marshmallow/nova-fontawesome:^1.0
```

#### 2. PHP 8.0 No Longer Supported

Minimum PHP version is now 8.1. Upgrade your PHP version before updating this package.

#### 3. Translations Loader Now Optional

The `outl1ne/nova-translations-loader` package is no longer a required dependency. If you need translation support:

```bash
composer require outl1ne/nova-translations-loader:^5.0
```

The package will automatically use it if installed, otherwise falls back to Laravel's built-in translation system.

### New Features in v2.x

#### Service Layer Architecture

API logic has been extracted into a dedicated service class for better testability and maintainability.

#### Fallback Icons

When the Font Awesome API is unavailable, the package now provides 40 common fallback icons for graceful degradation.

#### Debug Endpoint

New `/nova-vendor/nova-fontawesome/debug` endpoint for troubleshooting API integration issues.

#### Kit ID Support

Load Pro CSS using your Font Awesome Kit ID:

```php
NovaFontAwesome::make('Icon')
    ->kitId('your-kit-id');
```

Or via config:

```php
// config/nova-fontawesome.php
'pro_css' => [
    'kit_id' => env('FONTAWESOME_KIT_ID'),
],
```

#### Icon Families

Configure available icon families:

```php
NovaFontAwesome::make('Icon')
    ->families(['classic', 'brands', 'duotone', 'sharp']);
```

#### Client-Side Fuzzy Search

Instant local search results when API is slow:

```php
NovaFontAwesome::make('Icon')
    ->fuzzySearch(true)
    ->fuzzySearchThreshold(0.3);
```

#### IconRenderer Component

New Vue component for consistent icon rendering across your application.

### Configuration Changes

New configuration options have been added. Publish the updated config file:

```bash
php artisan vendor:publish --tag=nova-fontawesome-config --force
```

New options:
- `families` - Available icon families
- `pro_css.kit_id` - Font Awesome Kit ID
- `pro_css.css_url` - Custom Pro CSS URL
- `pro_css.local_css` - Self-hosted CSS path
- `fuzzy_search.enabled` - Enable/disable fuzzy search
- `fuzzy_search.threshold` - Fuzzy search sensitivity

### Migration Steps

1. **Check PHP version** - Ensure you're running PHP 8.1 or higher
2. **Upgrade Nova** - Ensure you're running Nova 5.x
3. **Update package**:
   ```bash
   composer require marshmallow/nova-fontawesome:^2.0
   ```
4. **Publish new config** (optional):
   ```bash
   php artisan vendor:publish --tag=nova-fontawesome-config --force
   ```
5. **Install translations loader** (if needed):
   ```bash
   composer require outl1ne/nova-translations-loader:^5.0
   ```
6. **Rebuild assets**:
   ```bash
   npm run build
   ```

### Need Help?

If you encounter issues during upgrade, please:
1. Check the [debug endpoint](/nova-vendor/nova-fontawesome/debug)
2. Review the [documentation](https://github.com/marshmallow-packages/nova-fontawesome)
3. Open an issue on GitHub
