<?php

namespace App\Http\Requests;

use App\Rules\MbLengthBetween;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
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
            'title' => ['required', 'string', new MbLengthBetween(4, 39)],
            'description' => ['required', 'string', new MbLengthBetween(150, 400)],
            'logo' => ['required', 'file', 'mimes:png', 'max:3072'],
        ];
    }
}
