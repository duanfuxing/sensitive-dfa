<?php

namespace duan617\sensitive\dfa;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

/**
 * Class ServiceProvider
 * @author Duan
 * @package duan617\sensitive\dfa
 */
class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('SensitiveDfa',function(){
            return new SensitiveDfa;
        });
    }
}
