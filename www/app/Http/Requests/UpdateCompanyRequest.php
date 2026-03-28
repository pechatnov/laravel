<?php

namespace App\Http\Requests;

use App\Rules\MbLengthBetween;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
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
            'title' => ['sometimes', 'string', new MbLengthBetween(4, 39)],
            'description' => ['sometimes', 'string', new MbLengthBetween(150, 400)],
            'logo' => ['sometimes', 'file', 'mimes:png', 'max:3072'],
        ];
    }
}
