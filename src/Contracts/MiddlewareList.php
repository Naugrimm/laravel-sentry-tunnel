<?php

namespace Naugrim\LaravelSentryTunnel\Contracts;

interface MiddlewareList
{
    /**
     * @return string[]
     */
    public function getMiddlewareList(): array;
}
