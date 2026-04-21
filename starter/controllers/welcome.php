<?php
// ──────────────────────────────────────────────────────────────────────────────
// welcome — Default controller
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 MerchantE. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

class welcome extends Controller
{
    public function index(): void
    {
        $this->display('welcome', [
            'title'   => 'MicroMVC',
            'message' => 'Welcome to MicroMVC — a single-file PHP micro-framework.',
        ]);
    }
}
