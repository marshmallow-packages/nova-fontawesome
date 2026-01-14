<?php

namespace Marshmallow\NovaFontAwesome\Tests;

use Marshmallow\NovaFontAwesome\FieldServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            FieldServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('nova-fontawesome.version', '6.x');
        $app['config']->set('nova-fontawesome.cache_duration', 3600);
        $app['config']->set('nova-fontawesome.free_only', true);
        $app['config']->set('nova-fontawesome.max_results', 25);
        $app['config']->set('nova-fontawesome.styles', ['solid', 'regular', 'brands']);
    }
}
