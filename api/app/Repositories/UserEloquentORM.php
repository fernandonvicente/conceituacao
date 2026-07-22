<?php

namespace App\Repositories;

use App\DTO\Users\CreateUserDTO;
use App\DTO\Users\UpdateUserDTO;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserEloquentORM implements UserRepositoryInterface
{
    public function __construct(
        private readonly User $model,
    ) {}

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->newQuery()
            ->with('profiles')
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?User
    {
        return $this->model
            ->with('profiles')
            ->find($id);
    }

    public function create(CreateUserDTO $dto): User
    {
        return $this->model->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->password,
        ]);
    }

    public function update(UpdateUserDTO $dto): ?User
    {
        $user = $this->findById($dto->id);

        if (! $user) {
            return null;
        }

        $data = [
            'name' => $dto->name,
            'email' => $dto->email,
        ];

        if ($dto->password !== null) {
            $data['password'] = $dto->password;
        }

        $user->update($data);

        return $user->refresh();
    }

    public function delete(int $id): bool
    {
        $user = $this->findById($id);

        return $user ? (bool) $user->delete() : false;
    }
}
