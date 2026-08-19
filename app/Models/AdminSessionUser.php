<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;

class AdminSessionUser implements Authenticatable
{
    public function __construct(
        public string $id,
        public string $email,
        public string $name,
        public string $role = 'admin',
        public bool $is_demo = false
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            email: $data['email'] ?? '',
            name: $data['name'] ?? 'Admin',
            role: $data['role'] ?? 'admin',
            is_demo: (bool) ($data['is_demo'] ?? false)
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'role' => $this->role,
            'is_demo' => $this->is_demo,
        ];
    }

    public function isDemo(): bool
    {
        return $this->is_demo;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canPerformDestructiveAction(): bool
    {
        return !$this->is_demo;
    }

    // Authenticatable Interface implementation (Session-backed)
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): string
    {
        return $this->id;
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getAuthPasswordName(): string
    {
        return '';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
        // Session-backed, no-op
    }

    public function getRememberTokenName(): ?string
    {
        return null;
    }
}
