<?php
// ──────────────────────────────────────────────────────────────────────────────
// notfound — 404 error handler
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

//
// This controller handles unmatched routes. It's configured in
// config/config.php:
//
//   '_routes' => [
//       '__404' => 'notfound/index',   // ← unmatched URLs land here
//   ]
//
// Sets the proper 404 HTTP status code and renders an error page.
//

class notfound extends Controller
{
    /**
     * Any unmatched URL
     *
     * Sets 404 status and renders the error view (views/404.php).
     */
    public function index(): void
    {
        http_response_code(404);
        $this->display('404', [
            'title'   => '404 — Not Found',
            'message' => 'The page you requested does not exist.',
        ]);
    }
}
