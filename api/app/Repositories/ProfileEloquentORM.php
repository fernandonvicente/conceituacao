<?php

namespace App\Repositories;

use App\DTO\Profiles\CreateProfileDTO;
use App\DTO\Profiles\UpdateProfileDTO;
use App\Models\Profile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProfileEloquentORM implements ProfileRepositoryInterface
{
    public function __construct(
        private readonly Profile $model,
    ) {}

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->newQuery()
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Profile
    {
        return $this->model->find($id);
    }

    public function create(CreateProfileDTO $dto): Profile
    {
        return $this->model->create([
            'name' => $dto->name,
        ]);
    }

    public function update(UpdateProfileDTO $dto): ?Profile
    {
        $profile = $this->findById($dto->id);

        if (! $profile) {
            return null;
        }

        $profile->update([
            'name' => $dto->name,
        ]);

        return $profile->refresh();
    }

    public function delete(int $id): bool
    {
        $profile = $this->findById($id);

        return $profile ? (bool) $profile->delete() : false;
    }
}
