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

    // ── Named Connections ───────────────────────────────────────────────────
    //
    // All models use named connections. Each key is a connection name that
    // models reference via connectionName(). Supported drivers:
    //
    //   'file'   — JSON flat-file store (JSONModel)
    //   'mysql'  — MySQL / MariaDB      (MySQLModel)
    //   'pgsql'  — PostgreSQL            (PGModel)
    //
    // Every model defaults to the 'default' connection unless it overrides
    // connectionName(). The pattern is the same for all model types:
    //
    //   class User extends JSONModel {
    //       protected static function connectionName(): string { return 'default'; }
    //       ...
    //   }
    //
    //   class Order extends MySQLModel {
    //       protected static function connectionName(): string { return 'orders_db'; }
    //       ...
    //   }

    'connections' => [

        // JSON flat-file store — data lives in the data/ directory.
        // This is the default. No database needed.
        'default' => [
            'driver' => 'file',
            'path'   => 'data',       // directory for .json files
        ],

        // ── Additional connections (uncomment to enable) ────────────────

        // // Second file store — e.g. for logs in a separate directory
        // 'logs' => [
        //     'driver' => 'file',
        //     'path'   => 'data/logs',
        // ],

        // // MySQL connection
        // 'mysql_main' => [
        //     'driver'   => 'mysql',
        //     'host'     => '127.0.0.1',
        //     'port'     => 3306,
        //     'dbname'   => 'myapp',
        //     'user'     => 'root',
        //     'password' => '',
        // ],

        // // PostgreSQL connection
        // 'analytics' => [
        //     'driver'   => 'pgsql',
        //     'host'     => '127.0.0.1',
        //     'port'     => 5432,
        //     'dbname'   => 'analytics',
        //     'user'     => 'postgres',
        //     'password' => '',
        // ],

    ],

];
