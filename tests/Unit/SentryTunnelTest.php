<?php

namespace SentryTunnel\Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use SentryTunnel\Tests\TestCase;

class SentryTunnelTest extends TestCase
{
    #[Test]
    public function it_proxies_a_request()
    {
        config(['sentry-tunnel.allowed-hosts' => 'account.sentry.test']);
        config(['sentry-tunnel.allowed-projects' => '123']);

        Http::fake([
            '*' => Http::response(),
        ]);

        $data = [
            'dsn' => 'https://user@account.sentry.test/123',
        ];
        $response = $this->postJson('/sentry/tunnel', $data);

        $response->assertStatus(200);

        Http::assertSent(function (Request $request) use ($data) {
            $this->assertEquals('https://account.sentry.test/api/123/envelope/?sentry_key=user', $request->url());
            $this->assertTrue($request->hasHeader('Content-Type', 'application/x-sentry-envelope'));
            $this->assertEquals(json_encode($data), $request->body());

            return true;
        });
    }

    #[Test]
    public function it_proxies_the_enveloppe()
    {
        config(['sentry-tunnel.allowed-hosts' => 'account.sentry.test']);
        config(['sentry-tunnel.allowed-projects' => '123']);

        Http::fake([
            '*' => Http::response(),
        ]);

        $data = implode("\n", [
            json_encode([
                'dsn' => 'https://user@account.sentry.test/123',
            ]),
            json_encode([
                'type' => 'client_report',
            ]),
            json_encode([
                'timestamp' => '123',
            ]),
        ]);

        $response = $this->call('POST', '/sentry/tunnel', content: $data);

        $response->assertStatus(200);

        Http::assertSent(function (Request $request) use ($data) {
            $this->assertEquals('https://account.sentry.test/api/123/envelope/?sentry_key=user', $request->url());
            $this->assertTrue($request->hasHeader('Content-Type', 'application/x-sentry-envelope'));
            $this->assertEquals($data, $request->body());

            return true;
        });
    }

    #[Test]
    public function it_fails_if_no_user()
    {
        Http::fake([
            '*' => Http::response(),
        ]);

        $data = [
            'dsn' => 'https://account.sentry.test/123',
        ];
        $response = $this->postJson('/sentry/tunnel', $data);

        $response->assertStatus(401);
        $response->assertSee('no user');

        Http::assertNothingSent();
    }

    #[Test]
    public function it_fails_if_invalid_host()
    {
        config(['sentry-tunnel.allowed-hosts' => 'account.sentry.test']);

        Http::fake([
            '*' => Http::response(),
        ]);

        $data = [
            'dsn' => 'https://user@host',
        ];
        $response = $this->postJson('/sentry/tunnel', $data);

        $response->assertStatus(401);
        $response->assertSee('invalid host');

        Http::assertNothingSent();
    }

    #[Test]
    public function it_fails_if_no_project()
    {
        config(['sentry-tunnel.allowed-hosts' => 'account.sentry.test']);

        Http::fake([
            '*' => Http::response(),
        ]);

        $data = [
            'dsn' => 'https://user@account.sentry.test',
        ];
        $response = $this->postJson('/sentry/tunnel', $data);

        $response->assertStatus(422);
        $response->assertSee('no project');

        Http::assertNothingSent();
    }

    #[Test]
    public function it_fails_if_project_void()
    {
        config(['sentry-tunnel.allowed-hosts' => 'account.sentry.test']);

        Http::fake([
            '*' => Http::response(),
        ]);

        $data = [
            'dsn' => 'https://user@account.sentry.test/0',
        ];
        $response = $this->postJson('/sentry/tunnel', $data);

        $response->assertStatus(422);
        $response->assertSee('no project');

        Http::assertNothingSent();
    }

    #[Test]
    public function it_fails_if_project_not_allowed()
    {
        config(['sentry-tunnel.allowed-hosts' => 'account.sentry.test']);
        config(['sentry-tunnel.allowed-projects' => '123']);

        Http::fake([
            '*' => Http::response(),
        ]);

        $data = [
            'dsn' => 'https://user@account.sentry.test/456',
        ];
        $response = $this->postJson('/sentry/tunnel', $data);

        $response->assertStatus(401);
        $response->assertSee('invalid project');

        Http::assertNothingSent();
    }

    #[Test]
    public function it_rethrow_an_error()
    {
        config(['sentry-tunnel.allowed-hosts' => 'account.sentry.test']);
        config(['sentry-tunnel.allowed-projects' => '123']);

        Http::fake([
            '*' => Http::response('error', status: 500),
        ]);

        $data = [
            'dsn' => 'https://user@account.sentry.test/123',
        ];
        $response = $this->postJson('/sentry/tunnel', $data);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'Server Error',
        ]);

        Http::assertSent(function (Request $request) use ($data) {
            $this->assertEquals('https://account.sentry.test/api/123/envelope/?sentry_key=user', $request->url());
            $this->assertTrue($request->hasHeader('Content-Type', 'application/x-sentry-envelope'));
            $this->assertEquals(json_encode($data), $request->body());

            return true;
        });
    }
}
