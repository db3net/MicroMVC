<?php
// ──────────────────────────────────────────────────────────────────────────────
// TestPGUser — PostgreSQL-backed model for integration tests
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

class TestPGUser extends PGModel
{
    public string $name;
    public string $email;

    public function __construct(string $name = '', string $email = '')
    {
        $this->name  = $name;
        $this->email = $email;
    }

    protected static function connectionName(): string { return 'pg_test'; }
    protected static function table(): string { return 'users'; }
    protected static function primaryKey(): string { return 'email'; }

    protected function toRow(): array
    {
        return ['name' => $this->name, 'email' => $this->email];
    }

    protected static function fromRow(array $row): static
    {
        return new static($row['name'], $row['email']);
    }
}
