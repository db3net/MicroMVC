# JSON File-Store

A simple key/value store backed by JSON files in the `data/` directory. No database setup required.

## Usage

```php
// Store a record
JSONStore::put('users', 'user_42', ['name' => 'David', 'role' => 'admin']);

// Retrieve it
$user = JSONStore::fetch('users', 'user_42');
// → ['name' => 'David', 'role' => 'admin']

// Append to a log file
JSONStore::log('audit', 'login', ['user' => 'user_42', 'ip' => '10.0.0.1']);
```

## Storage format

Data is stored as `data/users.json`, `data/audit.json`, etc. — human-readable JSON, easy to inspect and back up.

Logs are appended to `data/log` as timestamped lines.
