<?php
// ──────────────────────────────────────────────────────────────────────────────
// User — Example model using the JSON file-store
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

//
// This is a sample model to show how MicroMVC models work.
//
// JSONModel stores data as JSON files in the data/ directory — no database
// needed. To switch to MySQL or PostgreSQL, just change the parent class:
//
//   class User extends MySQLModel { ... }
//   class User extends PGModel   { ... }
//
// Then implement table(), primaryKey(), toRow(), and fromRow() instead of
// the JSON-specific methods below. See the docs for details.
//
// Usage from a controller:
//
//   // Create and save
//   $user = new User('Alice', 'alice@example.com');
//   $user->save();                          // writes to data/User.json
//
//   // Find by identifier (email in this case)
//   $user = User::find('alice@example.com');
//
//   // Delete
//   User::delete('alice@example.com');
//

class User extends JSONModel
{
    public string $name;
    public string $email;

    public function __construct(string $name = '', string $email = '')
    {
        $this->name  = $name;
        $this->email = $email;
    }

    // ── Required by JSONModel ───────────────────────────────────────────────

    /** Collection name — becomes the filename: data/User.json */
    protected static function storeName(): string { return 'User'; }

    /** Unique key for each record. Can be an ID, email, slug, etc. */
    protected function identifier(): string { return $this->email; }

    /** Serialize the model to an array for storage. */
    protected function toArray(): array
    {
        return ['name' => $this->name, 'email' => $this->email];
    }

    /** Reconstruct a model instance from a stored array. */
    protected static function fromArray(array $data): static
    {
        return new static($data['name'], $data['email']);
    }
}
