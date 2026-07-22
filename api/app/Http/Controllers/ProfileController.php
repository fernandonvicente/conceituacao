<?php

namespace App\Http\Controllers;

use App\DTO\Profiles\CreateProfileDTO;
use App\DTO\Profiles\UpdateProfileDTO;
use App\Http\Requests\StoreUpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $service,
    ) {}

    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 10), 50);
        $profiles = $this->service->paginate($perPage);

        return ProfileResource::collection($profiles);
    }

    public function store(StoreUpdateProfileRequest $request)
    {
        $profile = $this->service->create(
            CreateProfileDTO::makeFromRequest($request)
        );

        return response()->json([
            'message' => 'Perfil cadastrado com sucesso.',
            'data' => new ProfileResource($profile),
        ], Response::HTTP_CREATED);
    }

    public function show(int $profile)
    {
        $profile = $this->service->findById($profile);

        if (! $profile) {
            return response()->json([
                'message' => 'Perfil não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => new ProfileResource($profile),
        ]);
    }

    public function update(StoreUpdateProfileRequest $request, int $profile)
    {
        try {
            $profile = $this->service->update(
                UpdateProfileDTO::makeFromRequest($request, $profile)
            );
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! $profile) {
            return response()->json([
                'message' => 'Perfil não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Perfil atualizado com sucesso.',
            'data' => new ProfileResource($profile),
        ]);
    }

    public function destroy(int $profile)
    {
        try {
            $deleted = $this->service->delete($profile);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! $deleted) {
            return response()->json([
                'message' => 'Perfil não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Perfil excluído com sucesso.',
        ]);
    }
}
