<?php

namespace Codelocks;

use Codelocks\Auth\TokenGuard;
use Codelocks\Contracts\StoreTokenUser;
use Codelocks\Controllers\OAuthController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ClientServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations')
        ], 'identity-migrations');


        $this->publishes([
            __DIR__ . '/../config/identity.php' => config_path('identity.php'),
        ], 'identity-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/identity.php', 'identity');
    }

    public static function registerRoutes():void
    {
        Route::get(config('identity.routes.redirect'), [OAuthController::class, 'redirect'])->name('identity.redirect');
        Route::get(config('identity.routes.callback'), [OAuthController::class, 'callback'])->name('identity.callback');
    }

    public static function registerUserModel(string $className): void
    {
        if (class_exists($className)) {
            app()->singleton(StoreTokenUser::class, fn() => new $className);
        }
    }

    public static function registerGuardDriver($name = 'identity-token'): void
    {
        Auth::extend($name, function (Application $app, string $name) {
            // Return an instance of Illuminate\Contracts\Auth\Guard...

            return new TokenGuard($app['request'], $name, config('identity'));
        });
    }

}