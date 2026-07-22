<?php

namespace App\DTO\Users;

use App\Http\Requests\StoreUpdateUserRequest;

class UpdateUserDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $password,
    ) {}

    public static function makeFromRequest(StoreUpdateUserRequest $request, int $id): self
    {
        return new self(
            id: $id,
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            password: $request->filled('password')
                ? $request->string('password')->toString()
                : null,
        );
    }
}
