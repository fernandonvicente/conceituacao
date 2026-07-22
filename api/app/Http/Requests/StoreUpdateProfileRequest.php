<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $profileId = $this->route('profile');

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('profiles', 'name')->ignore($profileId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O perfil é obrigatório.',
            'name.min' => 'O perfil deve ter pelo menos 3 caracteres.',
            'name.unique' => 'Este perfil já está cadastrado.',
        ];
    }
}
