<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class MbLengthBetween implements ValidationRule
{
    public function __construct(
        private readonly int $minInclusive,
        private readonly int $maxInclusive,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $length = mb_strlen($value);
        if ($length < $this->minInclusive || $length > $this->maxInclusive) {
            $fail(__('validation.mb_between', [
                'attribute' => $attribute,
                'min' => $this->minInclusive,
                'max' => $this->maxInclusive,
            ]));
        }
    }
}
