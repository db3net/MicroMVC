# Routing

## Implicit routing

By default, URLs map directly to controllers — no config needed:

```
/users/show/42  →  users::show('42')
/hello          →  hello::index()
```

## Route aliases

Define aliases in `config/config.php` to map short slugs to controller/method pairs:

```php
$_config = [
    '_routes' => [
        '__default' => 'welcome',           // / → welcome::index()
        '__404'     => 'notfound/index',     // unmatched routes
        'dash'      => 'admin/dashboard',    // /dash → admin::dashboard()
    ],
];
```

## Special routes

| Key | Purpose |
|---|---|
| `__default` | Controller to load when visiting `/` |
| `__404` | Controller/method for unmatched routes |

## Arguments

Everything after `controller/method` is passed as arguments:

```
/users/show/42/details  →  users::show('42', 'details')
```
