<?php

namespace App\Providers;

use App\Services\FacebookAccountService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->bind( 'App\Services\FacebookAccountService',
        function( $app, array $parameters)
        {
            //call the constructor passing the first element of $parameters
            return new FacebookAccountService($parameters[0]);
        } );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
