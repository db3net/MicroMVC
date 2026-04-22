<?php
// ──────────────────────────────────────────────────────────────────────────────
// DBTest — Unit tests for the DB connection registry
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

use PHPUnit\Framework\TestCase;

class DBTest extends TestCase
{
    protected function setUp(): void
    {
        global $_config;
        $_config = [
            'connections' => [
                'default' => ['driver' => 'file', 'path' => 'data'],
                'logs'    => ['driver' => 'file', 'path' => 'data/logs'],
                'mysql_main' => [
                    'driver' => 'mysql',
                    'host'   => '127.0.0.1',
                    'port'   => 3306,
                    'dbname' => 'myapp',
                    'user'   => 'root',
                    'password' => '',
                ],
            ],
        ];

        DB::reset();
    }

    protected function tearDown(): void
    {
        global $_config;
        $_config = null;
        DB::reset();
    }

    public function testDataDirReturnsConfiguredPath(): void
    {
        $this->assertEquals('data', DB::dataDir('default'));
    }

    public function testDataDirReturnsNamedConnectionPath(): void
    {
        $this->assertEquals('data/logs', DB::dataDir('logs'));
    }

    public function testDataDirThrowsForMissingConnection(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("not configured");

        DB::dataDir('nonexistent');
    }

    public function testDataDirThrowsForNonFileDriver(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("not a file-store");

        DB::dataDir('mysql_main');
    }

    public function testConnectionThrowsForMissingConnection(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("not configured");

        DB::connection('nonexistent');
    }

    public function testResetClearsPool(): void
    {
        // Just verify reset doesn't throw — pool is internal
        DB::reset();
        $this->assertTrue(true);
    }
}
