<?php

namespace Naugrim\LaravelSentryTunnel\Services;

class MiddlewareList implements \Naugrim\LaravelSentryTunnel\Contracts\MiddlewareList
{

    public function getMiddlewareList(): array
    {
        $middleware = [
            "web",
        ];

        if (config("sentry-tunnel.use-auth-middleware")) {
            $middleware[] = "auth";
        }

        return $middleware;
    }
}
