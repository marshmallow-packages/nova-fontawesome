<?php

declare(strict_types=1);

namespace Marshmallow\NovaFontAwesome;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Marshmallow\NovaFontAwesome\Services\IconClassConverter;
use Marshmallow\NovaFontAwesome\Services\FontAwesomeApiService;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->routes();

        Nova::serving(function (ServingNova $event): void {
            Nova::script('nova-fontawesome', __DIR__ . '/../dist/js/nova-fontawesome.js');
            Nova::style('nova-fontawesome', __DIR__ . '/../dist/css/nova-fontawesome.css');

            // Load Font Awesome CSS based on strategy
            $this->loadFontAwesomeCss();
        });

        $this->loadTranslations();

        $this->publishes([
            __DIR__ . '/../config/nova-fontawesome.php' => config_path('nova-fontawesome.php'),
        ], 'nova-fontawesome-config');

        $this->publishes([
            __DIR__ . '/../resources/lang' => lang_path('vendor/nova-fontawesome'),
        ], 'nova-fontawesome-lang');
    }

    /**
     * Load Font Awesome CSS based on strategy configuration.
     *
     * Priority order:
     * 1. self-hosted - Load from local path
     * 2. kit - Use Font Awesome Kit
     * 3. cdn - Load from CDN (fallback)
     */
    protected function loadFontAwesomeCss(): void
    {
        $cssConfig = config('nova-fontawesome.css', []);
        $strategy = $cssConfig['strategy'] ?? 'self-hosted';

        // Legacy support: check old pro_css config if new css config not set
        $legacyConfig = config('nova-fontawesome.pro_css', []);
        if (empty($cssConfig) && !empty($legacyConfig)) {
            $this->loadLegacyCss($legacyConfig);

            return;
        }

        switch ($strategy) {
            case 'self-hosted':
                $path = $cssConfig['path'] ?? '/vendor/fontawesome/css/all.min.css';
                // Only load if file exists, otherwise fall through to CDN
                if ($this->selfHostedCssExists($path)) {
                    Nova::style('nova-fontawesome-local', asset($path));

                    return;
                }
                // Fall through to kit or cdn if self-hosted file doesn't exist
                if (!empty($cssConfig['kit_id'])) {
                    Nova::remoteScript('https://kit.fontawesome.com/' . $cssConfig['kit_id'] . '.js');

                    return;
                }
                // Fall through to CDN
                $this->loadCdnCss($cssConfig);
                break;

            case 'kit':
                $kitId = $cssConfig['kit_id'] ?? null;
                if ($kitId) {
                    Nova::remoteScript('https://kit.fontawesome.com/' . $kitId . '.js');

                    return;
                }
                // Fall through to CDN if no kit_id
                $this->loadCdnCss($cssConfig);
                break;

            case 'cdn':
            default:
                $this->loadCdnCss($cssConfig);
                break;
        }
    }

    /**
     * Check if self-hosted CSS file exists.
     */
    protected function selfHostedCssExists(string $path): bool
    {
        $publicPath = public_path(mb_ltrim($path, '/'));

        return file_exists($publicPath);
    }

    /**
     * Load CSS from CDN.
     */
    protected function loadCdnCss(array $cssConfig): void
    {
        $version = $cssConfig['cdn_version'] ?? '6.5.1';
        Nova::style(
            'nova-fontawesome-cdn',
            "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/{$version}/css/all.min.css"
        );
    }

    /**
     * Load CSS using legacy pro_css configuration (backwards compatibility).
     */
    protected function loadLegacyCss(array $config): void
    {
        // Option 1: Font Awesome Kit
        if (!empty($config['kit_id'])) {
            Nova::remoteScript('https://kit.fontawesome.com/' . $config['kit_id'] . '.js');

            return;
        }

        // Option 2: Direct CSS URL (Pro or custom)
        if (!empty($config['css_url'])) {
            Nova::style('nova-fontawesome-pro', $config['css_url']);

            return;
        }

        // Option 3: Local CSS file
        if (!empty($config['local_css'])) {
            Nova::style('nova-fontawesome-local', asset($config['local_css']));

            return;
        }

        // Default: Load Font Awesome Free from CDN
        Nova::style('nova-fontawesome-cdn', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');
    }

    /**
     * Load translations using nova-translations-loader if available,
     * otherwise fall back to Laravel's built-in translation loading.
     */
    protected function loadTranslations(): void
    {
        // Try to use nova-translations-loader if available and we have the trait
        if (trait_exists(\Outl1ne\NovaTranslationsLoader\LoadsNovaTranslations::class)) {
            $this->loadNovaTranslationsViaTrait();
        } else {
            // Fallback to Laravel's built-in JSON translations
            $this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang');
            $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'nova-fontawesome');
        }
    }

    /**
     * Load translations using nova-translations-loader trait.
     * We manually implement what the trait does to avoid inheritance issues.
     */
    protected function loadNovaTranslationsViaTrait(): void
    {
        $path = __DIR__ . '/../resources/lang';

        // Register translations with Nova directly
        Nova::serving(function () use ($path): void {
            $locale = app()->getLocale();
            $fallbackLocale = config('app.fallback_locale');

            // Try to load the translation file
            foreach ([$locale, $fallbackLocale, 'en'] as $tryLocale) {
                $file = "{$path}/{$tryLocale}.json";
                if (file_exists($file)) {
                    $translations = json_decode(file_get_contents($file), true) ?? [];
                    Nova::translations($translations);
                    break;
                }
            }
        });

        // Also load via Laravel's standard translation loading
        $this->loadJsonTranslationsFrom($path);
        $this->loadTranslationsFrom($path, 'nova-fontawesome');
    }

    /**
     * Register the field's routes.
     */
    protected function routes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->prefix('nova-vendor/nova-fontawesome')
            ->group(__DIR__ . '/../routes/api.php');
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/nova-fontawesome.php',
            'nova-fontawesome'
        );

        $this->app->singleton(FontAwesomeApiService::class, function ($app) {
            return new FontAwesomeApiService;
        });

        $this->app->singleton(IconClassConverter::class, function ($app) {
            return new IconClassConverter;
        });
    }
}
