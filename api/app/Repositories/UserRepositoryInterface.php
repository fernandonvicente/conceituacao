<?php

namespace App\Repositories;

use App\DTO\Users\CreateUserDTO;
use App\DTO\Users\UpdateUserDTO;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function paginate(int $perPage = 10): LengthAwarePaginator;

    public function findById(int $id): ?User;

    public function create(CreateUserDTO $dto): User;

    public function update(UpdateUserDTO $dto): ?User;

    public function delete(int $id): bool;
}
