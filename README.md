# Font Awesome Icons

A Laravel Nova 4 Field for Font Awesome 6 Icons

## Installation:

You can install the package in to a Laravel app that uses Nova via composer:

```bash
composer require marshmallow/nova-fontawesome
```

Then copy FontAwesome v6 css assets to Laravel public path where this plugin will try to access:

-   FontAwesome icon definition : http(s)://{fqdn}/css/all.min.css

```bash
    /public/css/fontawesome.css
```

## Usage:

Add the below to app/Nova resources.

```php
  use Marshmallow\NovaFontAwesome\NovaFontAwesome;

  NovaFontAwesome::make('Icon')
```

Add fontawesome.css to the public/css

You can override the text for the field button like so

```php
  NovaFontAwesome::make('Icon')->addButtonText('Click Me!')
```

You can set a default icon for when an icon has not been set like so. First parameter is the type e.g. far, fas, fab and the second is the icon name (without fa-)

```php
  NovaFontAwesome::make('Icon')->defaultIcon('far', 'check-circle')
```

If you want to persist the default icon (when they press clear it brings back the default so it can't be empty) then add the following:

```php
  NovaFontAwesome::make('Icon')->addButtonText('Click Me!')->defaultIcon('far', 'check-circle')->persistDefaultIcon()
```

You can limit the icons the user can choose from like so

```php
  NovaFontAwesome::make('Icon')->only([
    'facebook',
    'twitch',
    'twitter',
  ])
```

You can use Font Awesome Pro by doing the following (remember to get your license key!)

```php
  NovaFontAwesome::make('Icon')->pro()
```

## Development

```zsh
FONTAWESOME_NPM_AUTH_TOKEN=YOUR_TOKEN npm install
```

Free:

-   @fortawesome/fontawesome-free@next
-   @fortawesome/fontawesome-svg-core@next
-   @fortawesome/free-solid-svg-icons@next
-   @fortawesome/free-brands-svg-icons
-   @fortawesome/free-regular-svg-icons@next

Pro:

-   @fortawesome/fontawesome-pro@next
-   @fortawesome/pro-solid-svg-icons@next
-   @fortawesome/pro-regular-svg-icons@next
-   @fortawesome/pro-light-svg-icons@next
-   @fortawesome/pro-thin-svg-icons@next
-   @fortawesome/pro-duotone-svg-icons@next

## Credits

-   [All Contributors](../../contributors)

Based on Fontawesome Package by mdixon18 & PR from duckzland.
See https://github.com/mdixon18/fontawesome

-   [mdixon18](https://github.com/mdixon18/fontawesome)
-   [duckzland](https://github.com/duckzland/fontawesome)

## License:

The MIT License (MIT). Please see [License File](LICENSE) for more information.
