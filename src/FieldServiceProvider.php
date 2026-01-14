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
        // Try to use nova-translations-loader if available
        if (trait_exists(\Outl1ne\NovaTranslationsLoader\LoadsNovaTranslations::class)) {
            $this->loadNovaTranslations(__DIR__ . '/../resources/lang', 'nova-fontawesome', true);
        } else {
            // Fallback to Laravel's built-in JSON translations
            $this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang');
            $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'nova-fontawesome');
        }
    }

    /**
     * Load translations using nova-translations-loader.
     * This method mimics the LoadsNovaTranslations trait behavior.
     */
    protected function loadNovaTranslations(string $path, string $domain, bool $override = false): void
    {
        if (trait_exists(\Outl1ne\NovaTranslationsLoader\LoadsNovaTranslations::class)) {
            // Use reflection to call the trait method if available
            $loader = new class {
                use \Outl1ne\NovaTranslationsLoader\LoadsNovaTranslations;

                public function load(string $path, string $domain, bool $override): void
                {
                    $this->loadTranslations($path, $domain, $override);
                }
            };
            $loader->load($path, $domain, $override);
        }
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
