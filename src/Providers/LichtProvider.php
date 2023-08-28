<?php

namespace Hossam\Licht\Providers;

use Hossam\Licht\Console\Commands\CrudGenerator;
use Illuminate\Support\ServiceProvider;

class LichtProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CrudGenerator::class,
            ]);
        }
    }
}
