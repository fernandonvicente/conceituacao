<?php

namespace App\DTO\Users;

use App\Http\Requests\StoreUpdateUserRequest;

class CreateUserDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    ) {}

    public static function makeFromRequest(StoreUpdateUserRequest $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
        );
    }
}
