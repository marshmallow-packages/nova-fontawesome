<?php

namespace Marshmallow\NovaFontAwesome;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;
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
        Nova::serving(function (ServingNova $event) {
            Nova::script('nova-fontawesome', __DIR__ . '/../dist/js/nova-fontawesome.js');

            collect(config('nova-fontawesome.js') ?? [])->each(function ($path) {
                Nova::script('nova-fontawesome', asset($path));
            });
            Nova::style('nova-fontawesome', asset('/css/fontawesome.css'));
        });

        $this->loadTranslations(__DIR__ . '/../resources/lang', 'nova-fontawesome', true);

        $this->publishes([
            __DIR__ . '/../config/nova-fontawesome.php' => config_path('nova-fontawesome.php'),
        ], 'nova-fontawesome-config');
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
    }
}
