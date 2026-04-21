<?php
// ──────────────────────────────────────────────────────────────────────────────
// config.php — Application configuration
// Author: dblack
// Email: dblack@merchante.com
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

$_config = [
    '_database' => [
        'type' => 'file',
    ],
    '_routes' => [
        '__default' => 'welcome',
        '__404'     => 'notfound/index',
    ],
];
