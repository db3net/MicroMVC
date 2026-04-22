<?php
// ──────────────────────────────────────────────────────────────────────────────
// config.php — Application configuration
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

$_config = [

    // ── Routes ──────────────────────────────────────────────────────────────
    //
    // Map URL slugs to controller/method pairs.
    //   '__default'  — where "/" goes
    //   '__404'      — where unmatched routes go
    //   'slug'       — maps /slug to controller/method
    //
    // If a URL doesn't match any key here, segments are used directly:
    //   /users/show/42  →  users::show('42')

    '_routes' => [
        '__default' => 'welcome',
        '__404'     => 'notfound/index',
    ],

    // ── Storage ──────────────────────────────────────────────────────────────
    //
    // By default, MicroMVC uses a JSON flat-file store (the data/ directory).
    // JSONModel works out of the box with zero configuration — no database
    // needed. Just extend JSONModel and start saving.
    //
    // To use MySQL or PostgreSQL instead, extend MySQLModel or PGModel and
    // configure a named connection below.

    '_database' => [
        'type' => 'file',   // 'file' = JSON flat-file store (default)
    ],

    // ── Database Connections ────────────────────────────────────────────────
    //
    // Named connections used by MySQLModel and PGModel. Each key is a
    // connection name that models reference via connectionName().
    //
    // Supported drivers: 'mysql', 'pgsql'
    //
    // Models use 'default' unless they override connectionName():
    //
    //   class Order extends MySQLModel {
    //       protected static function connectionName(): string { return 'default'; }
    //       protected static function table(): string { return 'orders'; }
    //       ...
    //   }
    //
    //   class Report extends PGModel {
    //       protected static function connectionName(): string { return 'analytics'; }
    //       protected static function table(): string { return 'reports'; }
    //       ...
    //   }
    //
    // Uncomment and edit the examples below to enable database connections.

    // 'connections' => [
    //
    //     // Default MySQL connection
    //     'default' => [
    //         'driver'   => 'mysql',
    //         'host'     => '127.0.0.1',
    //         'port'     => 3306,
    //         'dbname'   => 'myapp',
    //         'user'     => 'root',
    //         'password' => '',
    //     ],
    //
    //     // PostgreSQL analytics database
    //     'analytics' => [
    //         'driver'   => 'pgsql',
    //         'host'     => '127.0.0.1',
    //         'port'     => 5432,
    //         'dbname'   => 'analytics',
    //         'user'     => 'postgres',
    //         'password' => '',
    //     ],
    //
    // ],

];
