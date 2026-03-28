<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\MapsReviewableType;
use App\Rules\MbLengthBetween;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateReviewRequest extends FormRequest
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
            'user_id' => ['sometimes', 'uuid', 'exists:users,id'],
            'reviewable_type' => ['sometimes', 'string', Rule::in(['user', 'company'])],
            'reviewable_id' => ['sometimes', 'uuid'],
            'content' => ['sometimes', 'string', new MbLengthBetween(150, 550)],
            'rating' => ['sometimes', 'integer', 'min:1', 'max:10'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('reviewable_id') xor $this->filled('reviewable_type')) {
                if (! $this->filled('reviewable_type')) {
                    $validator->errors()->add('reviewable_type', __('The reviewable type field is required when reviewable id is present.'));
                }
                if (! $this->filled('reviewable_id')) {
                    $validator->errors()->add('reviewable_id', __('The reviewable id field is required when reviewable type is present.'));
                }
            }

            if ($this->filled('reviewable_type') && $this->filled('reviewable_id')) {
                $this->withReviewableExists($validator);
            }
        });
    }

    protected function passedValidation(): void
    {
        if ($this->filled('reviewable_type')) {
            $type = $this->input('reviewable_type');
            if (is_string($type)) {
                $this->merge([
                    'reviewable_type' => $this->reviewableClass($type),
                ]);
            }
        }
    }
}
