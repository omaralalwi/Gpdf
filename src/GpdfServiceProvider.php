<?php

namespace Omaralalwi\Gpdf;

use Illuminate\Support\ServiceProvider;
use Omaralalwi\Gpdf\Gpdf;
use Omaralalwi\Gpdf\GpdfConfig;
use Omaralalwi\Gpdf\Facade\Gpdf as GpdfFacade;

class GpdfServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/gpdf.php' => config_path('gpdf.php'),
            ], 'gpdf');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/gpdf.php', 'gpdf');

        $this->app->singleton(Gpdf::class, function ($app) {
            $config = $app['config']['gpdf'];
            $gpdfConfig = new GpdfConfig($config);
            return new Gpdf($gpdfConfig);
        });

        $this->app->alias(Gpdf::class, 'gpdf');
    }
}
