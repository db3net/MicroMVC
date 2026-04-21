# Controllers & Views

## Controllers

Create a file in `controllers/` matching the class name. Controllers are auto-loaded — no registration needed.

```php
// controllers/users.php
class users extends Controller
{
    public function index(): void
    {
        $this->display('users/list', [
            'users' => ['Alice', 'Bob', 'Charlie'],
        ]);
    }

    public function show(string $id = ''): void
    {
        $this->json_output(['user_id' => $id]);
    }
}
```

### Available methods

| Method | What it does |
|---|---|
| `$this->display('view', $data)` | Render a PHP view template |
| `$this->display('view', $data, true)` | Render and return as string |
| `$this->json_output($data)` | Echo JSON response |
| `$this->json_output($data, true)` | Return JSON as string |

## Views

Views are plain PHP files in `views/`. The `$data` array from the controller is extracted into local variables:

```php
<!-- views/users/list.php -->
<h1>Users</h1>
<ul>
    <?php foreach ($users as $user): ?>
        <li><?= htmlspecialchars($user) ?></li>
    <?php endforeach; ?>
</ul>
```

Subdirectories work — `$this->display('users/list', $data)` loads `views/users/list.php`.
