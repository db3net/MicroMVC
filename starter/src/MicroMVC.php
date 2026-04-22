<?php
// ──────────────────────────────────────────────────────────────────────────────
// MicroMVC — Single-file, zero-dependency PHP MVC framework
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// ── Autoloader for models ───────────────────────────────────────────────────

spl_autoload_register(function (string $class): void {
    $path = 'models/' . $class . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

// ── Configuration ───────────────────────────────────────────────────────────

/**
 * Loads and caches application configuration from config/config.php.
 */
class Config
{
    /** @var array<string, mixed>|null */
    private static ?array $_config = null;

    /**
     * Get a top-level configuration value by key.
     *
     * If a global $_config is defined (e.g. injected in tests), it takes precedence.
     *
     * @param  string $key Configuration key (e.g. '_routes', '_database').
     * @return mixed
     */
    public static function forKey(string $key): mixed
    {
        global $_config;
        if (!empty($_config)) {
            return $_config[$key] ?? null;
        }

        if (self::$_config === null) {
            self::$_config = Loader::loadConfig();
        }
        return self::$_config[$key] ?? null;
    }
}


// ── Context / Bootstrap ─────────────────────────────────────────────────────

/**
 * Application entry point — detects environment and dispatches the request.
 */
class Context
{
    /** Check whether we're running from the command line. */
    public static function isCLI(): bool
    {
        return (php_sapi_name() === 'cli');
    }

    /**
     * Resolve the route, load the controller, and call the method.
     */
    public static function run(): void
    {
        $class     = Router::getClass();
        $method    = Router::getMethod();
        $arguments = Router::getArgs();

        // Sanitize: class and method must be simple identifiers
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $class) ||
            ($method !== '' && !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $method))) {
            self::abort(400, 'Invalid request');
            return;
        }

        // Block access to framework internals
        $reserved = ['Config', 'Context', 'Controller', 'View', 'Request',
            'Input', 'Loader', 'Router', 'URL', 'CLI', 'M2Object', 'Store', 'JSONStore',
            'Model', 'JSONModel', 'MySQLModel', 'PGModel'];
        if (in_array($class, $reserved, true)) {
            self::abort(403, 'Forbidden');
            return;
        }

        if (!class_exists($class)) {
            $path = 'controllers/' . $class . '.php';
            if (file_exists($path)) {
                include_once $path;
            }
        }

        if (!class_exists($class)) {
            self::abort(404, 'Not found');
            return;
        }

        if ($method === '') {
            $method = 'index';
        }

        $controller = new $class();

        if (!($controller instanceof Controller)) {
            self::abort(403, 'Forbidden');
            return;
        }

        $controller->_call($method, $arguments);
    }

    /**
     * Send an error response and stop execution.
     */
    private static function abort(int $code, string $message): void
    {
        if (!self::isCLI()) {
            http_response_code($code);
        }
        echo $message;
    }
}


// ── Controller ──────────────────────────────────────────────────────────────

/**
 * Base controller — extend this for each route handler.
 */
class Controller
{
    /**
     * Dispatch a method call with arguments.
     *
     * Only public methods defined on the controller subclass are callable.
     * Methods starting with _ and inherited Controller methods are blocked.
     *
     * @param string               $method Method name to invoke.
     * @param array<int, mixed>    $args   Positional arguments.
     */
    public function _call(string $method, array $args = []): void
    {
        // Block internal/magic methods and base Controller methods
        $blocked = ['_call', 'output', 'json_output', 'display', '__construct',
            '__destruct', '__call', '__get', '__set', '__toString'];
        if (str_starts_with($method, '_') || in_array($method, $blocked, true)) {
            echo "Method named: '{$method}' is not accessible";
            return;
        }

        if (method_exists($this, $method)) {
            call_user_func_array([$this, $method], $args);
        } else {
            echo "Method named: '{$method}' doesn't exist";
        }
    }

    /** Default action — override in subclasses. */
    public function index(): void
    {
        echo 'index';
    }

    /**
     * Output data as JSON.
     *
     * @param string $type   Output type (currently only 'json').
     * @param mixed  $data   Data to encode.
     * @param string $view   Unused — reserved for future output types.
     * @param bool   $as_var If true, return the string instead of echoing.
     * @param int    $options json_encode option flags.
     * @return string|void
     */
    public function output(
        string $type = 'json',
        mixed $data = '',
        string $view = '',
        bool $as_var = false,
        int $options = JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES
    ): mixed {
        return $this->json_output($data, $as_var, $options);
    }

    /**
     * Encode data as JSON and echo or return it.
     *
     * @param mixed $data    Data to encode.
     * @param bool  $as_var  If true, return the JSON string.
     * @param int   $options json_encode option flags.
     * @return string|void
     */
    public function json_output(
        mixed $data,
        bool $as_var = false,
        int $options = JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES
    ): mixed {
        $out = json_encode($data, $options);
        if ($as_var) {
            return $out;
        }
        echo $out;
        return null;
    }

    /**
     * Render a view template.
     *
     * @param string              $view   View name (without .php extension).
     * @param array<string,mixed> $data   Variables to extract into the view.
     * @param bool                $as_var If true, return rendered HTML instead of echoing.
     * @return string|void
     */
    public function display(string $view, array $data = [], bool $as_var = false): mixed
    {
        return View::render($view, $data, $as_var);
    }
}


// ── View ────────────────────────────────────────────────────────────────────

/**
 * Simple view renderer — loads a PHP template and extracts data into scope.
 */
class View
{
    /**
     * @param string              $view   View name (without .php extension).
     * @param array<string,mixed> $data   Variables to extract.
     * @param bool                $as_var If true, capture and return output.
     * @return string|void
     */
    public static function render(string $view, array $data = [], bool $as_var = false): mixed
    {
        return Loader::loadView($view . '.php', $data, $as_var);
    }
}


// ── Request / Input ─────────────────────────────────────────────────────────

/**
 * Convenience accessors for the current request context.
 */
class Request
{
    /**
     * Determine the controller class name from the calling file path.
     */
    public static function current_file(): ?string
    {
        $file = debug_backtrace()[1]['file'] ?? '';
        $segments = explode('/', $file);
        foreach ($segments as $segment) {
            if (strtolower(substr($segment, -4)) === '.php') {
                $class = substr($segment, 0, stripos($segment, '.php'));
                return ucwords(strtolower($class));
            }
        }
        return null;
    }

    /** Get the current method from the input. */
    public static function method(): mixed
    {
        return Input::first();
    }

    /** Get all arguments after the first input segment. */
    public static function args(): array
    {
        return Input::remainder_after_first();
    }
}

/**
 * Unified input abstraction — works for both URL query strings and CLI argv.
 */
class Input
{
    /** First argument. */
    public static function first(): mixed
    {
        return self::allArgs(0);
    }

    /** Second argument. */
    public static function second(): mixed
    {
        return self::allArgs(1);
    }

    /** Third argument. */
    public static function third(): mixed
    {
        return self::allArgs(2);
    }

    /**
     * All arguments after the first.
     *
     * @return array<int, mixed>
     */
    public static function remainder_after_first(): array
    {
        $args = self::allArgs();
        return array_slice($args, 1);
    }

    /**
     * Get all arguments, or a single argument by index.
     *
     * @param  int $index -1 for all, otherwise the 0-based index.
     * @return mixed       Array of all args, a single arg, or false if not found.
     */
    public static function allArgs(int $index = -1): mixed
    {
        if (Context::isCLI()) {
            $args = CLI::enumeratedArgs();
        } else {
            $args = URL::enumeratedArgs();
        }

        if ($index === -1) {
            return $args;
        }
        return $args[$index] ?? false;
    }
}


// ── Loader ──────────────────────────────────────────────────────────────────

/**
 * File loader for configuration and view templates.
 */
class Loader
{
    /**
     * Load config/config.php and return the $_config array.
     *
     * @return array<string, mixed>
     */
    public static function loadConfig(): array
    {
        if (file_exists('config/config.php')) {
            include_once 'config/config.php';
            return $_config ?? [];
        }
        return [];
    }

    /**
     * Load a view template, extracting data into its scope.
     *
     * @param string              $view  Filename relative to views/.
     * @param array<string,mixed> $data  Variables to extract.
     * @param bool                $asVar If true, capture output and return it.
     * @return string|void
     */
    public static function loadView(string $view, array $data = [], bool $asVar = false): mixed
    {
        // Prevent path traversal
        $realBase = realpath('views');
        $realPath = realpath('views/' . $view);
        if ($realBase === false || $realPath === false || !str_starts_with($realPath, $realBase)) {
            echo 'View not found';
            return null;
        }

        extract($data);

        if ($asVar) {
            ob_start();
            include $realPath;
            return ob_get_clean();
        }
        include $realPath;
        return null;
    }
}


// ── Router ──────────────────────────────────────────────────────────────────

/**
 * Maps incoming requests to controller/method pairs using config-based routes.
 *
 * URL pattern: /controller/method/arg1/arg2/...
 * Legacy URL:  index.php?/controller/method/arg1/arg2/...
 * CLI pattern: php index.php controller/method/arg1/arg2/...
 */
class Router
{
    /** @var array<string, string> */
    private static array $routes = [];

    /**
     * Load and cache routes from configuration.
     *
     * @return array<string, string>
     */
    public static function routes(): array
    {
        if (count(self::$routes) > 0) {
            return self::$routes;
        }
        self::$routes = Config::forKey('_routes') ?? [];
        return self::$routes;
    }

    /**
     * Resolve a route key to [class, method] elements.
     *
     * If the key matches a configured route, split that route string.
     * Otherwise, treat the directive segments as class/method directly.
     *
     * @param  string $key The first URL/CLI segment (route key).
     * @return array{0: string, 1: string} [class, method]
     */
    public static function routeElementsForKey(string $key): array
    {
        $routes = self::routes();

        if (isset($routes[$key])) {
            $route = explode('/', ltrim($routes[$key], '/'));
            // Ensure we always have at least [class, method]
            return [$route[0], $route[1] ?? ''];
        }

        $directives = self::directive();
        return [$directives[0] ?? '', $directives[1] ?? ''];
    }

    /**
     * Get the raw directive segments from the request.
     *
     * @return string[]
     */
    public static function directive(): array
    {
        if (Context::isCLI()) {
            $directive = CLI::firstArg() ?? '';
        } else {
            $directive = URL::firstArg() ?? '';
        }
        return explode('/', $directive);
    }

    /**
     * Get the route key (first segment of the directive).
     */
    public static function key(): string
    {
        $directive = self::directive();
        $key = $directive[0] ?? '';

        // Fall back to the default route if no key provided
        if ($key === '') {
            $routes = self::routes();
            $key = '__default';
        }
        return $key;
    }

    /** Resolve the controller class name. */
    public static function getClass(): string
    {
        return self::routeElementsForKey(self::key())[0];
    }

    /** Resolve the controller method name. */
    public static function getMethod(): string
    {
        return self::routeElementsForKey(self::key())[1];
    }

    /**
     * Collect arguments — everything after controller/method in the directive.
     *
     * @return string[]
     */
    public static function getArgs(): array
    {
        $directive = self::directive();
        // Segments 0=class, 1=method, 2+=args
        return array_slice($directive, 2);
    }
}


// ── URL ─────────────────────────────────────────────────────────────────────

/**
 * URL/HTTP request helpers.
 *
 * Supports both clean URLs (/controller/method/arg) and legacy query-string
 * style (index.php?/controller/method/arg). Clean URLs take precedence.
 */
class URL
{
    /** Current request URI. */
    public static function uri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '';
    }

    /** Full URL including protocol and host. */
    public static function fullurl(): string
    {
        return self::protocol() . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . self::uri();
    }

    /** Whether the request is over HTTPS. */
    public static function https(): bool
    {
        return !empty($_SERVER['HTTPS']);
    }

    /** Current protocol string. */
    public static function protocol(): string
    {
        return self::https() ? 'https' : 'http';
    }

    /**
     * Extract the route path from the request.
     *
     * Checks PATH_INFO and REQUEST_URI path first (clean URLs), then falls
     * back to QUERY_STRING (legacy ?/controller/method style).
     *
     * @return string The route path, e.g. "controller/method/arg1/arg2"
     */
    public static function path(): string
    {
        // Clean URLs: PATH_INFO is set by Apache/Nginx when rewriting
        if (!empty($_SERVER['PATH_INFO'])) {
            return trim($_SERVER['PATH_INFO'], '/');
        }

        // Clean URLs: parse the path from REQUEST_URI (strip script name)
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir = dirname($script);

        // Remove the script directory prefix (e.g. /subdir) from the URI
        if ($scriptDir !== '/' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        }
        // Remove the script filename itself (e.g. /index.php)
        if (str_starts_with($uri, '/' . basename($script))) {
            $uri = substr($uri, strlen('/' . basename($script)));
        }

        $path = trim($uri, '/');
        if ($path !== '') {
            return $path;
        }

        // Legacy fallback: ?/controller/method/arg style
        $q = ltrim($_SERVER['QUERY_STRING'] ?? '', '/');
        // parse_str treats the first path as a key; just return it raw
        return strtok($q, '&') ?: '';
    }

    /**
     * Get path segments as an array.
     *
     * @return string[]
     */
    public static function segments(): array
    {
        $path = self::path();
        if ($path === '') {
            return [];
        }
        return explode('/', $path);
    }

    /**
     * Parse the query string into an associative array.
     *
     * @return array<string, string>
     */
    public static function args(): array
    {
        $q = ltrim($_SERVER['QUERY_STRING'] ?? '', '/');
        parse_str($q, $out);
        return $out;
    }

    /**
     * Enumerate URL segments into a flat list for the Input class.
     *
     * @return array<int, string>
     */
    public static function enumeratedArgs(): array
    {
        return self::segments();
    }

    /**
     * First URL segment — the route directive (e.g. "controller/method/args").
     */
    public static function firstArg(): string
    {
        return self::path();
    }
}


// ── CLI ─────────────────────────────────────────────────────────────────────

/**
 * CLI argument helpers.
 */
class CLI
{
    /**
     * Get all argv entries, or a single one by index.
     *
     * @param  int $ndx -1 for all, otherwise the 0-based index.
     * @return mixed
     */
    public static function args(int $ndx = -1): mixed
    {
        global $argv;
        return ($ndx === -1) ? ($argv ?? []) : ($argv[$ndx] ?? null);
    }

    /**
     * The first real argument (argv[1]), trimmed of slashes.
     */
    public static function firstArg(): ?string
    {
        $args = self::args();
        if (count($args) > 1) {
            return trim($args[1], '/');
        }
        return null;
    }

    /** The script filename (argv[0]). */
    public static function file(): ?string
    {
        return self::args(0);
    }

    /**
     * All arguments after the script name, as a flat list.
     *
     * @return string[]
     */
    public static function enumeratedArgs(): array
    {
        global $argv;
        $all = $argv ?? [];
        $len = count($all);
        $args = [];
        for ($i = 1; $i < $len; $i++) {
            $args[] = $all[$i];
        }
        return $args;
    }
}


// ── Model / Store ───────────────────────────────────────────────────────────

/**
 * Base object with KVC-style setter dispatch.
 */
class M2Object
{
    /**
     * Set a property by calling its setter method (set<Key>).
     *
     * @param mixed  $value The value to set.
     * @param string $key   The property name.
     */
    public function takeValueForKey(mixed $value, string $key): void
    {
        $setter = 'set' . ucwords(strtolower($key));

        if (method_exists($this, $setter) && is_callable([$this, $setter])) {
            $this->$setter($value);
        }
    }
}

/**
 * Base store class — extend for different backends.
 */
class Store extends M2Object
{
}

/**
 * Simple JSON file-based key/value store.
 *
 * Data is stored as <collection>.json files in a configurable directory.
 * The directory is resolved from a named connection via DB::dataDir().
 */
class JSONStore extends Store
{
    /**
     * Sanitize a collection name to prevent path traversal.
     */
    private static function safeName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
    }

    /**
     * Fetch a record by class and identifier.
     *
     * @param  string $class      The data collection name.
     * @param  string $identifier The record key.
     * @param  string $dataDir    Base directory for data files.
     * @return mixed  The stored value, or false if not found.
     */
    public static function fetch(string $class, string $identifier, string $dataDir = 'data'): mixed
    {
        $file = $dataDir . '/' . self::safeName($class) . '.json';
        if (!file_exists($file)) {
            return false;
        }
        $data = json_decode(file_get_contents($file), true);
        return $data[$identifier] ?? false;
    }

    /**
     * Store a record.
     *
     * @param string $class      The data collection name.
     * @param string $identifier The record key.
     * @param mixed  $data       The value to store.
     * @param string $dataDir    Base directory for data files.
     */
    public static function put(string $class, string $identifier, mixed $data, string $dataDir = 'data'): void
    {
        $file = $dataDir . '/' . self::safeName($class) . '.json';
        $dstore = [];
        if (file_exists($file)) {
            $dstore = json_decode(file_get_contents($file), true) ?? [];
        }
        $dstore[$identifier] = $data;

        file_put_contents(
            $file,
            json_encode($dstore, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT)
        );
    }

    /**
     * Append a timestamped log entry.
     *
     * @param string $class      The log category.
     * @param string $identifier The log key.
     * @param mixed  $data       Scalar or array data to log.
     * @param string $dataDir    Base directory for data files.
     */
    public static function log(string $class, string $identifier, mixed $data, string $dataDir = 'data'): void
    {
        if (!is_scalar($data)) {
            $data = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        }

        $buf = time() . ' | ' . self::safeName($class) . '.' . $identifier . ' | ' . $data . "\n";
        $logFile = $dataDir . '/log';

        $handle = fopen($logFile, 'a');
        if ($handle === false) {
            die('Cannot open file: ' . $logFile);
        }
        fwrite($handle, $buf);
        fclose($handle);
    }
}


// ── Debug Helpers ───────────────────────────────────────────────────────────

/**
 * Extract a short file context (parent dir + filename) from a full path.
 *
 * @param  string $path Absolute file path.
 * @return string
 */
function file_context_from_path(string $path): string
{
    $segments = explode(DIRECTORY_SEPARATOR, $path);
    $count = count($segments);
    $context = '';
    for ($i = max(0, $count - 2); $i < $count; $i++) {
        $context .= DIRECTORY_SEPARATOR . $segments[$i];
    }
    return $context;
}

/**
 * Build a caller-context string (or array) from the debug backtrace.
 *
 * @param  bool $asString If true, return a formatted string; otherwise an array.
 * @return string|array<string, mixed>
 */
function debug_context(bool $asString = true): string|array
{
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $caller = $backtrace[1] ?? [];

    $data = [
        'cfile_context' => file_context_from_path($caller['file'] ?? ''),
        'cclass'        => $caller['class'] ?? '',
        'cfunction'     => $caller['function'] ?? '',
        'ctype'         => $caller['type'] ?? '',
        'cline'         => $caller['line'] ?? 0,
    ];

    if (!$asString) {
        return $data;
    }

    return $data['cclass'] . $data['ctype'] . $data['cfunction']
        . ' [' . $data['cfile_context'] . ':' . $data['cline'] . ']';
}

/**
 * Pretty-print a value with caller context (debug helper).
 *
 * @param mixed $object Value to dump.
 */
function p(mixed $object): void
{
    echo "<pre>\n" . debug_context() . " \n\n";
    print_r($object);
    echo "\n__________________________________________________________________\n";
    echo "</pre>";
}


// ── Database Connection Registry ────────────────────────────────────────────

/**
 * DB — Named connection registry.
 *
 * Manages both PDO database connections and file-store paths. Connection
 * configs are defined in config/config.php under the 'connections' key.
 *
 * Usage:
 *   $pdo = DB::connection('default');       // MySQL or PostgreSQL
 *   $dir = DB::dataDir('default');          // JSON file-store path
 */
class DB
{
    /** @var array<string, PDO> */
    private static array $pool = [];

    /**
     * Get the data directory for a named file-store connection.
     *
     * @param  string $name Connection name from config 'connections' array.
     * @return string The data directory path.
     * @throws RuntimeException If the connection is not configured or not a file driver.
     */
    public static function dataDir(string $name = 'default'): string
    {
        $all = Config::forKey('connections') ?? [];
        $cfg = $all[$name] ?? null;

        if ($cfg === null) {
            throw new RuntimeException("DB connection '{$name}' is not configured.");
        }

        if (($cfg['driver'] ?? '') !== 'file') {
            throw new RuntimeException("DB connection '{$name}' is not a file-store.");
        }

        return $cfg['path'] ?? 'data';
    }

    /**
     * Get a PDO instance for a named connection.
     *
     * @param  string $name Connection name from config 'connections' array.
     * @return PDO
     * @throws RuntimeException If the connection is not configured.
     */
    public static function connection(string $name = 'default'): PDO
    {
        if (isset(self::$pool[$name])) {
            return self::$pool[$name];
        }

        $all = Config::forKey('connections') ?? [];
        $cfg = $all[$name] ?? null;

        if ($cfg === null) {
            throw new RuntimeException("DB connection '{$name}' is not configured.");
        }

        $driver = $cfg['driver'] ?? 'mysql';
        $host   = $cfg['host']   ?? '127.0.0.1';
        $port   = $cfg['port']   ?? ($driver === 'pgsql' ? 5432 : 3306);
        $dbname = $cfg['dbname'] ?? '';

        $dsn = match ($driver) {
            'pgsql' => "pgsql:host={$host};port={$port};dbname={$dbname}",
            default => "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        };

        self::$pool[$name] = new PDO($dsn, $cfg['user'] ?? '', $cfg['password'] ?? '', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return self::$pool[$name];
    }

    /** Close all cached connections. */
    public static function reset(): void
    {
        self::$pool = [];
    }
}


// ── Models ──────────────────────────────────────────────────────────────────

/**
 * Base Model — Abstract superclass for all models.
 *
 * Subclasses must implement the storage backend methods.
 * See JSONModel, MySQLModel, and PGModel for concrete implementations.
 */
abstract class Model extends M2Object
{
    abstract public function save(): void;
    abstract public static function find(string $identifier): ?static;
    abstract public static function delete(string $identifier): void;
}

/**
 * JSONModel — Model backed by the built-in JSONStore.
 *
 * Uses named connections from config/config.php 'connections' key.
 * Override connectionName() to use a specific named connection:
 *
 *   class AuditLog extends JSONModel {
 *       protected static function connectionName(): string { return 'logs'; }
 *       protected static function storeName(): string { return 'audit'; }
 *       // ...
 *   }
 */
abstract class JSONModel extends Model
{
    abstract protected static function storeName(): string;
    abstract protected function identifier(): string;
    abstract protected function toArray(): array;
    abstract protected static function fromArray(array $data): static;

    /** Override to use a different named connection. */
    protected static function connectionName(): string { return 'default'; }

    /** Resolve the data directory from the named connection. */
    protected static function dataDir(): string
    {
        return DB::dataDir(static::connectionName());
    }

    public function save(): void
    {
        JSONStore::put(static::storeName(), $this->identifier(), $this->toArray(), static::dataDir());
    }

    public static function find(string $identifier): ?static
    {
        $data = JSONStore::fetch(static::storeName(), $identifier, static::dataDir());
        if (!$data) {
            return null;
        }
        return static::fromArray($data);
    }

    public static function delete(string $identifier): void
    {
        JSONStore::put(static::storeName(), $identifier, null, static::dataDir());
    }
}

/**
 * MySQLModel — Model backed by a MySQL/MariaDB connection via PDO.
 *
 * Uses named connections from config/config.php 'connections' key.
 * Override connectionName() to use a specific named connection:
 *
 *   class Order extends MySQLModel {
 *       protected static function connectionName(): string { return 'orders_db'; }
 *       protected static function table(): string { return 'orders'; }
 *       // ...
 *   }
 */
abstract class MySQLModel extends Model
{
    abstract protected static function table(): string;
    abstract protected static function primaryKey(): string;
    abstract protected function toRow(): array;
    abstract protected static function fromRow(array $row): static;

    /** Override to use a different named connection. */
    protected static function connectionName(): string { return 'default'; }

    protected static function connection(): PDO
    {
        return DB::connection(static::connectionName());
    }

    public function save(): void
    {
        $row     = $this->toRow();
        $columns = array_keys($row);
        $placeholders = array_map(fn($c) => ":$c", $columns);
        $updates = array_map(fn($c) => "$c = VALUES($c)", $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
            static::table(),
            implode(', ', $columns),
            implode(', ', $placeholders),
            implode(', ', $updates)
        );

        $stmt = static::connection()->prepare($sql);
        $stmt->execute($row);
    }

    public static function find(string $identifier): ?static
    {
        $sql  = sprintf('SELECT * FROM %s WHERE %s = :id LIMIT 1', static::table(), static::primaryKey());
        $stmt = static::connection()->prepare($sql);
        $stmt->execute(['id' => $identifier]);
        $row = $stmt->fetch();
        return $row ? static::fromRow($row) : null;
    }

    public static function delete(string $identifier): void
    {
        $sql  = sprintf('DELETE FROM %s WHERE %s = :id', static::table(), static::primaryKey());
        $stmt = static::connection()->prepare($sql);
        $stmt->execute(['id' => $identifier]);
    }
}

/**
 * PGModel — Model backed by a PostgreSQL connection via PDO.
 *
 * Uses named connections from config/config.php 'connections' key.
 * Override connectionName() to use a specific named connection:
 *
 *   class Report extends PGModel {
 *       protected static function connectionName(): string { return 'analytics'; }
 *       protected static function table(): string { return 'reports'; }
 *       // ...
 *   }
 */
abstract class PGModel extends Model
{
    abstract protected static function table(): string;
    abstract protected static function primaryKey(): string;
    abstract protected function toRow(): array;
    abstract protected static function fromRow(array $row): static;

    /** Override to use a different named connection. */
    protected static function connectionName(): string { return 'default'; }

    protected static function connection(): PDO
    {
        return DB::connection(static::connectionName());
    }

    public function save(): void
    {
        $row     = $this->toRow();
        $columns = array_keys($row);
        $placeholders = array_map(fn($c) => ":$c", $columns);
        $updates = array_map(fn($c) => "$c = EXCLUDED.$c", $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s) ON CONFLICT (%s) DO UPDATE SET %s',
            static::table(),
            implode(', ', $columns),
            implode(', ', $placeholders),
            static::primaryKey(),
            implode(', ', $updates)
        );

        $stmt = static::connection()->prepare($sql);
        $stmt->execute($row);
    }

    public static function find(string $identifier): ?static
    {
        $sql  = sprintf('SELECT * FROM %s WHERE %s = :id LIMIT 1', static::table(), static::primaryKey());
        $stmt = static::connection()->prepare($sql);
        $stmt->execute(['id' => $identifier]);
        $row = $stmt->fetch();
        return $row ? static::fromRow($row) : null;
    }

    public static function delete(string $identifier): void
    {
        $sql  = sprintf('DELETE FROM %s WHERE %s = :id', static::table(), static::primaryKey());
        $stmt = static::connection()->prepare($sql);
        $stmt->execute(['id' => $identifier]);
    }
}