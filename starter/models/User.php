<?php

/**
 * User — Example model using JSONStore for persistence.
 *
 * Swap `extends JSONModel` for `MySQLModel` or `PGModel` to switch backends.
 */
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
