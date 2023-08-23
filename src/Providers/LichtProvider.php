<?php

namespace Hossam\Licht\Providers;

use App\Console\Commands\CrudGeneratore;
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
                CrudGeneratore::class,
            ]);
        }
    }
}
