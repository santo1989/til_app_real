<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class IsSmart implements Rule
{
    /**
     * Determine if the given value is a SMART-style measurable target.
     * We keep the heuristic conservative: require at least one numeric measure
     * (digits) and reject vague verbs-only descriptions.
     *
     * Examples accepted: "90%", "10 days", "Reduce time to hire to 14 days",
     * "Increase coverage by 5%".
     * Examples rejected: "Improve customer satisfaction", "Do better", "Support people".
     */
    public function passes($attribute, $value)
    {
        if (is_null($value) || $value === '') {
            return false;
        }

        $value = (string) $value;

        // If there is at least one numeric digit, consider it measurable.
        if (preg_match('/\d+/', $value) === 0) {
            return false;
        }

        // Accept percentage forms: "90%" or "90 %" or ">= 80%"
        if (preg_match('/\d+\s*%/', $value)) {
            return true;
        }

        // Accept numeric with unit or time: "10 days", "14 days", "5 FTE"
        if (preg_match('/\d+\s*([a-zA-Z]{1,10})/', $value)) {
            return true;
        }

        // Accept comparative numeric statements like "reduce to 10", "increase by 5"
        if (preg_match('/(reduce|increase|decrease|improve|limit|reach|to)\s+\d+/i', $value)) {
            return true;
        }

        // Otherwise, fall back to rejecting vague descriptions even if digits present
        return false;
    }

    public function message()
    {
        return 'The objective target must be measurable (include a numeric value, e.g. "90%", "14 days", "reduce to 10").';
    }
}
