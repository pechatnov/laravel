<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\MapsReviewableType;
use App\Rules\MbLengthBetween;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreReviewRequest extends FormRequest
{
    use MapsReviewableType;

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
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'reviewable_type' => ['required', 'string', Rule::in(['user', 'company'])],
            'reviewable_id' => ['required', 'uuid'],
            'content' => ['required', 'string', new MbLengthBetween(150, 550)],
            'rating' => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->withReviewableExists($validator);
        });
    }

    protected function passedValidation(): void
    {
        $type = $this->input('reviewable_type');
        if (! is_string($type)) {
            return;
        }

        $this->merge([
            'reviewable_type' => $this->reviewableClass($type),
        ]);
    }
}
