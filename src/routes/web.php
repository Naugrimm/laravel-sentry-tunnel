<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Naugrim\LaravelSentryTunnel\Http\Controller\SentryTunnel;

Route::post(config('sentry-tunnel.tunnel-url'), [SentryTunnel::class, 'tunnel']);
