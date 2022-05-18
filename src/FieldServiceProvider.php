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
            Nova::style('nova-fontawesome', url('/css/fontawesome.css'));
        });

        $this->loadTranslations(__DIR__ . '/../resources/lang', 'nova-fontawesome', true);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
