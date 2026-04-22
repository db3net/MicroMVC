<?php
// ──────────────────────────────────────────────────────────────────────────────
// welcome — Default controller (the home page)
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

//
// This controller handles the root URL ("/"). It's set as the default
// in config/config.php:
//
//   '_routes' => [
//       '__default' => 'welcome',   // ← "/" maps here
//   ]
//
// It renders a view template (views/welcome.php) and passes data into it.
// Variables in the $data array are extracted into the view's scope, so
// $title and $message are available directly in the template.
//

class welcome extends Controller
{
    /**
     * GET /
     *
     * Renders the welcome page. Uses $this->display() to load a view.
     * Pass an array of data — keys become variable names in the template.
     */
    public function index(): void
    {
        $this->display('welcome', [
            'title'   => 'MicroMVC',
            'message' => 'Welcome to MicroMVC — a single-file PHP micro-framework.',
        ]);
    }
}
