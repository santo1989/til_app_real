<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\AllowedWeightage;
use App\Rules\IsSmart;
use App\Models\FinancialYear;
use Carbon\Carbon;

class ObjectiveSettingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Support both bulk submission (objectives array) and single-object forms.
        if ($this->has('objectives')) {
            return [
                'objectives' => 'required|array|min:3|max:9',
                'objectives.*.type' => 'required|string|in:departmental,individual',
                'objectives.*.description' => 'required|string',
                'objectives.*.weightage' => ['required', 'integer', new AllowedWeightage()],
                'objectives.*.target' => 'required|string',
                'date_of_setting' => 'nullable|date',
                // optional IDP payload that may accompany objective submissions
                'idp' => 'nullable|array',
                'idp.description' => 'nullable|string',
                'idp.review_date' => 'nullable|date',
                'idp.status' => 'nullable|string',
            ];
        }

        // Single objective submission rules
        return [
            'type' => 'required|string|in:departmental,individual',
            'description' => 'required|string',
            'weightage' => ['required', 'integer', new AllowedWeightage()],
            'target' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'financial_year' => 'required|string',
            'is_smart' => 'nullable|boolean',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            // Only apply aggregate checks for bulk submissions
            if ($this->has('objectives')) {
                $objectives = $this->input('objectives', []);
                $total = array_sum(array_column($objectives, 'weightage'));
                if ($total !== 100) {
                    $v->errors()->add('objectives', 'Total weightage of all objectives must equal 100%.');
                    return;
                }

                $deptTotal = 0;
                $indCount = 0;
                $deptCount = 0;
                foreach ($objectives as $o) {
                    if (($o['type'] ?? '') === 'departmental') {
                        $deptTotal += (int)($o['weightage'] ?? 0);
                        $deptCount++;
                    } else {
                        $indCount++;
                    }
                }

                $configuredDeptTotal = config('appraisal.departmental_total', 30);
                if ($deptCount > 0 && $deptTotal !== $configuredDeptTotal) {
                    $v->errors()->add('objectives', "Total departmental (team) objectives must total {$configuredDeptTotal}% of overall weightage.");
                }

                // Enforce departmental objectives count: 2-3 when departmental objectives are present
                if ($deptCount > 0) {
                    $deptMin = config('appraisal.departmental_min_count', 2);
                    $deptMax = config('appraisal.departmental_max_count', 3);
                    if ($deptCount < $deptMin || $deptCount > $deptMax) {
                        $v->errors()->add('objectives', "Departmental/Team objectives must be between {$deptMin} and {$deptMax} items.");
                    }
                }

                $indMin = config('appraisal.individual_min', 3);
                $indMax = config('appraisal.individual_max', 6);
                if ($indCount < $indMin || $indCount > $indMax) {
                    $v->errors()->add('objectives', "Individual objectives must be between {$indMin} and {$indMax}.");
                }

                // Validate date_of_setting is within the first month of the financial year when provided
                $dos = $this->input('date_of_setting');
                if ($dos) {
                    $fyLabel = $this->input('financial_year');
                    $fy = null;
                    if ($fyLabel) {
                        $fy = FinancialYear::where('label', $fyLabel)->first();
                    }

                    if (!$fy) {
                        // fallback to active FY
                        $fy = FinancialYear::active();
                    }

                    if ($fy && $fy->start_date) {
                        $start = Carbon::parse($fy->start_date)->startOfDay();
                        $firstMonthEnd = $start->copy()->addMonth()->endOfDay();
                        $given = Carbon::parse($dos);
                        if (!$given->between($start, $firstMonthEnd)) {
                            $v->errors()->add('date_of_setting', 'Date of setting must fall within the first month of the selected financial year.');
                        }

                        // optional extra rule: if setting against the active FY, ensure date is not in the future
                        if (($fyLabel && $fyLabel === FinancialYear::getActiveName()) && $given->greaterThan(now())) {
                            $v->errors()->add('date_of_setting', 'Date of setting for the active financial year cannot be in the future.');
                        }
                    }
                }

                // Ensure each objective has a non-null financial_year. If missing, attempt to populate
                // from the active FinancialYear. If there's no active FY and any objective is missing the
                // financial_year, fail validation.
                $activeFyLabel = FinancialYear::getActiveName();
                $missingFy = false;
                // Work on a copy of the request data so we can set nested values properly
                $all = $this->all();
                foreach ($objectives as $idx => $o) {
                    if (empty($o['financial_year'])) {
                        if ($activeFyLabel) {
                            if (!isset($all['objectives'][$idx])) {
                                $all['objectives'][$idx] = [];
                            }
                            $all['objectives'][$idx]['financial_year'] = $activeFyLabel;
                        } else {
                            $missingFy = true;
                        }
                    }
                }

                // If we injected values, replace the request input so controllers will receive them
                if (!$missingFy && isset($all['objectives'])) {
                    $this->replace($all);
                }

                if ($missingFy) {
                    $v->errors()->add('objectives', 'No active financial year found. Please specify a financial_year for each objective.');
                }

                // SMART validation: either globally enabled via config or opt-in per-object via is_smart
                $enforceAll = config('appraisal.enforce_is_smart', false);
                foreach ($this->input('objectives', []) as $idx => $o) {
                    $shouldCheck = $enforceAll || (!empty($o['is_smart']));
                    if ($shouldCheck) {
                        $rule = new IsSmart();
                        $value = $o['target'] ?? null;
                        if (!$rule->passes("objectives.{$idx}.target", $value)) {
                            $v->errors()->add("objectives.{$idx}.target", $rule->message());
                        }
                    }
                }
            }
        });
    }

    /**
     * Prepare the data for validation. If an active financial year exists, inject its label
     * into any objective entries that are missing the `financial_year` key. This ensures
     * subsequent validation rules operate against a complete payload.
     */
    protected function prepareForValidation()
    {
        if (!$this->has('objectives')) {
            return;
        }

        $activeFyLabel = FinancialYear::getActiveName();
        if (!$activeFyLabel) {
            // Nothing to inject
            return;
        }

        $all = $this->all();
        foreach ($all['objectives'] as $idx => $obj) {
            if (empty($obj['financial_year'])) {
                $all['objectives'][$idx]['financial_year'] = $activeFyLabel;
            }
        }

        $this->replace($all);
    }
}
