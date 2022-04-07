<?php

namespace Naugrim\LaravelSentryTunnel\Contracts;

interface MiddlewareList
{
    public function getMiddlewareList() : array;
}
