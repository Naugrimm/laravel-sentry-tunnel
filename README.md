# Laravel Sentry Tunnel

This package provides a URL for use with the [`tunnel`-option](https://docs.sentry.io/platforms/javascript/troubleshooting/#using-the-tunnel-option) of the Sentry SDK.

## Installation

```shell
composer require asbiin/laravel-sentry-tunnel
```

## Configuration

You can optionally publish the configuration files:

```shell
php artisan vendor:publish --provider=Naugrim\\LaravelSentryTunnel\\Provider
```

You **must** place at least one allowed host in your `.env` file:

```dotenv
SENTRY_TUNNEL_ALLOWED_HOSTS=my.host.com
```

**NOTE**: This essentially creates a reverse proxy to the `SENTRY_TUNNEL_ALLOWED_HOSTS`. As the Sentry DSN is not kept secret, this enables everyone to send messages to these hosts that seem to originate from your server.

Therefore, the default middleware list for the tunnel URL includes `web` and `auth` (so that only authenticated users can use the endpoint).

As you currently cannot pass a dynamic `X-XSRF-TOKEN` header in Sentry's `transportOptions` you either have to implement your own transport or place the tunnel URL in the exclude-list in the `VerifyCsrfToken` middleware.

If you want to change this behavior, provide a custom implementation of `\Naugrim\LaravelSentryTunnel\Contracts\MiddlewareList` via the container:

```php
// app/Services/MyMiddlewareList.php

namespace App\Services;

use Naugrim\LaravelSentryTunnel\Services\MiddlewareList;

class MyMiddlewareList extends MiddlewareList
{
    public function getMiddlewareList() : array{
        return array_merge(
            parent::getMiddlewareList(),
            [
                "throttle",
            ]
        );
    }
}
```

Then add it to the container in your `AppServiceProvider`:

```php
// app/Providers/AppServiceProvider.php

use Illuminate\Support\ServiceProvider;
use Naugrim\LaravelSentryTunnel\Contracts\MiddlewareList;
use App\Services\MyMiddlewareList;

class AppServiceProvider extends ServiceProvider
{
    public function register(){
        $this->app->alias(MyMiddlewareList::class, MiddlewareList::class);
    }
}
```

Optionally you can restrict the project IDs that are allowed to use this endpoint. The default behavior is to allow all projects.

```dotenv
SENTRY_TUNNEL_ALLOWED_PROJECTS=1234,456,78
```

You can change the URL of the tunnel if required. The default value is `/sentry/tunnel`

```dotenv
SENTRY_TUNNEL_URL="/super/secret/tunnel"
```

## Usage

Consult [Sentry's documentation](https://docs.sentry.io/platforms/javascript/troubleshooting/#using-the-tunnel-option).
