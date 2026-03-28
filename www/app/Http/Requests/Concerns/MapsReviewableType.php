<?php

namespace App\Http\Requests\Concerns;

use App\Models\Company;
use App\Models\User;
use Illuminate\Validation\Validator;

trait MapsReviewableType
{
    protected function reviewableClass(string $type): string
    {
        return match ($type) {
            'user' => User::class,
            'company' => Company::class,
            default => $type,
        };
    }

    protected function withReviewableExists(Validator $validator): void
    {
        $data = $validator->getData();
        $type = $data['reviewable_type'] ?? null;
        $id = $data['reviewable_id'] ?? null;

        if (! is_string($type) || ! is_string($id)) {
            return;
        }

        $class = match ($type) {
            'user' => User::class,
            'company' => Company::class,
            default => null,
        };

        if ($class === null || ! class_exists($class)) {
            return;
        }

        if (! $class::query()->whereKey($id)->exists()) {
            $validator->errors()->add('reviewable_id', __('The selected reviewable id is invalid.'));
        }
    }
}
