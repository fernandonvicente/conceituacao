<?php

namespace App\Services;

use App\DTOs\UserProfileSyncResult;
use App\Messaging\ProfileDetachProducer;
use App\Models\User;
use App\Repositories\UserProfileRepositoryInterface;

class UserProfileService
{
    public function __construct(
        private readonly UserProfileRepositoryInterface $repository,
        private readonly ProfileDetachProducer $producer,
    ) {}

    public function findUser(int $userId): ?User
    {
        return $this->repository->findUser($userId);
    }

    public function sync(int $userId, array $profileIds): ?UserProfileSyncResult
    {
        $user = $this->repository->findUser($userId);

        if (! $user) {
            return null;
        }

        $currentProfileIds = $user->profiles->pluck('id')->all();
        $profileIdsToAttach = array_values(array_diff($profileIds, $currentProfileIds));
        $profileIdsToDetach = array_values(array_diff($currentProfileIds, $profileIds));

        $this->repository->attach($userId, $profileIdsToAttach);

        foreach ($profileIdsToDetach as $profileId) {
            $this->producer->publish($userId, $profileId);
        }

        return new UserProfileSyncResult(
            user: $user->load('profiles'),
            queuedDetaches: count($profileIdsToDetach),
        );
    }
}
