<?php

namespace Nookery\HuaweiCloud;

use Illuminate\Support\ServiceProvider;

class Provider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(dirname(__DIR__).'/migrations');
        $this->publishes([
            dirname(__DIR__).'/config/huawei.php' => config_path('huawei.php'),
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('huawei-cloud', function ($app)
        {
            return new HuaweiCloud();
        });
    }
}
