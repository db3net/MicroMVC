<?php
// ──────────────────────────────────────────────────────────────────────────────
// DBConnectionTest — Integration tests for the DB connection registry
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

use PHPUnit\Framework\TestCase;

class DBConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        DB::reset();
    }

    protected function tearDown(): void
    {
        global $_config;
        $_config = null;
        DB::reset();
    }

    // ── MySQL connection ────────────────────────────────────────────────────

    public function testMySQLConnectionReturnsPDO(): void
    {
        $host = getenv('MYSQL_HOST') ?: '127.0.0.1';
        $port = getenv('MYSQL_PORT') ?: '3306';
        $db   = getenv('MYSQL_DATABASE') ?: 'testdb';
        $user = getenv('MYSQL_USER') ?: 'root';
        $pass = getenv('MYSQL_PASSWORD') ?: 'test';

        global $_config;
        $_config = [
            'connections' => [
                'mysql_test' => [
                    'driver'   => 'mysql',
                    'host'     => $host,
                    'port'     => (int) $port,
                    'dbname'   => $db,
                    'user'     => $user,
                    'password' => $pass,
                ],
            ],
        ];

        try {
            $pdo = DB::connection('mysql_test');
            $this->assertInstanceOf(PDO::class, $pdo);
        } catch (PDOException $e) {
            $this->markTestSkipped('MySQL not available: ' . $e->getMessage());
        }
    }

    public function testMySQLConnectionPoolsInstances(): void
    {
        $host = getenv('MYSQL_HOST') ?: '127.0.0.1';
        $port = getenv('MYSQL_PORT') ?: '3306';
        $db   = getenv('MYSQL_DATABASE') ?: 'testdb';
        $user = getenv('MYSQL_USER') ?: 'root';
        $pass = getenv('MYSQL_PASSWORD') ?: 'test';

        global $_config;
        $_config = [
            'connections' => [
                'mysql_test' => [
                    'driver'   => 'mysql',
                    'host'     => $host,
                    'port'     => (int) $port,
                    'dbname'   => $db,
                    'user'     => $user,
                    'password' => $pass,
                ],
            ],
        ];

        try {
            $first  = DB::connection('mysql_test');
            $second = DB::connection('mysql_test');
            $this->assertSame($first, $second);
        } catch (PDOException $e) {
            $this->markTestSkipped('MySQL not available: ' . $e->getMessage());
        }
    }

    // ── PostgreSQL connection ───────────────────────────────────────────────

    public function testPostgresConnectionReturnsPDO(): void
    {
        $host = getenv('PG_HOST') ?: '127.0.0.1';
        $port = getenv('PG_PORT') ?: '5432';
        $db   = getenv('PG_DATABASE') ?: 'testdb';
        $user = getenv('PG_USER') ?: 'postgres';
        $pass = getenv('PG_PASSWORD') ?: 'test';

        global $_config;
        $_config = [
            'connections' => [
                'pg_test' => [
                    'driver'   => 'pgsql',
                    'host'     => $host,
                    'port'     => (int) $port,
                    'dbname'   => $db,
                    'user'     => $user,
                    'password' => $pass,
                ],
            ],
        ];

        try {
            $pdo = DB::connection('pg_test');
            $this->assertInstanceOf(PDO::class, $pdo);
        } catch (PDOException $e) {
            $this->markTestSkipped('PostgreSQL not available: ' . $e->getMessage());
        }
    }

    public function testPostgresConnectionPoolsInstances(): void
    {
        $host = getenv('PG_HOST') ?: '127.0.0.1';
        $port = getenv('PG_PORT') ?: '5432';
        $db   = getenv('PG_DATABASE') ?: 'testdb';
        $user = getenv('PG_USER') ?: 'postgres';
        $pass = getenv('PG_PASSWORD') ?: 'test';

        global $_config;
        $_config = [
            'connections' => [
                'pg_test' => [
                    'driver'   => 'pgsql',
                    'host'     => $host,
                    'port'     => (int) $port,
                    'dbname'   => $db,
                    'user'     => $user,
                    'password' => $pass,
                ],
            ],
        ];

        try {
            $first  = DB::connection('pg_test');
            $second = DB::connection('pg_test');
            $this->assertSame($first, $second);
        } catch (PDOException $e) {
            $this->markTestSkipped('PostgreSQL not available: ' . $e->getMessage());
        }
    }
}
