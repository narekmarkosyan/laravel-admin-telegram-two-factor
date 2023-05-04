<?php

namespace Narekmarkosyan\LaravelAdminTelegramTwoFactor;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Narekmarkosyan\LaravelAdminTelegramTwoFactor\Http\Middleware\AuthAdminTelegramTwoFactor;

class AuthAttemptsServiceProvider extends ServiceProvider
{
    /**
     * @param AuthTelegramTwoFactor $extension
     * @param Router $router
     * @param Kernel $kernel
     */
    public function boot(AuthTelegramTwoFactor $extension, Router $router, Kernel $kernel)
    {
        if (!AuthTelegramTwoFactor::boot()) {
            return;
        }

        // Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Disabled
        if (!AuthTelegramTwoFactor::config('enable')) {
            return;
        }

        // Register middleware
        $router->aliasMiddleware('admin.auth.2fa.telegram', AuthAdminTelegramTwoFactor::class);
        $router->pushMiddlewareToGroup('admin', 'admin.auth.2fa.telegram');

        if ($views = $extension->views()) {
            $this->loadViewsFrom($views, AuthTelegramTwoFactor::$group);
        }

        if ($this->app->runningInConsole() && $assets = $extension->assets()) {
            $this->publishes(
                [$assets => public_path('vendor/narekmarkosyan/laravel-admin-telegram-two-factor')],
                AuthTelegramTwoFactor::$group
            );
        }

        $this->app->booted(function () {
            AuthTelegramTwoFactor::routes(__DIR__ . '/../routes/web.php');
        });
    }
}
