<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\FinancialYear;
use App\Http\Requests\ObjectiveSettingRequest;

class ObjectiveSettingRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_submission_populates_financial_year_from_active()
    {
        // create an active financial year
        $fy = FinancialYear::create([
            'label' => '2025-26',
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'is_active' => true,
        ]);

        $data = [
            'objectives' => [
                ['type' => 'individual', 'description' => 'Obj 1', 'weightage' => 25, 'target' => 'Do A'],
                ['type' => 'individual', 'description' => 'Obj 2', 'weightage' => 25, 'target' => 'Do B'],
                ['type' => 'individual', 'description' => 'Obj 3', 'weightage' => 25, 'target' => 'Do C'],
                ['type' => 'individual', 'description' => 'Obj 4', 'weightage' => 25, 'target' => 'Do D'],
            ],
        ];

        $req = new ObjectiveSettingRequest();
        // populate request data as if submitted
        $req->replace($data);

        $validator = $this->app['validator']->make($req->all(), $req->rules());
        // register after callbacks from the request
        $req->withValidator($validator);

        $this->assertTrue($validator->passes(), 'Validator should pass when active FY exists and will be injected');

        // after passing, the request should have merged the financial_year into objectives
        $this->assertEquals($fy->label, $req->input('objectives.0.financial_year'));
        $this->assertEquals($fy->label, $req->input('objectives.1.financial_year'));
    }

    public function test_bulk_submission_fails_without_active_financial_year()
    {
        // ensure no active FY exists
        FinancialYear::query()->delete();

        $data = [
            'objectives' => [
                ['type' => 'individual', 'description' => 'Obj 1', 'weightage' => 25, 'target' => 'Do A'],
                ['type' => 'individual', 'description' => 'Obj 2', 'weightage' => 25, 'target' => 'Do B'],
                ['type' => 'individual', 'description' => 'Obj 3', 'weightage' => 25, 'target' => 'Do C'],
                ['type' => 'individual', 'description' => 'Obj 4', 'weightage' => 25, 'target' => 'Do D'],
            ],
        ];

        $req = new ObjectiveSettingRequest();
        $req->replace($data);

        $validator = $this->app['validator']->make($req->all(), $req->rules());
        $req->withValidator($validator);

        $this->assertFalse($validator->passes(), 'Validator should fail when no active financial year exists');

        $errors = $validator->errors()->all();
        $this->assertStringContainsString('No active financial year found', implode('; ', $errors));
    }
}
