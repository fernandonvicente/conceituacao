<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncProfilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'profile_ids' => ['present', 'array'],
            'profile_ids.*' => ['integer', 'exists:profiles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'profile_ids.present' => 'A lista de perfis é obrigatória.',
            'profile_ids.array' => 'A lista de perfis é inválida.',
            'profile_ids.*.exists' => 'Um dos perfis informados não existe.',
        ];
    }
}
