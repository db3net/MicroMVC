<?php
// ──────────────────────────────────────────────────────────────────────────────
// M2DataObjectTest — Unit tests for the M2DataObject KVC base class
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

use PHPUnit\Framework\TestCase;

class TestM2Child extends M2DataObject
{
    public string $name = '';

    public function setName(string $value): void
    {
        $this->name = $value;
    }
}

class M2DataObjectTest extends TestCase
{
    public function testTakeValueForKeyCallsSetter(): void
    {
        $obj = new TestM2Child();
        $obj->takeValueForKey('Alice', 'name');

        $this->assertEquals('Alice', $obj->name);
    }

    public function testTakeValueForKeyIgnoresMissingSetter(): void
    {
        $obj = new TestM2Child();
        // 'age' has no setter — should silently do nothing
        $obj->takeValueForKey(30, 'age');

        $this->assertEquals('', $obj->name);
    }
}
