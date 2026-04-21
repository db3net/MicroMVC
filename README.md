# MicroMVC

Single-file, zero-dependency PHP MVC framework with routing, views, CLI support, and a simple JSON file-store.

## Features

- **One file** — the entire framework lives in `src/MicroMVC.php`
- **URL and CLI routing** — same controllers work from the browser and the command line
- **Simple config-based routes** — map URL slugs to controller/method pairs
- **Views with data extraction** — pass an array, get variables in your template
- **JSON file-store** — lightweight key/value persistence with no database required
- **Zero dependencies** — just PHP 8.1+

## Project Structure

```
MicroMVC/
├── src/
│   └── MicroMVC.php        # The framework (single file)
├── config/
│   └── config.php           # Routes and app configuration
├── controllers/             # Your controller classes
├── views/                   # PHP view templates
├── models/                  # Model classes (optional)
├── data/                    # JSON file-store data directory
├── public/
│   ├── index.php            # Web entry point
│   └── .htaccess            # Apache rewrite rules
├── composer.json
└── LICENSE
```

## Quick Start

```bash
git clone https://github.com/db3net/MicroMVC.git
cd MicroMVC

# Option A: PHP built-in server
php -S localhost:8080 -t public

# Option B: Apache — point your vhost document root to the public/ directory
```

Visit `http://localhost:8080` and you should see the welcome page.

## Routing

Routes are defined in `config/config.php`:

```php
$_config = [
    '_routes' => [
        '__default' => 'welcome',          // GET / → welcome::index()
        '__404'     => 'notfound/index',    // fallback
        'dashboard' => 'admin/dashboard',   // GET /dashboard → admin::dashboard()
    ],
];
```

URLs follow the pattern `index.php?/<controller>/<method>/arg1/arg2/...`

With the included `.htaccess`, clean URLs work automatically: `/dashboard/settings/arg1`

Any path segments after the controller/method are passed as arguments to the method.

## Controllers

Controllers extend the base `Controller` class:

```php
class welcome extends Controller
{
    public function index(): void
    {
        $this->display('welcome', [
            'title'   => 'Hello',
            'message' => 'Welcome to MicroMVC.',
        ]);
    }

    public function greet(string $name = 'world'): void
    {
        $this->json_output(['greeting' => "Hello, $name!"]);
    }
}
```

Place controller files in `controllers/` — they are auto-loaded by name.

## Views

Views are plain PHP files in `views/`. Data passed from the controller is extracted into local variables:

```php
<!-- views/welcome.php -->
<h1><?= htmlspecialchars($title) ?></h1>
<p><?= htmlspecialchars($message) ?></p>
```

## CLI Usage

The same controllers work from the command line:

```bash
php public/index.php welcome/greet/David
```

## JSON File-Store

A simple key/value store backed by JSON files in the `data/` directory:

```php
// Write
JSONStore::put('users', 'user_123', ['name' => 'David', 'role' => 'admin']);

// Read
$user = JSONStore::fetch('users', 'user_123');

// Log
JSONStore::log('audit', 'login', ['user' => 'user_123']);
```

## Requirements

- PHP 8.1+
- Apache with `mod_rewrite` (for clean URLs), or use PHP's built-in server

## License

GPL-3.0 — see [LICENSE](LICENSE) for details.
