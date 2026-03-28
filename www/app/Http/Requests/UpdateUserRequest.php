<?php

namespace App\Http\Requests;

use App\Rules\MbLengthBetween;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'first_name' => ['sometimes', 'string', new MbLengthBetween(4, 39)],
            'last_name' => ['sometimes', 'string', new MbLengthBetween(4, 39)],
            'phone' => ['sometimes', 'string', 'regex:/^\+7[0-9]{10}$/', Rule::unique('users', 'phone')->ignore($userId, 'id')],
            'avatar' => ['sometimes', 'file', 'mimes:jpeg,png', 'max:2048'],
        ];
    }
}
