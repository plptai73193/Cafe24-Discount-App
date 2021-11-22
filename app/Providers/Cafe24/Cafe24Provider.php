<?php

namespace App\Providers\Cafe24;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;

class Cafe24Provider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        App::bind('cafe24', function()
        {
            return new \App\Services\Cafe24\Cafe24;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
