<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class YearendAssessmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'teamObjectives' => 'array',
            'teamObjectives.*.id' => 'required|integer|exists:objectives,id',
            'teamObjectives.*.target_achieved' => 'required|numeric|min:0|max:1',
            'teamObjectives.*.final_score' => 'required|numeric|min:0|max:100',
            'individualObjectives' => 'array',
            'individualObjectives.*.id' => 'required|integer|exists:objectives,id',
            'individualObjectives.*.target_achieved' => 'required|numeric|min:0|max:1',
            'individualObjectives.*.final_score' => 'required|numeric|min:0|max:100',
        ];
    }
}
