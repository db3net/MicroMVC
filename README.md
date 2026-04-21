# MicroMVC

A radically lightweight, single-file PHP micro-framework. Routing, controllers, views, and a JSON file-store — the entire MVC stack in one ~500-line file with zero dependencies. Drop it in, point at PHP, and go.

## Why?

Most PHP frameworks trade simplicity for features you'll never use. MicroMVC goes the other way: **one file, zero dependencies, instant startup**. No autoloader to build, no service container to boot, no dependency graph to resolve. Just PHP doing what PHP does best — serving requests fast.

- **~20KB** on disk. The whole framework.
- **No `composer install`**. No vendor directory. No lock files.
- **No configuration ceremony**. One config file, plain arrays, done.
- **Sub-millisecond framework overhead**. Your code is the bottleneck, not ours.
- **~30MB Docker image**. Perfect for microservices where every megabyte and every cold-start millisecond counts.

If you need middleware pipelines, dependency injection, and an ORM — use Laravel. If you need a controller, a view, and a route running in 30 seconds — use this.

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

Open `http://localhost:8080` — you should see the welcome page.

For production, see [Server Setup](#server-setup) below.

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

MicroMVC uses clean URLs out of the box — no query strings, no `index.php` in the URL:

```
https://mysite.com/users/show/42
                   ─────┬─────────
                   controller/method/args
```

A request hits `public/index.php` (via server rewrite rules), which calls `Context::run()`. The router parses the URL path, matches the first segment against `config/config.php`, and dispatches to the right controller and method.

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

## Server Setup

MicroMVC supports clean URLs (`/controller/method/arg1/arg2`) on all major server configurations. The `public/` directory is the document root — only `index.php` and static assets are exposed.

### Apache

Point your virtual host document root to the `public/` directory. The included `.htaccess` handles rewriting automatically.

```apache
<VirtualHost *:80>
    ServerName mysite.com
    DocumentRoot /var/www/mysite/public

    <Directory /var/www/mysite/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Make sure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

The `.htaccess` file rewrites all non-file, non-directory requests to `index.php`:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [QSA,L]
```

### Nginx

```nginx
server {
    listen 80;
    server_name mysite.com;
    root /var/www/mysite/public;
    index index.php;

    # Route all requests through index.php (clean URLs)
    location / {
        try_files $uri $uri/ /index.php$uri?$query_string;
    }

    # Pass PHP requests to PHP-FPM
    location ~ \.php(/|$) {
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    # Block access to dotfiles
    location ~ /\. {
        deny all;
    }
}
```

### PHP Built-in Server

For local development, PHP's built-in server works with no extra config:

```bash
php -S localhost:8080 -t public
```

Clean URLs work automatically — the server falls back to `index.php` when a file isn't found.

## Containers & Microservices

MicroMVC is a natural fit for containerized deployments. Zero dependencies means no `composer install`, no vendor directory, and no build step. Your Dockerfile is trivial and your image is tiny.

### Dockerfile

```dockerfile
FROM php:8.3-alpine

COPY . /app
WORKDIR /app

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
```

Build and run:

```bash
docker build -t my-service .
docker run -p 8080:8080 my-service
```

The resulting image is ~30MB. Cold starts are near-instant.

### Why this works for microservices

- **Minimal attack surface** — no framework dependencies to patch, no supply chain risk
- **Fast startup** — no autoloader to build, no config cache to warm, no service container to boot
- **Small images** — `php:alpine` + your code, nothing else
- **One service, one concern** — each container gets its own MicroMVC app with just the controllers it needs
- **Easy to replicate** — spin up a new microservice by copying the skeleton and adding a controller

Each microservice is just a Dockerfile, a config file, and a few controllers. No shared framework state, no dependency conflicts between services, no monorepo coordination.

### Docker Compose example

```yaml
services:
  users-api:
    build: ./services/users
    ports: ["8081:8080"]
  orders-api:
    build: ./services/orders
    ports: ["8082:8080"]
  notifications:
    build: ./services/notifications
    ports: ["8083:8080"]
```

Each service is an independent MicroMVC app with its own routes, controllers, and data store.

## Security

MicroMVC includes baseline protections against common attacks:

- **Controller name validation** — only alphanumeric class/method names are accepted; path traversal attempts like `../../etc/passwd` are rejected
- **Framework class blocking** — internal classes (`Config`, `Router`, `Loader`, etc.) cannot be instantiated via URL
- **Method access control** — underscore-prefixed methods, magic methods (`__construct`, `__toString`, etc.), and base `Controller` internals are not callable from the URL
- **View path traversal protection** — `realpath()` validation ensures views can only be loaded from the `views/` directory
- **JSONStore path sanitization** — collection names are stripped to `[a-zA-Z0-9_-]` to prevent file path injection
- **Controller type checking** — only classes extending `Controller` can be dispatched

### What's NOT included

MicroMVC is a micro-framework — these are your responsibility:

- **Output escaping** — use `htmlspecialchars()` in your views to prevent XSS
- **CSRF protection** — add token validation for state-changing forms
- **Authentication / authorization** — implement in your controllers or a base controller
- **Rate limiting** — handle at the web server or load balancer level
- **Input validation** — validate and sanitize user input in your controllers
- **HTTPS** — configure TLS at the web server level

A good pattern is to create a `BaseController` that extends `Controller` with auth checks, then extend that for protected routes.

## Configuration Reference

`config/config.php` returns an array with these keys:

| Key | Purpose | Example |
|---|---|---|
| `_routes` | URL slug → controller/method mapping | `['dash' => 'admin/dashboard']` |
| `_database` | Storage backend config | `['type' => 'file']` |

You can add your own keys and access them anywhere with `Config::forKey('your_key')`.

## License

GPL-3.0 — see [LICENSE](LICENSE) for details.
