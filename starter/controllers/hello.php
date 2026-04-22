<?php
// ──────────────────────────────────────────────────────────────────────────────
// hello — Example JSON API controller
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

//
// A minimal controller that returns JSON responses.
//
// Routes are automatic — the URL maps directly to the class and method:
//
//   /hello            → hello::index()
//   /hello/greet      → hello::greet()       → uses default arg 'world'
//   /hello/greet/Dave → hello::greet('Dave')  → arg passed from URL
//
// No route configuration needed. Just create the class and go.
//

class hello extends Controller
{
    /**
     * GET /hello
     *
     * Returns a simple JSON greeting. Good starting point for an API.
     */
    public function index(): void
    {
        $this->jsonOutput(['message' => 'Hello from MicroMVC!']);
    }

    /**
     * GET /hello/greet/{name}
     *
     * URL segments after the method name are passed as arguments.
     * The default value makes the parameter optional.
     */
    public function greet(string $name = 'world'): void
    {
        $this->jsonOutput(['hello' => $name]);
    }
}
