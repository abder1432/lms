<?php

namespace App\Providers;

use App\Services\TapPayment\TapPayment;
use Illuminate\Support\ServiceProvider;

class TapServiceProvider extends ServiceProvider
{

  /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }


    /**
     * Registers app bindings and aliases.
     */
    protected function registerBindings()
    {
        $this->app->singleton(TapPayment::class, function () {
            return new TapPayment();
        });

        $this->app->alias(TapPayment::class, 'TapPayment');
    }
}
