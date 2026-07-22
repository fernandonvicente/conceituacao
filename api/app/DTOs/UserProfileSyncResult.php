<?php

namespace App\DTOs;

use App\Models\User;

readonly class UserProfileSyncResult
{
    public function __construct(
        public User $user,
        public int $queuedDetaches,
    ) {}
}
