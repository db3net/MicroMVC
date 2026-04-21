# Security

## Built-in protections

- **Controller name validation** — only alphanumeric class/method names accepted; path traversal rejected
- **Framework class blocking** — internal classes (`Config`, `Router`, `Loader`, etc.) cannot be instantiated via URL
- **Method access control** — underscore-prefixed methods, magic methods, and base `Controller` internals are blocked
- **View path traversal protection** — `realpath()` validation ensures views load only from `views/`
- **JSONStore path sanitization** — collection names stripped to `[a-zA-Z0-9_-]`
- **Controller type checking** — only classes extending `Controller` can be dispatched

## Your responsibility

MicroMVC is a micro-framework — these are on you:

- **Output escaping** — use `htmlspecialchars()` in views to prevent XSS
- **CSRF protection** — add token validation for state-changing forms
- **Authentication / authorization** — implement in your controllers or a base controller
- **Rate limiting** — handle at the web server or load balancer level
- **Input validation** — validate and sanitize user input in your controllers
- **HTTPS** — configure TLS at the web server level

A good pattern is to create a `BaseController` that extends `Controller` with auth checks, then extend that for protected routes.
