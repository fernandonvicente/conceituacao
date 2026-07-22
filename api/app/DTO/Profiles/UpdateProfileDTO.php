<?php

namespace App\DTO\Profiles;

use App\Http\Requests\StoreUpdateProfileRequest;

class UpdateProfileDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {}

    public static function makeFromRequest(StoreUpdateProfileRequest $request, int $id): self
    {
        return new self(
            id: $id,
            name: $request->string('name')->toString(),
        );
    }
}
