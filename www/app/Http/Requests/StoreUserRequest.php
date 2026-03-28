<?php

namespace App\Http\Requests;

use App\Rules\MbLengthBetween;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
        return [
            'first_name' => ['required', 'string', new MbLengthBetween(4, 39)],
            'last_name' => ['required', 'string', new MbLengthBetween(4, 39)],
            'phone' => ['required', 'string', 'regex:/^\+7[0-9]{10}$/', 'unique:users,phone'],
            'avatar' => ['required', 'file', 'mimes:jpeg,png', 'max:2048'],
        ];
    }
}
