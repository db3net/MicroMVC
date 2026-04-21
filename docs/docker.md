# Docker & Microservices

MicroMVC is a natural fit for containers. Zero dependencies means no `composer install`, no vendor directory, and no build step.

## Dockerfiles

Two Dockerfiles are included:

### `Dockerfile` — PHP built-in server (simplest)

```dockerfile
FROM php:8.3-alpine
COPY . /app
WORKDIR /app
EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
```

Good for development, internal tools, and low-traffic services. Image size: **~30MB**.

### `Dockerfile.alpine` — Nginx + PHP-FPM (production)

A production-grade image with Nginx and PHP-FPM on Alpine Linux. Image size: **~40MB**.

Use this when you need:
- Static file serving
- Connection handling under load
- Standard production PHP-FPM tuning

## Build & Run

```bash
# Development
docker build -t myapp .
docker run -p 8080:8080 myapp

# Production
docker build -f Dockerfile.alpine -t myapp .
docker run -p 80:80 myapp
```

## Why this works for microservices

- **Minimal attack surface** — no framework dependencies to patch, no supply chain risk
- **Fast startup** — no autoloader to build, no config cache to warm, no service container to boot
- **Small images** — `php:alpine` + your code, nothing else
- **One service, one concern** — each container gets its own MicroMVC app with just the controllers it needs
- **Easy to replicate** — spin up a new microservice by copying the skeleton and adding a controller

## Docker Compose example

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
