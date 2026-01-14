<?php

namespace Marshmallow\NovaFontAwesome;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Marshmallow\NovaFontAwesome\Services\FontAwesomeApiService;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->routes();

        Nova::serving(function (ServingNova $event) {
            Nova::script('nova-fontawesome', __DIR__ . '/../dist/js/nova-fontawesome.js');

            collect(config('nova-fontawesome.js') ?? [])->each(function ($path) {
                Nova::script('nova-fontawesome', asset($path));
            });
            collect(config('nova-fontawesome.css') ?? [])->each(function ($path) {
                Nova::style('nova-fontawesome', asset($path));
            });
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
        $domain = 'nova-fontawesome';

        // Register translations with Nova directly (what the trait does internally)
        if (method_exists(Nova::class, 'translations')) {
            Nova::serving(function () use ($path, $domain) {
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
        }

        // Also load via Laravel's standard translation loading
        $this->loadJsonTranslationsFrom($path);
        $this->loadTranslationsFrom($path, $domain);
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
            return new FontAwesomeApiService();
        });
    }
}
