<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AllowedWeightage implements Rule
{
    protected $allowed;

    public function __construct()
    {
        $this->allowed = config('appraisal.allowed_weightages', [10, 15, 20, 25]);
    }

    public function passes($attribute, $value)
    {
        return in_array((int)$value, $this->allowed, true);
    }

    public function message()
    {
        return 'Each objective weightage must be one of: ' . implode(', ', $this->allowed) . ' %.';
    }
}
