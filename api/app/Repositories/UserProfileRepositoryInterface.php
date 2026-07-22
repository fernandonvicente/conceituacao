<?php

namespace App\Repositories;

use App\Models\User;

interface UserProfileRepositoryInterface
{
    public function findUser(int $userId): ?User;

    public function attach(int $userId, array $profileIds): void;

    public function detach(int $userId, int $profileId): void;
}
