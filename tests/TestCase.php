<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Laravel's test client trims trailing slashes from URIs. This sends the path untouched,
     * which the slash / no-slash routing rules depend on.
     */
    protected function getRaw(string $uri, array $server = []): \Illuminate\Testing\TestResponse
    {
        $response = $this->app->handle(\Illuminate\Http\Request::create($uri, 'GET', [], [], [], $server));

        return \Illuminate\Testing\TestResponse::fromBaseResponse($response);
    }
    //
}
