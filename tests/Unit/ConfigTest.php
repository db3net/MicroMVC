<?php
// ──────────────────────────────────────────────────────────────────────────────
// ConfigTest — Unit tests for the Config class
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    protected function setUp(): void
    {
        // Inject config via the global override that Config::forKey() checks first
        global $_config;
        $_config = [
            '_routes' => [
                '__default' => 'welcome',
                '__404'     => 'notfound/index',
            ],
            'connections' => [
                'default' => ['driver' => 'file', 'path' => 'data'],
            ],
            'custom_key' => 'custom_value',
        ];
    }

    protected function tearDown(): void
    {
        global $_config;
        $_config = null;
    }

    public function testForKeyReturnsRoutes(): void
    {
        $routes = Config::forKey('_routes');
        $this->assertIsArray($routes);
        $this->assertArrayHasKey('__default', $routes);
        $this->assertEquals('welcome', $routes['__default']);
    }

    public function testForKeyReturnsConnections(): void
    {
        $connections = Config::forKey('connections');
        $this->assertIsArray($connections);
        $this->assertArrayHasKey('default', $connections);
        $this->assertEquals('file', $connections['default']['driver']);
    }

    public function testForKeyReturnsCustomValue(): void
    {
        $this->assertEquals('custom_value', Config::forKey('custom_key'));
    }

    public function testForKeyReturnsNullForMissingKey(): void
    {
        $this->assertNull(Config::forKey('nonexistent_key'));
    }
}
