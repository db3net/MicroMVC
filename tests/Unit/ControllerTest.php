<?php
// ──────────────────────────────────────────────────────────────────────────────
// ControllerTest — Unit tests for the Controller base class
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Fixtures/TestController.php';

class ControllerTest extends TestCase
{
    private TestController $controller;

    protected function setUp(): void
    {
        $this->controller = new TestController();
    }

    // ── _call dispatch ──────────────────────────────────────────────────────

    public function testCallDispatchesIndex(): void
    {
        ob_start();
        $this->controller->_call('index');
        $output = ob_get_clean();

        $this->assertEquals('test-index', $output);
    }

    public function testCallDispatchesMethodWithArgs(): void
    {
        ob_start();
        $this->controller->_call('greet', ['Alice']);
        $output = ob_get_clean();

        $this->assertEquals('hello-Alice', $output);
    }

    public function testCallDispatchesMethodWithDefaultArg(): void
    {
        ob_start();
        $this->controller->_call('greet');
        $output = ob_get_clean();

        $this->assertEquals('hello-world', $output);
    }

    public function testCallBlocksUnderscorePrefixedMethods(): void
    {
        ob_start();
        $this->controller->_call('_secret');
        $output = ob_get_clean();

        $this->assertStringContainsString('not accessible', $output);
    }

    public function testCallBlocksInternalMethods(): void
    {
        $blocked = ['_call', 'output', 'jsonOutput', 'display',
            '__construct', '__destruct', '__call', '__get', '__set', '__toString'];

        foreach ($blocked as $method) {
            ob_start();
            $this->controller->_call($method);
            $output = ob_get_clean();

            $this->assertStringContainsString('not accessible', $output,
                "Method '{$method}' should be blocked");
        }
    }

    public function testCallReportsNonexistentMethod(): void
    {
        ob_start();
        $this->controller->_call('doesNotExist');
        $output = ob_get_clean();

        $this->assertStringContainsString("doesn't exist", $output);
    }

    // ── JSON output ─────────────────────────────────────────────────────────

    public function testJsonOutputEchoes(): void
    {
        ob_start();
        $this->controller->jsonOutput(['key' => 'value']);
        $output = ob_get_clean();

        $this->assertJson($output);
        $decoded = json_decode($output, true);
        $this->assertEquals('value', $decoded['key']);
    }

    public function testJsonOutputReturnsAsVar(): void
    {
        $result = $this->controller->jsonOutput(['key' => 'value'], true);

        $this->assertIsString($result);
        $decoded = json_decode($result, true);
        $this->assertEquals('value', $decoded['key']);
    }

    public function testOutputDelegatesToJsonOutput(): void
    {
        $result = $this->controller->output('json', ['a' => 1], '', true);

        $decoded = json_decode($result, true);
        $this->assertEquals(1, $decoded['a']);
    }

    // ── View rendering ──────────────────────────────────────────────────────

    public function testDisplayRendersView(): void
    {
        $html = $this->controller->display('welcome', [
            'title'   => 'Test',
            'message' => 'Hello Test',
        ], true);

        $this->assertStringContainsString('Test', $html);
        $this->assertStringContainsString('Hello Test', $html);
    }

    public function testDisplayHandlesMissingView(): void
    {
        ob_start();
        $this->controller->display('nonexistent_view', [], false);
        $output = ob_get_clean();

        $this->assertStringContainsString('View not found', $output);
    }
}
