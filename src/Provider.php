<?php

declare(strict_types=1);

namespace Naugrim\LaravelSentryTunnel;

use Illuminate\Support\ServiceProvider;

class Provider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__."/config/sentry-tunnel.php", "sentry-tunnel");
        $this->app->alias(Services\MiddlewareList::class, Contracts\MiddlewareList::class);
    }

    public function boot() : void
    {
        $this->loadRoutesFrom(__DIR__."/routes/web.php");
        $this->publishes([
            __DIR__."/config/sentry-tunnel.php" => config_path("sentry-tunnel.php"),
        ]);
    }
}
