<?php

namespace App\Repositories;

use App\DTO\Profiles\CreateProfileDTO;
use App\DTO\Profiles\UpdateProfileDTO;
use App\Models\Profile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProfileRepositoryInterface
{
    public function paginate(int $perPage = 10): LengthAwarePaginator;

    public function findById(int $id): ?Profile;

    public function create(CreateProfileDTO $dto): Profile;

    public function update(UpdateProfileDTO $dto): ?Profile;

    public function delete(int $id): bool;
}
