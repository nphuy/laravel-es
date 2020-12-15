<?php

namespace HNP\LaravelES;

use Illuminate\Support\ServiceProvider;

class LaravelESServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/hnp_es.php', 'es-config');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/hnp_es.php' => base_path('config/hnp_es.php'),
        ], 'es_config');
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

    }
}
