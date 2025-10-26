<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MidtermRevisionRequest extends FormRequest
{
    public function authorize()
    {
        // authorization handled by controller (line manager)
        return true;
    }

    public function rules()
    {
        return [
            'revisions' => 'required|array|min:1',
            'revisions.*.action' => 'required|string|in:add,update,delete',
            // for add/update
            'revisions.*.id' => 'sometimes|integer|exists:objectives,id',
            'revisions.*.title' => 'sometimes|string|max:255',
            'revisions.*.description' => 'nullable|string',
            // allow flexible weightage values here; we validate totals in the controller
            'revisions.*.weightage' => 'nullable|integer|min:0|max:100',
            'revisions.*.type' => 'nullable|string|in:individual,departmental',
            'revisions.*.department_id' => 'nullable|integer|exists:departments,id',
        ];
    }

    public function messages()
    {
        return [
            'revisions.required' => 'At least one revision action is required.',
        ];
    }
}
