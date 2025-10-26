<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\AllowedWeightage;
use App\Rules\IsSmart;

class SingleObjectiveRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'user_id' => 'sometimes|required|exists:users,id',
            'department_id' => 'sometimes|nullable|exists:departments,id',
            'type' => 'required|string|in:individual,departmental',
            'description' => 'required|string',
            'weightage' => ['required', 'integer', new AllowedWeightage()],
            'target' => 'required|string',
            'is_smart' => 'nullable|boolean',
            'status' => 'nullable|string',
            'financial_year' => 'required|string',
        ];

        // Apply IsSmart rule when globally enabled or when the request indicates is_smart
        $enforceAll = config('appraisal.enforce_is_smart', false);
        if ($enforceAll || $this->input('is_smart')) {
            $rules['target'] = ['required', 'string', new IsSmart()];
        }

        return $rules;
    }
}
