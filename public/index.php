<?php
// ──────────────────────────────────────────────────────────────────────────────
// MicroMVC — Application entry point
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 MerchantE. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

chdir(dirname(__DIR__));

require_once 'src/MicroMVC.php';

Context::run();
();
