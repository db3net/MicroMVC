<?php
// ──────────────────────────────────────────────────────────────────────────────
// MySQLModelTest — Integration tests for MySQLModel against a real MySQL server
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Fixtures/TestMySQLUser.php';

class MySQLModelTest extends TestCase
{
    private static ?PDO $pdo = null;

    public static function setUpBeforeClass(): void
    {
        $host = getenv('MYSQL_HOST') ?: '127.0.0.1';
        $port = getenv('MYSQL_PORT') ?: '3306';
        $db   = getenv('MYSQL_DATABASE') ?: 'testdb';
        $user = getenv('MYSQL_USER') ?: 'root';
        $pass = getenv('MYSQL_PASSWORD') ?: 'test';

        // Inject the mysql_test connection into global config
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
            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
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
            self::markTestSkipped('MySQL not available: ' . $e->getMessage());
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
            $this->markTestSkipped('MySQL not available');
        }

        self::$pdo->exec('TRUNCATE TABLE users');
        DB::reset();
    }

    // ── CRUD operations ─────────────────────────────────────────────────────

    public function testSaveInsertsNewRecord(): void
    {
        $user = new TestMySQLUser('Alice', 'alice@example.com');
        $user->save();

        $found = TestMySQLUser::find('alice@example.com');
        $this->assertNotNull($found);
        $this->assertEquals('Alice', $found->name);
        $this->assertEquals('alice@example.com', $found->email);
    }

    public function testSaveUpdatesExistingRecord(): void
    {
        $user = new TestMySQLUser('Alice', 'alice@example.com');
        $user->save();

        $user->name = 'Alice Updated';
        $user->save();

        $found = TestMySQLUser::find('alice@example.com');
        $this->assertEquals('Alice Updated', $found->name);
    }

    public function testFindReturnsNullForMissing(): void
    {
        $this->assertNull(TestMySQLUser::find('nobody@example.com'));
    }

    public function testDelete(): void
    {
        $user = new TestMySQLUser('Bob', 'bob@example.com');
        $user->save();

        TestMySQLUser::delete('bob@example.com');

        $this->assertNull(TestMySQLUser::find('bob@example.com'));
    }

    public function testMultipleRecords(): void
    {
        (new TestMySQLUser('Alice', 'alice@example.com'))->save();
        (new TestMySQLUser('Bob', 'bob@example.com'))->save();
        (new TestMySQLUser('Charlie', 'charlie@example.com'))->save();

        $this->assertEquals('Alice', TestMySQLUser::find('alice@example.com')->name);
        $this->assertEquals('Bob', TestMySQLUser::find('bob@example.com')->name);
        $this->assertEquals('Charlie', TestMySQLUser::find('charlie@example.com')->name);
    }

    public function testDeleteDoesNotAffectOtherRecords(): void
    {
        (new TestMySQLUser('Alice', 'alice@example.com'))->save();
        (new TestMySQLUser('Bob', 'bob@example.com'))->save();

        TestMySQLUser::delete('alice@example.com');

        $this->assertNull(TestMySQLUser::find('alice@example.com'));
        $this->assertNotNull(TestMySQLUser::find('bob@example.com'));
    }

    // ── Upsert behavior ────────────────────────────────────────────────────

    public function testSaveIsIdempotent(): void
    {
        $user = new TestMySQLUser('Alice', 'alice@example.com');
        $user->save();
        $user->save();
        $user->save();

        // Should still be exactly one record
        $stmt = self::$pdo->query('SELECT COUNT(*) FROM users');
        $this->assertEquals(1, $stmt->fetchColumn());
    }
}
