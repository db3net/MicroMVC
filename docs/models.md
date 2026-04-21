# Models

MicroMVC includes a lightweight model layer with three storage backends built in. All models extend a common `Model` base class, so switching backends is a one-line change.

```
Model (abstract)
├── JSONModel  — file-based via JSONStore, zero config
├── MySQLModel — PDO + MySQL/MariaDB
└── PGModel    — PDO + PostgreSQL
```

Models live in the `models/` directory and are autoloaded automatically.

---

## JSONModel (default)

No database required. Data is stored as JSON files in the `data/` directory.

### Example model

```php
<?php
// models/User.php

class User extends JSONModel
{
    public string $name;
    public string $email;

    public function __construct(string $name = '', string $email = '')
    {
        $this->name  = $name;
        $this->email = $email;
    }

    protected static function storeName(): string { return 'User'; }
    protected function identifier(): string { return $this->email; }

    protected function toArray(): array
    {
        return ['name' => $this->name, 'email' => $this->email];
    }

    protected static function fromArray(array $data): static
    {
        return new static($data['name'], $data['email']);
    }
}
?>
```

### Usage in a controller

```php
<?php
class users extends Controller
{
    public function create(): void
    {
        $user = new User('David', 'david@example.com');
        $user->save();
        $this->json_output(['status' => 'created']);
    }

    public function show(string $email): void
    {
        $user = User::find($email);
        if (!$user) {
            $this->json_output(['error' => 'Not found']);
            return;
        }
        $this->json_output(['name' => $user->name, 'email' => $user->email]);
    }
}
?>
```

### Required methods

| Method | Purpose |
|---|---|
| `storeName()` | Name used for the JSON file (`data/User.json`) |
| `identifier()` | Unique key for this record |
| `toArray()` | Serialize the model to an associative array |
| `fromArray(array)` | Hydrate a model from an associative array |

---

## MySQLModel

### Configuration

Add a `mysql` key to `config/config.php`:

```php
$_config = [
    'mysql' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'dbname'   => 'myapp',
        'user'     => 'root',
        'password' => '',
    ],
    // ...existing config...
];
```

### Example model

```php
<?php
// models/User.php

class User extends MySQLModel
{
    public int    $id;
    public string $name;
    public string $email;

    public function __construct(int $id = 0, string $name = '', string $email = '')
    {
        $this->id    = $id;
        $this->name  = $name;
        $this->email = $email;
    }

    protected static function table(): string { return 'users'; }
    protected static function primaryKey(): string { return 'id'; }

    protected function toRow(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'email' => $this->email];
    }

    protected static function fromRow(array $row): static
    {
        return new static((int) $row['id'], $row['name'], $row['email']);
    }
}
?>
```

### Required methods

| Method | Purpose |
|---|---|
| `table()` | Database table name |
| `primaryKey()` | Primary key column name |
| `toRow()` | Serialize to a column => value array |
| `fromRow(array)` | Hydrate from a database row |

`save()` uses `INSERT ... ON DUPLICATE KEY UPDATE`, so it handles both inserts and updates.

---

## PGModel

### Configuration

Add a `pgsql` key to `config/config.php`:

```php
$_config = [
    'pgsql' => [
        'host'     => '127.0.0.1',
        'port'     => 5432,
        'dbname'   => 'myapp',
        'user'     => 'postgres',
        'password' => '',
    ],
    // ...existing config...
];
```

### Example model

Same structure as MySQLModel — just change the extends:

```php
<?php
class User extends PGModel
{
    // ...identical to MySQLModel example above...
}
?>
```

`save()` uses `INSERT ... ON CONFLICT DO UPDATE` (PostgreSQL upsert syntax).

### Required methods

Same as MySQLModel: `table()`, `primaryKey()`, `toRow()`, `fromRow()`.

---

## Switching backends

Changing storage is a one-line edit on your model:

```php
// File-based (default, no setup)
class User extends JSONModel { ... }

// MySQL/MariaDB
class User extends MySQLModel { ... }

// PostgreSQL
class User extends PGModel { ... }
```

The controller code stays exactly the same — `save()`, `find()`, and `delete()` work identically across all three backends.
