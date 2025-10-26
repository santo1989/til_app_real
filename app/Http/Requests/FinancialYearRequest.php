<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinancialYearRequest extends FormRequest
{
    public function authorize()
    {
        // Only allow HR admins or super admin to manage financial years
        return auth()->check() && in_array(auth()->user()->role, ['hr_admin', 'super_admin']);
    }

    public function rules()
    {
        $fyId = $this->route('financialYear') ? $this->route('financialYear')->id : null;
        $labelRule = Rule::unique('financial_years', 'label');
        if ($fyId) {
            $labelRule = $labelRule->ignore($fyId);
        }

        return [
            'label' => ['required', 'string', 'max:20', $labelRule],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
