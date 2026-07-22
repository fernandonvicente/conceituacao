<?php

namespace App\Http\Controllers;

use App\DTO\Users\CreateUserDTO;
use App\DTO\Users\UpdateUserDTO;
use App\Http\Requests\StoreUpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $service,
    ) {}

    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 10), 50);
        $users = $this->service->paginate($perPage);

        return UserResource::collection($users);
    }

    public function store(StoreUpdateUserRequest $request)
    {
        $user = $this->service->create(
            CreateUserDTO::makeFromRequest($request)
        );

        return response()->json([
            'message' => 'Usuário cadastrado com sucesso.',
            'data' => new UserResource($user),
        ], Response::HTTP_CREATED);
    }

    public function show(int $user)
    {
        $user = $this->service->findById($user);

        if (! $user) {
            return response()->json([
                'message' => 'Usuário não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    public function update(StoreUpdateUserRequest $request, int $user)
    {
        $user = $this->service->update(
            UpdateUserDTO::makeFromRequest($request, $user)
        );

        if (! $user) {
            return response()->json([
                'message' => 'Usuário não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Usuário atualizado com sucesso.',
            'data' => new UserResource($user),
        ]);
    }

    public function destroy(int $user)
    {
        if (! $this->service->delete($user)) {
            return response()->json([
                'message' => 'Usuário não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Usuário excluído com sucesso.',
        ]);
    }
}
