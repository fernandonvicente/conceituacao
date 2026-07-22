<?php

namespace App\Services;

use App\DTO\Users\CreateUserDTO;
use App\DTO\Users\UpdateUserDTO;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function findById(int $id): ?User
    {
        return $this->repository->findById($id);
    }

    public function create(CreateUserDTO $dto): User
    {
        return $this->repository->create($dto);
    }

    public function update(UpdateUserDTO $dto): ?User
    {
        return $this->repository->update($dto);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
