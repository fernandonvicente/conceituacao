<?php

namespace App\Repositories;

use App\Models\User;

class UserProfileEloquentORM implements UserProfileRepositoryInterface
{
    public function __construct(
        private readonly User $model,
    ) {}

    public function findUser(int $userId): ?User
    {
        return $this->model
            ->with('profiles')
            ->find($userId);
    }

    public function attach(int $userId, array $profileIds): void
    {
        if ($profileIds === []) {
            return;
        }

        $this->model
            ->findOrFail($userId)
            ->profiles()
            ->syncWithoutDetaching($profileIds);
    }

    public function detach(int $userId, int $profileId): void
    {
        $this->model
            ->find($userId)
            ?->profiles()
            ->detach($profileId);
    }
}
