<?php
// ──────────────────────────────────────────────────────────────────────────────
// ContextTest — Unit tests for the Context dispatcher
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{
    public function testIsCLIReturnsTrueInTestEnvironment(): void
    {
        // PHPUnit runs under CLI, so this should be true
        $this->assertTrue(Context::isCLI());
    }
}
