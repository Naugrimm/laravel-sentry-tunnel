<?php

namespace SentryTunnel\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use SentryTunnel\Tests\TestCase;

class ConfigTest extends TestCase
{
    #[Test]
    public function it_set_allowed_hosts_from_sentry_url()
    {
        putenv('SENTRY_LARAVEL_DSN=https://test@account.sentry.test/123');

        $config = require __DIR__.'/../../config/sentry-tunnel.php';

        $this->assertEquals('account.sentry.test', $config['allowed-hosts'][0]);
    }

    #[Test]
    public function it_set_allowed_projects_from_sentry_url()
    {
        putenv('SENTRY_LARAVEL_DSN=https://test@account.sentry.test/123');

        $config = require __DIR__.'/../../config/sentry-tunnel.php';

        $this->assertEquals('123', $config['allowed-projects'][0]);
    }
}
