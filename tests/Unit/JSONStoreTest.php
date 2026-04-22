<?php
// ──────────────────────────────────────────────────────────────────────────────
// JSONStoreTest — Unit tests for the JSONStore class
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

use PHPUnit\Framework\TestCase;

class JSONStoreTest extends TestCase
{
    private string $dataDir;

    protected function setUp(): void
    {
        $this->dataDir = sys_get_temp_dir() . '/micromvc_test_' . uniqid();
        mkdir($this->dataDir, 0755, true);
    }

    protected function tearDown(): void
    {
        // Clean up temp files
        $files = glob($this->dataDir . '/*');
        foreach ($files as $file) {
            unlink($file);
        }
        rmdir($this->dataDir);
    }

    // ── put / find ─────────────────────────────────────────────────────────

    public function testPutAndFind(): void
    {
        JSONStore::put('users', 'alice', ['name' => 'Alice'], $this->dataDir);
        $result = JSONStore::find('users', 'alice', $this->dataDir);

        $this->assertEquals(['name' => 'Alice'], $result);
    }

    public function testFindReturnsMultipleRecords(): void
    {
        JSONStore::put('users', 'alice', ['name' => 'Alice'], $this->dataDir);
        JSONStore::put('users', 'bob', ['name' => 'Bob'], $this->dataDir);

        $this->assertEquals(['name' => 'Alice'], JSONStore::find('users', 'alice', $this->dataDir));
        $this->assertEquals(['name' => 'Bob'], JSONStore::find('users', 'bob', $this->dataDir));
    }

    public function testPutOverwritesExistingRecord(): void
    {
        JSONStore::put('users', 'alice', ['name' => 'Alice'], $this->dataDir);
        JSONStore::put('users', 'alice', ['name' => 'Alice Updated'], $this->dataDir);

        $result = JSONStore::find('users', 'alice', $this->dataDir);
        $this->assertEquals(['name' => 'Alice Updated'], $result);
    }

    public function testFindReturnsFalseForMissingCollection(): void
    {
        $result = JSONStore::find('nonexistent', 'key', $this->dataDir);
        $this->assertFalse($result);
    }

    public function testFindReturnsFalseForMissingKey(): void
    {
        JSONStore::put('users', 'alice', ['name' => 'Alice'], $this->dataDir);
        $result = JSONStore::find('users', 'bob', $this->dataDir);

        $this->assertFalse($result);
    }

    // ── Collection name sanitization ────────────────────────────────────────

    public function testSanitizesCollectionName(): void
    {
        // Path traversal characters should be stripped
        JSONStore::put('../etc/passwd', 'key', 'value', $this->dataDir);

        // File should be created with sanitized name, not the traversal path
        $this->assertFileExists($this->dataDir . '/etcpasswd.json');
        $this->assertFileDoesNotExist($this->dataDir . '/../etc/passwd.json');
    }

    // ── log ─────────────────────────────────────────────────────────────────

    public function testLogAppendsEntry(): void
    {
        JSONStore::log('auth', 'login', 'user logged in', $this->dataDir);

        $logFile = $this->dataDir . '/log';
        $this->assertFileExists($logFile);

        $content = file_get_contents($logFile);
        $this->assertStringContainsString('auth.login', $content);
        $this->assertStringContainsString('user logged in', $content);
    }

    public function testLogAppendsMultipleEntries(): void
    {
        JSONStore::log('auth', 'login', 'first', $this->dataDir);
        JSONStore::log('auth', 'logout', 'second', $this->dataDir);

        $content = file_get_contents($this->dataDir . '/log');
        $lines = array_filter(explode("\n", trim($content)));

        $this->assertCount(2, $lines);
    }

    public function testLogHandlesArrayData(): void
    {
        JSONStore::log('api', 'request', ['method' => 'GET', 'path' => '/users'], $this->dataDir);

        $content = file_get_contents($this->dataDir . '/log');
        $this->assertStringContainsString('api.request', $content);
        $this->assertStringContainsString('GET', $content);
    }

    // ── JSON file format ────────────────────────────────────────────────────

    public function testStoresValidJson(): void
    {
        JSONStore::put('test', 'key', ['nested' => ['a' => 1]], $this->dataDir);

        $raw = file_get_contents($this->dataDir . '/test.json');
        $decoded = json_decode($raw, true);

        $this->assertNotNull($decoded);
        $this->assertEquals(1, $decoded['key']['nested']['a']);
    }
}
