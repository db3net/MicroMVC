<?php
// ──────────────────────────────────────────────────────────────────────────────
// RouterTest — Unit tests for the Router class
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        global $_config;
        $_config = [
            '_routes' => [
                '__default' => 'welcome',
                '__404'     => 'notfound/index',
                'api'       => 'apicontroller/handle',
            ],
        ];

        // Reset the static route cache between tests
        $ref = new ReflectionClass(Router::class);
        $prop = $ref->getProperty('routes');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
    }

    protected function tearDown(): void
    {
        global $_config;
        $_config = null;
    }

    public function testRoutesLoadsFromConfig(): void
    {
        $routes = Router::routes();

        $this->assertArrayHasKey('__default', $routes);
        $this->assertArrayHasKey('__404', $routes);
        $this->assertEquals('welcome', $routes['__default']);
    }

    public function testRouteElementsForConfiguredKey(): void
    {
        [$class, $method] = Router::routeElementsForKey('__404');

        $this->assertEquals('notfound', $class);
        $this->assertEquals('index', $method);
    }

    public function testRouteElementsForKeyWithSlash(): void
    {
        [$class, $method] = Router::routeElementsForKey('api');

        $this->assertEquals('apicontroller', $class);
        $this->assertEquals('handle', $method);
    }

    public function testRoutesCachesAfterFirstLoad(): void
    {
        $first  = Router::routes();
        $second = Router::routes();

        $this->assertSame($first, $second);
    }
}
