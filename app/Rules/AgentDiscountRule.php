<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AgentDiscountRule implements ValidationRule
{
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {

        $user = auth()->user();

        if (! $user) {
            return;
        }

        $maxDiscount = (float) $user->max_discount_percent;

        if ((float) $value > $maxDiscount) {

            $fail(
                "Maximum allowed discount is {$maxDiscount}%."
            );
        }
    }
}