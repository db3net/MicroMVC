<?php
// ──────────────────────────────────────────────────────────────────────────────
// JSONModelTest — Unit tests for the JSONModel (via User model)
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

use PHPUnit\Framework\TestCase;

class JSONModelTest extends TestCase
{
    private string $dataDir;

    protected function setUp(): void
    {
        $this->dataDir = sys_get_temp_dir() . '/micromvc_model_test_' . uniqid();
        mkdir($this->dataDir, 0755, true);

        // Point the 'default' connection at our temp directory
        global $_config;
        $_config = [
            'connections' => [
                'default' => ['driver' => 'file', 'path' => $this->dataDir],
            ],
        ];
    }

    protected function tearDown(): void
    {
        global $_config;
        $_config = null;

        // Clean up
        $files = glob($this->dataDir . '/*');
        foreach ($files as $file) {
            unlink($file);
        }
        rmdir($this->dataDir);

        DB::reset();
    }

    public function testSaveAndFind(): void
    {
        $user = new User('Alice', 'alice@example.com');
        $user->save();

        $found = User::find('alice@example.com');

        $this->assertNotNull($found);
        $this->assertEquals('Alice', $found->name);
        $this->assertEquals('alice@example.com', $found->email);
    }

    public function testFindReturnsNullForMissing(): void
    {
        $this->assertNull(User::find('nobody@example.com'));
    }

    public function testSaveOverwritesExisting(): void
    {
        $user = new User('Alice', 'alice@example.com');
        $user->save();

        $user->name = 'Alice Updated';
        $user->save();

        $found = User::find('alice@example.com');
        $this->assertEquals('Alice Updated', $found->name);
    }

    public function testDelete(): void
    {
        $user = new User('Bob', 'bob@example.com');
        $user->save();

        User::delete('bob@example.com');

        $this->assertNull(User::find('bob@example.com'));
    }

    public function testMultipleRecords(): void
    {
        (new User('Alice', 'alice@example.com'))->save();
        (new User('Bob', 'bob@example.com'))->save();

        $alice = User::find('alice@example.com');
        $bob   = User::find('bob@example.com');

        $this->assertEquals('Alice', $alice->name);
        $this->assertEquals('Bob', $bob->name);
    }
}
