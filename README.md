# MicroMVC

A radically lightweight PHP micro-framework where the entire stack — routing, controllers, views, models, and a JSON file-store — lives in a single file with zero dependencies. No Composer. No vendor directory. No build step. Just one file and you're shipping.

While most frameworks require a package manager, hundreds of dependencies, generated autoloaders, and cache warming before you write a line of code, MicroMVC skips all of that. Drop one file into your project and you have a complete MVC stack with convention-based routing, swappable model backends (JSON, MySQL, PostgreSQL), a built-in file store, CLI support, and six layers of security hardened into the core — input validation, path-traversal protection, method-access controls, framework-class blocking, controller type checking, and collection-name sanitization.

It's built for developers who want to move fast without dragging a supply chain behind them. REST APIs, internal tools, microservices, containerized workloads, rapid prototypes — anywhere you need PHP that starts instantly and stays out of your way.

- **~35KB total footprint**. One file. That's the whole framework.
- **Zero dependencies**. No Composer, no lock files, no autoloader ceremony, no supply chain risk.
- **Sub-millisecond overhead**. Your code is the bottleneck, not the framework.
- **~30MB Docker image**. Alpine + your code, nothing else. Purpose-built for containers.
- **Convention-based routing**. URLs map directly to controllers — no route files to maintain.
- **Three model backends**. JSONModel for file storage, MySQLModel and PGModel for databases. Swap with one line.
- **Security by default**. Six built-in protections so you're hardened from the first request.
- **CLI-ready**. Run any controller from the command line with the same routing logic.

## Install

One command. Pick a name for your project and go:

```bash
curl -fsSL https://raw.githubusercontent.com/db3net/MicroMVC/master/install.sh | bash -s myapp
```

That clones the starter project into `./myapp` — a self-contained app ready to run. No framework repo, no extra files, just your project.

Then:

```bash
cd myapp
php -S localhost:8080 -t public
```

Open `http://localhost:8080` and you're live.

### Alternative: manual setup

```bash
git clone --depth 1 https://github.com/db3net/MicroMVC.git
cp -r MicroMVC/starter myapp
rm -rf MicroMVC
cd myapp
php -S localhost:8080 -t public
```

### Docker

```bash
cd myapp
docker build -t myapp .
docker run -p 8080:8080 myapp
```

## Quick Start

### Create a controller

```php
<?php
// controllers/hello.php
class hello extends Controller
{
    public function index(): void
    {
        $this->jsonOutput(['message' => 'Hello from MicroMVC!']);
    }

    public function greet(string $name = 'world'): void
    {
        $this->jsonOutput(['hello' => $name]);
    }
}
?>
```

Hit `http://localhost:8080/hello` or `http://localhost:8080/hello/greet/David`.

## How URLs Work

```
https://mysite.com/hello/greet/David
                   ──┬── ──┬── ──┬──
               controller method  arg
```

`/hello/greet/David` → calls `hello::greet('David')`. No route config needed — the URL *is* the route. Need aliases? See [Routing](docs/routing.md).

## Your Project

```
myapp/
├── src/MicroMVC.php       # The entire framework — one file
├── public/index.php       # Entry point (3 lines)
├── config/config.php      # Routes and settings
├── controllers/           # Your controllers
├── models/                # Your models
├── views/                 # Your templates
├── data/                  # JSON file-store (auto-created)
├── Dockerfile             # Production-ready Alpine image
└── Dockerfile.alpine      # Minimal Alpine + PHP-FPM image
```

## Documentation

| Guide | What's covered |
|---|---|
| [Controllers & Views](docs/controllers-and-views.md) | Writing controllers, rendering views, JSON output |
| [Models](docs/models.md) | JSONModel, MySQLModel, PGModel — switching backends |
| [Routing](docs/routing.md) | Route config, default routes, 404 handling |
| [CLI Mode](docs/cli.md) | Running controllers from the command line |
| [JSON File-Store](docs/json-store.md) | Reading, writing, and logging data |
| [Server Setup](docs/server-setup.md) | Apache, Nginx, and PHP built-in server |
| [Docker & Microservices](docs/docker.md) | Dockerfiles, Compose, microservice patterns |
| [Security](docs/security.md) | Built-in protections and your responsibilities |
| [Configuration](docs/configuration.md) | Config keys and custom settings |

## Repository Structure

This repo contains both the framework source and a working app:

```
MicroMVC/                  # Framework development repo
├── src/MicroMVC.php       # Framework source
├── config/                # App configuration
├── controllers/           # Example controllers
├── models/                # Example models
├── views/                 # Example views
├── public/                # Web entry point
├── tests/                 # Framework test suite
├── docs/                  # Documentation
├── install.sh             # One-line installer
└── README.md
```

The installer (`install.sh`) copies the app files directly from this repo — no separate starter directory to maintain.

## License

GPL-3.0 — see [LICENSE](LICENSE) for details.
