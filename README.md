# MicroMVC

A radically lightweight, single-file PHP micro-framework. The entire MVC stack — routing, controllers, views, and a JSON file-store — in one ~500-line file with zero dependencies.

- **~20KB** on disk. The whole framework.
- **No `composer install`**. No vendor directory. No lock files.
- **Sub-millisecond overhead**. Your code is the bottleneck, not ours.
- **~30MB Docker image**. Built for containers and microservices.

## Quick Start

### 1. Get it

```bash
git clone https://github.com/db3net/MicroMVC.git
cd MicroMVC
```

### 2. Create a controller

```php
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
```

### 3. Run it

**Option A — PHP:**

```bash
php -S localhost:8080 -t public
```

**Option B — Docker:**

```bash
docker build -t myapp .
docker run -p 8080:8080 myapp
```

That's it. Hit `http://localhost:8080/hello` or `http://localhost:8080/hello/greet/David`.

## How URLs Work

```
https://mysite.com/hello/greet/David
                   ──┬── ──┬── ──┬──
               controller method  arg
```

`/hello/greet/David` → calls `hello::greet('David')`. No route config needed — the URL *is* the route. Need aliases? See [Routing](docs/routing.md).

## Project Structure

```
MicroMVC/
├── src/MicroMVC.php       # The entire framework — one file
├── public/index.php       # Entry point (3 lines)
├── config/config.php      # Routes and settings
├── controllers/           # Your controllers
├── views/                 # Your templates
├── data/                  # JSON file-store
├── Dockerfile             # Production-ready Alpine image
└── Dockerfile.alpine      # Minimal Alpine + PHP-FPM image
```

## Documentation

| Guide | What's covered |
|---|---|
| [Controllers & Views](docs/controllers-and-views.md) | Writing controllers, rendering views, JSON output |
| [Routing](docs/routing.md) | Route config, default routes, 404 handling |
| [CLI Mode](docs/cli.md) | Running controllers from the command line |
| [JSON File-Store](docs/json-store.md) | Reading, writing, and logging data |
| [Server Setup](docs/server-setup.md) | Apache, Nginx, and PHP built-in server |
| [Docker & Microservices](docs/docker.md) | Dockerfiles, Compose, microservice patterns |
| [Security](docs/security.md) | Built-in protections and your responsibilities |
| [Configuration](docs/configuration.md) | Config keys and custom settings |

## License

GPL-3.0 — see [LICENSE](LICENSE) for details.
