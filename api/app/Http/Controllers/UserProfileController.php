<?php

namespace App\Http\Controllers;

use App\Http\Requests\SyncProfilesRequest;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\UserResource;
use App\Services\UserProfileService;
use Illuminate\Http\Response;

class UserProfileController extends Controller
{
    public function __construct(
        private readonly UserProfileService $service,
    ) {}

    public function index(int $user)
    {
        $user = $this->service->findUser($user);

        if (! $user) {
            return response()->json([
                'message' => 'Usuário não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        return ProfileResource::collection($user->profiles);
    }

    public function sync(SyncProfilesRequest $request, int $user)
    {
        $result = $this->service->sync(
            $user,
            $request->validated('profile_ids'),
        );

        if (! $result) {
            return response()->json([
                'message' => 'Usuário não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => $result->queuedDetaches > 0
                ? 'Perfis associados. Desassociações enviadas para processamento.'
                : 'Perfis atualizados com sucesso.',
            'queued_detaches' => $result->queuedDetaches,
            'data' => new UserResource($result->user),
        ], $result->queuedDetaches > 0
            ? Response::HTTP_ACCEPTED
            : Response::HTTP_OK);
    }
}
