<?php
// ──────────────────────────────────────────────────────────────────────────────
// notfound — 404 controller
// Author: dblack
// Email: dblack@merchante.com
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

class notfound extends Controller
{
    public function index(): void
    {
        http_response_code(404);
        $this->display('404', [
            'title'   => '404 — Not Found',
            'message' => 'The page you requested does not exist.',
        ]);
    }
}
