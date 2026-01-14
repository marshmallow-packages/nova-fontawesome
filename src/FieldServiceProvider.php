<?php

namespace Marshmallow\NovaFontAwesome;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Marshmallow\NovaFontAwesome\Services\FontAwesomeApiService;
use Outl1ne\NovaTranslationsLoader\LoadsNovaTranslations;

class FieldServiceProvider extends ServiceProvider
{
    use LoadsNovaTranslations;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
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

        $this->loadTranslations(__DIR__ . '/../resources/lang', 'nova-fontawesome', true);

        $this->publishes([
            __DIR__ . '/../config/nova-fontawesome.php' => config_path('nova-fontawesome.php'),
        ], 'nova-fontawesome-config');
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
     *
     * @return void
     */
    public function register()
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
