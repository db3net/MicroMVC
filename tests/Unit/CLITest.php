<?php
// ──────────────────────────────────────────────────────────────────────────────
// CLITest — Unit tests for the CLI argument helper class
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

use PHPUnit\Framework\TestCase;

class CLITest extends TestCase
{
    private array $originalArgv;

    protected function setUp(): void
    {
        global $argv;
        $this->originalArgv = $argv ?? [];
    }

    protected function tearDown(): void
    {
        global $argv;
        $argv = $this->originalArgv;
    }

    public function testArgsReturnsAllArgv(): void
    {
        global $argv;
        $argv = ['index.php', 'hello/greet/Alice'];

        $result = CLI::args();
        $this->assertCount(2, $result);
        $this->assertEquals('index.php', $result[0]);
    }

    public function testArgsByIndex(): void
    {
        global $argv;
        $argv = ['index.php', 'hello/greet'];

        $this->assertEquals('index.php', CLI::args(0));
        $this->assertEquals('hello/greet', CLI::args(1));
        $this->assertNull(CLI::args(5));
    }

    public function testFirstArgReturnsSecondArgvTrimmed(): void
    {
        global $argv;
        $argv = ['index.php', '/hello/greet/'];

        $this->assertEquals('hello/greet', CLI::firstArg());
    }

    public function testFirstArgReturnsNullWhenNoArgs(): void
    {
        global $argv;
        $argv = ['index.php'];

        $this->assertNull(CLI::firstArg());
    }

    public function testFileReturnsScriptName(): void
    {
        global $argv;
        $argv = ['index.php', 'hello'];

        $this->assertEquals('index.php', CLI::file());
    }

    public function testEnumeratedArgsSkipsScriptName(): void
    {
        global $argv;
        $argv = ['index.php', 'hello', 'world'];

        $result = CLI::enumeratedArgs();
        $this->assertEquals(['hello', 'world'], $result);
    }

    public function testEnumeratedArgsEmptyWhenOnlyScript(): void
    {
        global $argv;
        $argv = ['index.php'];

        $this->assertEmpty(CLI::enumeratedArgs());
    }
}
