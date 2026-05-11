<?php

namespace App\DTO\User;

use App\Enums\DepartmentEnum;

class RegisterUserDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $role,
        public readonly string $department,

    ) {}

    /**
     * Convert validated request array into DTO
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            role: $data['role'],
            department: $data['department'] ?? null,
        );
    }
}
