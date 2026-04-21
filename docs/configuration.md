# Configuration

All configuration lives in `config/config.php`.

## Keys

| Key | Purpose | Example |
|---|---|---|
| `_routes` | URL slug → controller/method mapping | `['dash' => 'admin/dashboard']` |
| `_database` | Storage backend config | `['type' => 'file']` |

## Custom keys

Add your own keys and access them anywhere:

```php
// config/config.php
$_config = [
    '_routes'   => [ ... ],
    'app_name'  => 'My App',
    'api_limit' => 100,
];

// In a controller
$name = Config::forKey('app_name');
```
