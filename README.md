# MicroMVC

A radically lightweight, single-file PHP micro-framework. The entire MVC stack — routing, controllers, views, models, and a JSON file-store — in one file with zero dependencies making this possibly the lightest PHP MVC framework.

- **~20KB** on disk. The whole framework.
- **No `composer install`**. No vendor directory. No lock files.
- **Sub-millisecond overhead**. Your code is the bottleneck, not ours.
- **~30MB Docker image**. Built for containers and microservices.

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
        $this->json_output(['message' => 'Hello from MicroMVC!']);
    }

    public function greet(string $name = 'world'): void
    {
        $this->json_output(['hello' => $name]);
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

This repo contains both the framework source and the starter project:

```
MicroMVC/                  # Framework development repo
├── src/MicroMVC.php       # Framework source
├── starter/               # Starter project (what users install)
├── tests/                 # Framework test suite
├── docs/                  # Documentation
├── install.sh             # One-line installer
└── README.md
```

## License

GPL-3.0 — see [LICENSE](LICENSE) for details.
