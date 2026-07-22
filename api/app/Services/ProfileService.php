<?php

namespace App\Services;

use App\DTO\Profiles\CreateProfileDTO;
use App\DTO\Profiles\UpdateProfileDTO;
use App\Models\Profile;
use App\Repositories\ProfileRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use RuntimeException;

class ProfileService
{
    public function __construct(
        private readonly ProfileRepositoryInterface $repository,
    ) {}

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function findById(int $id): ?Profile
    {
        return $this->repository->findById($id);
    }

    public function create(CreateProfileDTO $dto): Profile
    {
        return $this->repository->create($dto);
    }

    public function update(UpdateProfileDTO $dto): ?Profile
    {
        $profile = $this->repository->findById($dto->id);

        if (! $profile) {
            return null;
        }

        // evita renomear o perfil base do sistema
        if ($profile->name === 'Administrador' && $dto->name !== 'Administrador') {
            throw new RuntimeException('O perfil Administrador não pode ser renomeado.');
        }

        return $this->repository->update($dto);
    }

    public function delete(int $id): bool
    {
        $profile = $this->repository->findById($id);

        if (! $profile) {
            return false;
        }

        if ($profile->name === 'Administrador') {
            throw new RuntimeException('O perfil Administrador não pode ser excluído.');
        }

        return $this->repository->delete($id);
    }
}
