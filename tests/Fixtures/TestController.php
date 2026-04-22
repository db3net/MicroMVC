<?php
// ──────────────────────────────────────────────────────────────────────────────
// TestController — Stub controller for unit tests
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

class TestController extends Controller
{
    public function index(): void
    {
        echo 'test-index';
    }

    public function greet(string $name = 'world'): void
    {
        echo "hello-{$name}";
    }

    public function jsonAction(): void
    {
        $this->jsonOutput(['status' => 'ok']);
    }

    public function _secret(): void
    {
        echo 'should-not-reach';
    }
}
