<?php
//1. MOVER AL PACKAGE, 
//2. lumen: let’s add our new Service Provider to the array of Service Providers in file bootstrap/app.php:
//2. laravel: let’s add our new Service Provider to the array of Service Providers in file config/app.php:
//3. we create the controller it in the same src folder of our package.
//4. do the same with routes.php
namespace Healthmeasures\Laravel;

use Illuminate\Support\ServiceProvider;

class HealthmeasuresServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__.'/healthmeasures-routes.php';
        
        $this->app->make('Healthmeasures\Laravel\HealthmeasuresController');
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {        
        $this->loadViewsFrom(__DIR__ . '/views', 'healthmeasures');
        $this->publishes([
            __DIR__ . '/views' => base_path('resources/views/healthmeasures'),
        ]);
    }
}
