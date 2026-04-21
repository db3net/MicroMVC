# MicroMVC

A single-file, zero-dependency PHP micro-framework. The entire MVC framework — routing, controllers, views, and a JSON file-store — lives in one file: `src/MicroMVC.php`.

## Why?

Sometimes you don't need Laravel. You need a controller, a view, and a route — and you need it running in 30 seconds. MicroMVC gives you a proper MVC structure with no composer dependencies, no build step, and no configuration ceremony.

## Requirements

- PHP 8.1+

That's it. No extensions, no Composer packages, no database.

## Getting Started

### 1. Clone

```bash
git clone https://github.com/db3net/MicroMVC.git
cd MicroMVC
```

### 2. Run

**PHP built-in server (fastest way to start):**

```bash
php -S localhost:8080 -t public
```

**Apache:** Point your virtual host's document root to the `public/` directory. The included `.htaccess` handles URL rewriting automatically.

**Nginx:** Route all requests to `public/index.php`:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 3. Visit

Open `http://localhost:8080` — you should see the welcome page.

## Project Structure

```
MicroMVC/
├── src/MicroMVC.php         # The entire framework — one file
├── public/
│   ├── index.php            # Web entry point (3 lines)
│   └── .htaccess            # Apache URL rewriting
├── config/config.php        # Routes and settings
├── controllers/             # Your controllers go here
├── views/                   # Your view templates go here
├── models/                  # Your models go here (optional)
├── data/                    # JSON file-store writes here
└── composer.json            # Optional — for autoloading convenience
```

## How It Works

### Routing

A request hits `public/index.php`, which calls `Context::run()`. The router reads the URL (or CLI args), matches the first segment against `config/config.php`, and dispatches to the right controller and method.

**URL pattern:** `/controller/method/arg1/arg2/...`

Define routes in `config/config.php`:

```php
$_config = [
    '_routes' => [
        '__default' => 'welcome',           // / → welcome::index()
        '__404'     => 'notfound/index',     // unmatched routes
        'dash'      => 'admin/dashboard',    // /dash → admin::dashboard()
    ],
];
```

- `__default` — where `/` goes
- `__404` — where unmatched routes go
- Everything else maps a URL slug to `controller/method`

If a URL doesn't match any route key, the segments are used directly as `controller/method`. So `/users/list` calls `users::list()` with no config entry needed.

Arguments after the method are passed through automatically: `/users/show/42` calls `users::show('42')`.

### Writing a Controller

Create a file in `controllers/` matching the class name:

```php
// controllers/users.php
class users extends Controller
{
    public function index(): void
    {
        $this->display('users/list', [
            'users' => ['Alice', 'Bob', 'Charlie'],
        ]);
    }

    public function show(string $id = ''): void
    {
        $this->json_output(['user_id' => $id]);
    }
}
```

Controllers are auto-loaded by name — no registration or config needed.

**Available methods on `Controller`:**

| Method | What it does |
|---|---|
| `$this->display('view', $data)` | Render a PHP view template |
| `$this->display('view', $data, true)` | Render and return as string |
| `$this->json_output($data)` | Echo JSON response |
| `$this->json_output($data, true)` | Return JSON as string |

### Writing a View

Views are plain PHP files in `views/`. The `$data` array you pass from the controller is extracted into local variables:

```php
<!-- views/users/list.php -->
<h1>Users</h1>
<ul>
    <?php foreach ($users as $user): ?>
        <li><?= htmlspecialchars($user) ?></li>
    <?php endforeach; ?>
</ul>
```

### CLI Mode

The same controllers work from the command line — no changes needed:

```bash
# Calls welcome::index()
php public/index.php welcome

# Calls users::show('42')
php public/index.php users/show/42
```

This makes it easy to build CLI tools, cron jobs, or admin scripts that share logic with your web app.

### JSON File-Store

A simple key/value store backed by JSON files in the `data/` directory. No database setup required.

```php
// Store a record
JSONStore::put('users', 'user_42', ['name' => 'David', 'role' => 'admin']);

// Retrieve it
$user = JSONStore::fetch('users', 'user_42');
// → ['name' => 'David', 'role' => 'admin']

// Append to a log file
JSONStore::log('audit', 'login', ['user' => 'user_42', 'ip' => '10.0.0.1']);
```

Data is stored as `data/users.json`, `data/audit.json`, etc. — human-readable and easy to inspect.

## Configuration Reference

`config/config.php` returns an array with these keys:

| Key | Purpose | Example |
|---|---|---|
| `_routes` | URL slug → controller/method mapping | `['dash' => 'admin/dashboard']` |
| `_database` | Storage backend config | `['type' => 'file']` |

You can add your own keys and access them anywhere with `Config::forKey('your_key')`.

## License

GPL-3.0 — see [LICENSE](LICENSE) for details.
