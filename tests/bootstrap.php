<?php
// ──────────────────────────────────────────────────────────────────────────────
// Test bootstrap — loads the framework and sets the working directory
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

// PHPUnit autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Set working directory to project root so Loader, views/, controllers/ resolve
chdir(__DIR__ . '/..');
