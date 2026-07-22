<?php

namespace App\DTO\Profiles;

use App\Http\Requests\StoreUpdateProfileRequest;

class CreateProfileDTO
{
    public function __construct(
        public readonly string $name,
    ) {}

    public static function makeFromRequest(StoreUpdateProfileRequest $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
        );
    }
}
