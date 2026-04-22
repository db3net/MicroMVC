<?php
// ──────────────────────────────────────────────────────────────────────────────
// HelperFunctionsTest — Unit tests for standalone helper functions
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

use PHPUnit\Framework\TestCase;

class HelperFunctionsTest extends TestCase
{
    public function testFileContextFromPathExtractsLastTwoSegments(): void
    {
        $result = fileContextFromPath('/var/www/myapp/controllers/hello.php');

        $this->assertStringContainsString('controllers', $result);
        $this->assertStringContainsString('hello.php', $result);
    }

    public function testFileContextFromPathHandlesShortPath(): void
    {
        $result = fileContextFromPath('hello.php');

        $this->assertStringContainsString('hello.php', $result);
    }

    public function testDebugContextReturnsString(): void
    {
        $result = debugContext(true);

        $this->assertIsString($result);
    }

    public function testDebugContextReturnsArray(): void
    {
        $result = debugContext(false);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('cfile_context', $result);
        $this->assertArrayHasKey('cclass', $result);
        $this->assertArrayHasKey('cfunction', $result);
        $this->assertArrayHasKey('ctype', $result);
        $this->assertArrayHasKey('cline', $result);
    }
}
