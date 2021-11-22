<?php

namespace App\Providers\Cafe24;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;

class Cafe24ApiProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        App::bind('cafe24api', function()
        {
            return new \App\Services\Cafe24\Cafe24Api;
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
