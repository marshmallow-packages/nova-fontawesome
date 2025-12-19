[![Latest Version on Packagist](https://img.shields.io/packagist/v/marshmallow/nova-fontawesome.svg?style=flat-square)](https://packagist.org/packages/marshmallow/nova-fontawesome)
[![Total Downloads](https://img.shields.io/packagist/dt/marshmallow/nova-fontawesome.svg?style=flat-square)](https://packagist.org/packages/marshmallow/nova-fontawesome)

# Laravel Nova Font Awesome Icons

A Laravel Nova field for selecting Font Awesome icons using the Font Awesome GraphQL API. This package loads icons **on-demand** via API calls, meaning you don't need to bundle all 30,000+ icons in your package.

## Features

- 🔍 **Smart search** - Uses Font Awesome's Algolia-powered fuzzy search
- 🚀 **On-demand loading** - Icons are fetched via API, not bundled
- 🎨 **Style filtering** - Filter by solid, regular, brands, light, thin, duotone
- 💾 **Caching** - API results are cached to reduce requests
- 🌙 **Dark mode** - Full support for Nova's dark mode
- ⚡ **Debounced search** - Prevents excessive API calls
- 📱 **Responsive** - Works great on all screen sizes
- 🔄 **Backwards compatible** - Supports both new Graph API and legacy package-based approach

> [!important]
> This package was originally forked from [mdixon18/fontawesome](https://github.com/mdixon18/fontawesome). Since we were making many opinionated changes, we decided to continue development in our own version rather than submitting pull requests that might not benefit all users of the original package. You're welcome to use this package, we're actively maintaining it. If you encounter any issues, please don't hesitate to reach out.

## Requirements

- `php: ^8.0|^8.1`
- `laravel/nova: ^4.0|^5.0`
- Laravel 10.0+ or 11.0+

## Installation

Install via Composer:

```bash
composer require marshmallow/nova-fontawesome
```

Publish the config file (optional):

```bash
php artisan vendor:publish --tag=nova-fontawesome-config
```

Build the assets:

```bash
npm install && npm run prod
```

That's it! The package now uses the Font Awesome GraphQL API, so you don't need to manually download or include Font Awesome JavaScript files.

## Usage

### Basic Usage

```php
use Marshmallow\NovaFontAwesome\NovaFontAwesome;

public function fields(NovaRequest $request): array
{
    return [
        ID::make()->sortable(),

        NovaFontAwesome::make('Icon'),
    ];
}
```

### Available Methods

#### Set Font Awesome Version

```php
NovaFontAwesome::make('Icon')
    ->version('6.x') // or specific like '6.5.1'
```

#### Limit Icon Styles

```php
NovaFontAwesome::make('Icon')
    ->styles(['solid', 'brands']) // Only show solid and brand icons
```

Available styles: `solid`, `regular`, `light`, `thin`, `duotone`, `brands`

#### Free vs Pro Icons

```php
// Only free icons (default)
NovaFontAwesome::make('Icon')
    ->freeOnly()

// Include Pro icons (requires FA Pro subscription and API token)
NovaFontAwesome::make('Icon')
    ->includePro()
```

> **Note:** To access Pro icons, you need to:
> 1. Have an active Font Awesome Pro subscription
> 2. Get your API token from [Font Awesome Account Settings](https://fontawesome.com/account)
> 3. Add the token to your `.env` file: `FONTAWESOME_API_TOKEN=your-token-here`

#### Allow Empty/Null Values

```php
NovaFontAwesome::make('Icon')
    ->nullable()
```

#### Custom Button Text

```php
NovaFontAwesome::make('Icon')
    ->addButtonText('Click Me!')
```

#### Limit Search Results

```php
NovaFontAwesome::make('Icon')
    ->maxResults(100)
```

#### Minimum Search Length

```php
NovaFontAwesome::make('Icon')
    ->minSearchLength(3) // Require 3 characters before searching
```

#### Set Default Icon

```php
NovaFontAwesome::make('Icon')
    ->defaultIcon('far', 'check-circle')
```

#### Persist Default Icon

If you want to persist the default icon (when they press clear it brings back the default so it can't be empty):

```php
NovaFontAwesome::make('Icon')
    ->defaultIcon('far', 'check-circle')
    ->persistDefaultIcon()
```

#### Limit Available Icons

```php
NovaFontAwesome::make('Icon')
    ->only([
        'facebook',
        'twitch',
        'twitter',
    ])
```

### Complete Example

```php
use Marshmallow\NovaFontAwesome\NovaFontAwesome;

public function fields(NovaRequest $request): array
{
    return [
        ID::make()->sortable(),

        Text::make('Name'),

        NovaFontAwesome::make('Icon')
            ->version('6.x')
            ->styles(['solid', 'regular', 'brands'])
            ->freeOnly()
            ->nullable()
            ->addButtonText('Choose an icon...')
            ->maxResults(50)
            ->minSearchLength(2)
            ->help('Select an icon to represent this item'),
    ];
}
```

## How It Works

This package uses the [Font Awesome GraphQL API](https://docs.fontawesome.com/apis/graphql) to:

1. **Search icons** - When you type in the search box, a GraphQL query is sent to Font Awesome's API
2. **Fetch SVGs** - The API returns icon metadata including SVG data for rendering
3. **Cache results** - Search results are cached for 1 hour to reduce API calls

### API Endpoints

The package registers three API routes:

- `GET /nova-vendor/nova-fontawesome/search` - Search icons
- `GET /nova-vendor/nova-fontawesome/icon/{name}` - Get a specific icon
- `GET /nova-vendor/nova-fontawesome/popular` - Get popular icons for initial display

## Configuration

After publishing the config file, you can modify `config/nova-fontawesome.php`:

```php
return [
    // Default Font Awesome version
    'version' => env('FONTAWESOME_VERSION', '6.x'),

    // Cache duration in seconds (default: 1 hour)
    'cache_duration' => env('FONTAWESOME_CACHE_DURATION', 3600),

    // Only show free icons by default
    'free_only' => env('FONTAWESOME_FREE_ONLY', true),

    // Default styles to show
    'styles' => ['solid', 'regular', 'brands'],

    // Maximum search results
    'max_results' => env('FONTAWESOME_MAX_RESULTS', 50),

    // Optional API token for authenticated requests (required for Pro icons)
    'api_token' => env('FONTAWESOME_API_TOKEN'),

    // Legacy configuration for backwards compatibility
    'js' => [
        '/vendor/fontawesome/all.js',
        // add more if you need to...
    ],
    'pro' => false,
];
```

### Environment Variables

Add these to your `.env` file to customize the configuration:

```env
FONTAWESOME_VERSION=6.x
FONTAWESOME_CACHE_DURATION=3600
FONTAWESOME_FREE_ONLY=true
FONTAWESOME_MAX_RESULTS=50
# Only needed if you want to access Pro icons
FONTAWESOME_API_TOKEN=your-api-token-here
```

## Displaying Icons

### Stored Value Format

The field stores the complete Font Awesome class string:

```
"fa-solid fa-user"
"fa-regular fa-arrow-right"
"fa-brands fa-github"
```

### In Blade Views

```html
<!-- Direct usage -->
<i class="{{ $model->icon }}"></i>

<!-- Or with Font Awesome Kit -->
<i class="fa-solid fa-{{ $iconName }}"></i>
```

### Helper Method (Optional)

You can add a helper to your model:

```php
public function getIconHtmlAttribute(): string
{
    if (!$this->icon) {
        return '';
    }

    return sprintf('<i class="%s"></i>', e($this->icon));
}
```

## Backwards Compatibility

This package maintains backwards compatibility with the legacy package-based approach. If you were using the old method where you manually added FontAwesome JavaScript files to your public directory, that will continue to work. However, we recommend migrating to the new Graph API approach as it:

- Doesn't require manual JS file management
- Loads icons on-demand (smaller bundle size)
- Always has the latest icons
- Provides better search functionality

### Migrating from Legacy Approach

1. Remove the manual Font Awesome JavaScript files from your `public/vendor/fontawesome` directory (optional)
2. Update your config file to use the new Graph API settings
3. Run `npm install && npm run prod` to rebuild assets
4. Clear your application cache

The stored icon values remain compatible, so you don't need to update your database.

## Troubleshooting

### Icons not loading

1. Check that your server can reach `api.fontawesome.com`
2. Check Laravel logs for API errors
3. Clear the cache: `php artisan cache:clear`

### Styles not filtering correctly

Make sure you're using valid style names: `solid`, `regular`, `light`, `thin`, `duotone`, `brands`

### Pro icons not showing

Pro icons require a Font Awesome Pro subscription. The API will only return icons you have access to based on your subscription level.

## Licence

The MIT License (MIT). Please see [License File](LICENCE) for more information.

## 💖 Sponsorships

If you are reliant on this package in your production applications, consider [sponsoring us](https://github.com/sponsors/marshmallow-packages)! It is the best way to help us keep doing what we love to do: making great open source software.

## Contributing

Feel free to suggest changes, ask for new features or fix bugs yourself. We're sure there are still a lot of improvements that could be made, and we would be very happy to merge useful pull requests.

### Special thanks to
-   [All Contributors](../../contributors)
-   [mdixon18](https://github.com/mdixon18/fontawesome)
-   [duckzland](https://github.com/duckzland/fontawesome)

## Made with ❤️ for open source

At [Marshmallow](https://marshmallow.nl) we use a lot of open source software as part of our daily work.
So when we have an opportunity to give something back, we're super excited!

We hope you will enjoy this small contribution from us and would love to [hear from you](mailto:hello@marshmallow.nl) if you find it useful in your projects. Follow us on [Twitter](https://x.com/marshmallow_dev) for more updates!
