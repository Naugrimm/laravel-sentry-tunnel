<?php

declare(strict_types=1);

namespace Naugrim\LaravelSentryTunnel\Http\Controller;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Naugrim\LaravelSentryTunnel\Contracts\MiddlewareList;

class SentryTunnel extends Controller
{
    public function __construct(MiddlewareList $middlewareList)
    {
        $this->middleware($middlewareList->getMiddlewareList());
    }

    private function allowedHosts(): array
    {
        $allowedHosts = trim(config("sentry-tunnel.allowed-hosts") ?? "");
        if (empty($allowedHosts)) {
            return [];
        }

        return explode(",", $allowedHosts);
    }

    private function allowedProjects(): array
    {
        $allowedProjects = trim(config("sentry-tunnel.allowed-projects") ?? "");
        if (empty($allowedProjects)) {
            return [];
        }

        return explode(",", $allowedProjects);
    }

    public function tunnel(Request $request)
    {
        $envelope = $request->getContent();
        $pieces = explode("\n", $envelope, 2);
        $header = json_decode($pieces[0], true);
        if (! isset($header["dsn"])) {
            return response('no dsn', 422);
        }

        $dsn = parse_url($header["dsn"]);

        if (empty($dsn["user"])) {
            return response("no user", 401);
        }

        if (! in_array($dsn["host"], $this->allowedHosts(), true)) {
            return response('invalid host', 401);
        }

        if (! $projectId = intval(trim($dsn["path"], "/"))) {
            return response('no project', 422);
        }

        $allowedProjects = $this->allowedProjects();
        if (! empty($allowedProjects) && ! in_array($projectId, $allowedProjects, true)) {
            return response('invalid project', 401);
        }

        return Http::withBody($envelope, "application/x-sentry-envelope")
            ->post("https://".$dsn["host"]."/api/{$projectId}/envelope/?sentry_key=".$dsn["user"])
        ;
    }
}
