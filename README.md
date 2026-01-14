[![Latest Version on Packagist](https://img.shields.io/packagist/v/marshmallow/nova-fontawesome.svg?style=flat-square)](https://packagist.org/packages/marshmallow/nova-fontawesome)
[![Total Downloads](https://img.shields.io/packagist/dt/marshmallow/nova-fontawesome.svg?style=flat-square)](https://packagist.org/packages/marshmallow/nova-fontawesome)
[![CI](https://github.com/marshmallow-packages/nova-fontawesome/actions/workflows/ci.yml/badge.svg)](https://github.com/marshmallow-packages/nova-fontawesome/actions/workflows/ci.yml)

# Laravel Nova Font Awesome Icons

A Laravel Nova field for selecting Font Awesome icons using the Font Awesome GraphQL API. This package loads icons **on-demand** via API calls, meaning you don't need to bundle all 30,000+ icons in your package.

## Features

- 🔍 **Smart search** - Uses Font Awesome's Algolia-powered fuzzy search
- 🚀 **On-demand loading** - Icons are fetched via API, not bundled
- 🎨 **Style filtering** - Filter by solid, regular, brands, light, thin, duotone
- 👨‍👩‍👧‍👦 **Family support** - Classic, Brands, Duotone, Sharp, Sharp-Duotone
- 💾 **Caching** - API results are cached to reduce requests
- 🌙 **Dark mode** - Full support for Nova's dark mode
- ⚡ **Debounced search** - Prevents excessive API calls
- 📱 **Responsive** - Works great on all screen sizes
- 🔄 **Fallback icons** - Graceful degradation when API is unavailable
- 🔎 **Local fuzzy search** - Instant results for common icons
- 🐛 **Debug endpoint** - Built-in troubleshooting tools

> [!important]
> This package was originally forked from [mdixon18/fontawesome](https://github.com/mdixon18/fontawesome). Since we were making many opinionated changes, we decided to continue development in our own version rather than submitting pull requests that might not benefit all users of the original package. You're welcome to use this package, we're actively maintaining it. If you encounter any issues, please don't hesitate to reach out.

## Requirements

- PHP ^8.1 | ^8.2 | ^8.3
- Laravel Nova ^5.0
- Laravel 10.x or 11.x

> **Note:** For Nova 4 support, use version 1.x: `composer require marshmallow/nova-fontawesome:^1.0`

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
npm install && npm run build
```

That's it! The package uses the Font Awesome GraphQL API, so you don't need to manually download or include Font Awesome files.

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

#### Set Icon Families

```php
NovaFontAwesome::make('Icon')
    ->families(['classic', 'brands', 'duotone', 'sharp'])
```

Available families: `classic`, `brands`, `duotone`, `sharp`, `sharp-duotone`

> **Note:** Duotone, Sharp, and Sharp-Duotone require Font Awesome Pro.

#### Free vs Pro Icons

```php
// Only free icons (default)
NovaFontAwesome::make('Icon')
    ->freeOnly()

// Include Pro icons (requires FA Pro subscription and API token)
NovaFontAwesome::make('Icon')
    ->includePro()
```

#### Font Awesome Kit ID (Pro)

Load Pro CSS using your Font Awesome Kit:

```php
NovaFontAwesome::make('Icon')
    ->kitId('abc123def')
```

Or use a custom Pro CSS URL:

```php
NovaFontAwesome::make('Icon')
    ->proCssUrl('https://pro.fontawesome.com/releases/v6.5.0/css/all.css')
```

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

#### Client-Side Fuzzy Search

Enable or disable the local fuzzy search fallback:

```php
NovaFontAwesome::make('Icon')
    ->fuzzySearch(true) // enabled by default
    ->fuzzySearchThreshold(0.3) // 0-1, lower = stricter matching
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
            ->families(['classic', 'brands'])
            ->freeOnly()
            ->nullable()
            ->addButtonText('Choose an icon...')
            ->maxResults(50)
            ->minSearchLength(2)
            ->fuzzySearch(true)
            ->help('Select an icon to represent this item'),
    ];
}
```

## How It Works

This package uses the [Font Awesome GraphQL API](https://docs.fontawesome.com/apis/graphql) to:

1. **Search icons** - When you type in the search box, a GraphQL query is sent to Font Awesome's API
2. **Fetch SVGs** - The API returns icon metadata including SVG data for rendering
3. **Cache results** - Search results are cached for 1 hour to reduce API calls
4. **Fallback gracefully** - If the API is unavailable, local fallback icons are used

### API Endpoints

The package registers these API routes:

- `GET /nova-vendor/nova-fontawesome/search` - Search icons
- `GET /nova-vendor/nova-fontawesome/icon/{name}` - Get a specific icon
- `GET /nova-vendor/nova-fontawesome/popular` - Get popular icons for initial display
- `GET /nova-vendor/nova-fontawesome/metadata` - Get available families and styles
- `GET /nova-vendor/nova-fontawesome/debug` - Troubleshooting endpoint
- `GET /nova-vendor/nova-fontawesome/fallback` - Get fallback icons

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

    // Available icon families
    'families' => ['classic', 'brands'],

    // Maximum search results
    'max_results' => env('FONTAWESOME_MAX_RESULTS', 50),

    // Optional API token for authenticated requests (required for Pro icons)
    'api_token' => env('FONTAWESOME_API_TOKEN'),

    // Pro CSS configuration
    'pro_css' => [
        'kit_id' => env('FONTAWESOME_KIT_ID'),
        'css_url' => env('FONTAWESOME_PRO_CSS_URL'),
        'local_css' => env('FONTAWESOME_LOCAL_CSS'),
    ],

    // Client-side fuzzy search settings
    'fuzzy_search' => [
        'enabled' => true,
        'threshold' => 0.3,
    ],
];
```

### Environment Variables

Add these to your `.env` file to customize the configuration:

```env
FONTAWESOME_VERSION=6.x
FONTAWESOME_CACHE_DURATION=3600
FONTAWESOME_FREE_ONLY=true
FONTAWESOME_MAX_RESULTS=50

# Only needed for Pro icons
FONTAWESOME_API_TOKEN=your-api-token-here

# Pro CSS loading options (pick one)
FONTAWESOME_KIT_ID=abc123def
FONTAWESOME_PRO_CSS_URL=https://pro.fontawesome.com/releases/v6.5.0/css/all.css
FONTAWESOME_LOCAL_CSS=css/fontawesome-pro.css
```

### Getting a Font Awesome API Token

To access Font Awesome Pro icons or improve API rate limits, you'll need an API token:

1. **Create or log in to your Font Awesome account** at [fontawesome.com/account](https://fontawesome.com/account)
2. **Navigate to API Tokens** section at [fontawesome.com/account#api-tokens](https://fontawesome.com/account#api-tokens)
3. **Generate a new token** by clicking "Create Token"
4. **Copy the token** and add it to your `.env` file:
   ```env
   FONTAWESOME_API_TOKEN=your-token-here
   ```

> **Note:** API tokens are automatically exchanged for short-lived access tokens and cached for performance. The package handles token refresh automatically.

## Displaying Icons

### Stored Value Format

The field stores the complete Font Awesome class string:

```
"fa-solid fa-user"
"fa-regular fa-arrow-right"
"fa-brands fa-github"
"fa-sharp fa-solid fa-house"
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

## Troubleshooting

### Debug Endpoint

Visit `/nova-vendor/nova-fontawesome/debug` to check:
- API connectivity
- Token exchange status
- Search functionality
- Cache status

### Icons not loading

1. Check the debug endpoint for API errors
2. Check that your server can reach `api.fontawesome.com`
3. Check Laravel logs for errors
4. Clear the cache: `php artisan cache:clear`

### Styles not filtering correctly

Make sure you're using valid style names: `solid`, `regular`, `light`, `thin`, `duotone`, `brands`

### Pro icons not showing

1. Verify you have an active Font Awesome Pro subscription
2. Check that your API token is correctly set in `.env`
3. Use the debug endpoint to verify token exchange

## Upgrading

See [UPGRADE.md](UPGRADE.md) for upgrade instructions between major versions.

## Development

### Running Tests

```bash
composer test
```

### Code Style

```bash
composer lint        # Fix code style
composer lint-check  # Check without fixing
```

### Static Analysis

```bash
composer analyse
```

## Licence

The MIT License (MIT). Please see [License File](LICENCE) for more information.

## Sponsorships

If you are reliant on this package in your production applications, consider [sponsoring us](https://github.com/sponsors/marshmallow-packages)! It is the best way to help us keep doing what we love to do: making great open source software.

## Contributing

Feel free to suggest changes, ask for new features or fix bugs yourself. We're sure there are still a lot of improvements that could be made, and we would be very happy to merge useful pull requests.

### Special thanks to
- [All Contributors](../../contributors)
- [mdixon18](https://github.com/mdixon18/fontawesome)
- [duckzland](https://github.com/duckzland/fontawesome)

## Made with love for open source

At [Marshmallow](https://marshmallow.nl) we use a lot of open source software as part of our daily work.
So when we have an opportunity to give something back, we're super excited!

We hope you will enjoy this small contribution from us and would love to [hear from you](mailto:hello@marshmallow.nl) if you find it useful in your projects. Follow us on [Twitter](https://x.com/marshmallow_dev) for more updates!
