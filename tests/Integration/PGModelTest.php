<?php
// ──────────────────────────────────────────────────────────────────────────────
// PGModelTest — Integration tests for PGModel against a real PostgreSQL server
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Fixtures/TestPGUser.php';

class PGModelTest extends TestCase
{
    private static ?PDO $pdo = null;

    public static function setUpBeforeClass(): void
    {
        $host = getenv('PG_HOST') ?: '127.0.0.1';
        $port = getenv('PG_PORT') ?: '5432';
        $db   = getenv('PG_DATABASE') ?: 'testdb';
        $user = getenv('PG_USER') ?: 'postgres';
        $pass = getenv('PG_PASSWORD') ?: 'test';

        // Inject the pg_test connection into global config
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
            $dsn = "pgsql:host={$host};port={$port};dbname={$db}";
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            self::$pdo->exec('
                CREATE TABLE IF NOT EXISTS users (
                    email VARCHAR(255) PRIMARY KEY,
                    name  VARCHAR(255) NOT NULL
                )
            ');
        } catch (PDOException $e) {
            self::markTestSkipped('PostgreSQL not available: ' . $e->getMessage());
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$pdo) {
            self::$pdo->exec('DROP TABLE IF EXISTS users');
            self::$pdo = null;
        }

        global $_config;
        $_config = null;
        DB::reset();
    }

    protected function setUp(): void
    {
        if (!self::$pdo) {
            $this->markTestSkipped('PostgreSQL not available');
        }

        self::$pdo->exec('TRUNCATE TABLE users');
        DB::reset();
    }

    // ── CRUD operations ─────────────────────────────────────────────────────

    public function testSaveInsertsNewRecord(): void
    {
        $user = new TestPGUser('Alice', 'alice@example.com');
        $user->save();

        $found = TestPGUser::find('alice@example.com');
        $this->assertNotNull($found);
        $this->assertEquals('Alice', $found->name);
        $this->assertEquals('alice@example.com', $found->email);
    }

    public function testSaveUpdatesExistingRecord(): void
    {
        $user = new TestPGUser('Alice', 'alice@example.com');
        $user->save();

        $user->name = 'Alice Updated';
        $user->save();

        $found = TestPGUser::find('alice@example.com');
        $this->assertEquals('Alice Updated', $found->name);
    }

    public function testFindReturnsNullForMissing(): void
    {
        $this->assertNull(TestPGUser::find('nobody@example.com'));
    }

    public function testDelete(): void
    {
        $user = new TestPGUser('Bob', 'bob@example.com');
        $user->save();

        TestPGUser::delete('bob@example.com');

        $this->assertNull(TestPGUser::find('bob@example.com'));
    }

    public function testMultipleRecords(): void
    {
        (new TestPGUser('Alice', 'alice@example.com'))->save();
        (new TestPGUser('Bob', 'bob@example.com'))->save();
        (new TestPGUser('Charlie', 'charlie@example.com'))->save();

        $this->assertEquals('Alice', TestPGUser::find('alice@example.com')->name);
        $this->assertEquals('Bob', TestPGUser::find('bob@example.com')->name);
        $this->assertEquals('Charlie', TestPGUser::find('charlie@example.com')->name);
    }

    public function testDeleteDoesNotAffectOtherRecords(): void
    {
        (new TestPGUser('Alice', 'alice@example.com'))->save();
        (new TestPGUser('Bob', 'bob@example.com'))->save();

        TestPGUser::delete('alice@example.com');

        $this->assertNull(TestPGUser::find('alice@example.com'));
        $this->assertNotNull(TestPGUser::find('bob@example.com'));
    }

    // ── Upsert behavior (ON CONFLICT DO UPDATE) ────────────────────────────

    public function testSaveIsIdempotent(): void
    {
        $user = new TestPGUser('Alice', 'alice@example.com');
        $user->save();
        $user->save();
        $user->save();

        $stmt = self::$pdo->query('SELECT COUNT(*) FROM users');
        $this->assertEquals(1, $stmt->fetchColumn());
    }
}
