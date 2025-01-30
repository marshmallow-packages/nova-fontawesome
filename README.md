[![Latest Version on Packagist](https://img.shields.io/packagist/v/marshmallow/nova-fontawesome.svg?style=flat-square)](https://packagist.org/packages/marshmallow/nova-fontawesome)
[![Total Downloads](https://img.shields.io/packagist/dt/marshmallow/nova-fontawesome.svg?style=flat-square)](https://packagist.org/packages/marshmallow/nova-fontawesome)

# Laravel Nova Font Awesome Icons

A Laravel Nova Field which will let you select a Font Awesome Icon.

> [!important]
> This package was originally forked from [mdixon18/fontawesome](https://github.com/mdixon18/fontawesome). Since we were making many opinionated changes, we decided to continue development in our own version rather than submitting pull requests that might not benefit all users of the original package. You're welcome to use this package, we're actively maintaining it. If you encounter any issues, please don't hesitate to reach out.

## Requirements

- `php: ^8.0`
- `laravel/nova: ^4.0|^5.0`
- `fontawesome: ^6.0`

## Installation:

> [!important]
> The installation has two steps!

**Step 1**: You can install the package in to a Laravel app that uses Nova via composer:

```bash
composer require marshmallow/nova-fontawesome
```

**Step 2**: Next download your copy from `Font Awesome` and add the `JS` files to the public folder of your project. By default we expect you to add the `all.js` at `./public/vendor/fontawesome/all.js` so we can access it at `https://your-project.test/vendor/fontawesome/all.js`. Because of licecing we do not include this file, you will have to copy it there yourself.

**Optional Step 3**: You can change the location and the JS files that we load by changing the config. By default the example above will be loaded in the config.
If you need to load more `JS` files or need them to be in another location, you can publish the config file using the command below.

```bash
php artisan vendor:publish --tag=nova-fontawesome-config
```

```php
return [

  'js' => [
    '/vendor/fontawesome/all.js',
    // add more if you need to...
  ],

];
```

## Usage:

Add the below to app/Nova resources.

```php
use Marshmallow\NovaFontAwesome\NovaFontAwesome;
NovaFontAwesome::make('Icon');
```

```php
NovaFontAwesome::make('Icon')
  ->addButtonText('Click Me!')
```

You can set a default icon for when an icon has not been set like so. First parameter is the type e.g. far, fas, fab and the second is the icon name (without fa-)

```php
NovaFontAwesome::make('Icon')->defaultIcon('far', 'check-circle')
```

If you want to persist the default icon (when they press clear it brings back the default so it can't be empty) then add the following:

```php
NovaFontAwesome::make('Icon')
  ->addButtonText('Click Me!')
  ->defaultIcon('far', 'check-circle')
  ->persistDefaultIcon();
```

You can limit the icons the user can choose from like so

```php
NovaFontAwesome::make('Icon')->only([
  'facebook',
  'twitch',
  'twitter',
]);
```

You can use Font Awesome Pro by changing the config value to `true` (remember to get your license key!).

```php
return [

  // ...
  'pro' => true,
];
```

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
