<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Objective;
use App\Models\FinancialYear;
use App\Models\Appraisal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

// Refresh database
Artisan::call('migrate:fresh', ['--seed' => true]);

// Create FY and users
$fy = FinancialYear::create([
    'label' => 'FY2025',
    'start_date' => now()->subMonths(6)->toDateString(),
    'end_date' => now()->addMonths(6)->toDateString(),
    'is_active' => true,
]);

$manager = User::factory()->create(['role' => 'line_manager']);
$superAdmin = User::factory()->create(['role' => 'super_admin']);
$employee = User::factory()->create(['role' => 'employee', 'line_manager_id' => $manager->id]);

$o1 = Objective::create([
    'user_id' => $employee->id,
    'type' => 'individual',
    'description' => 'Obj 1',
    'weightage' => 50,
    'financial_year' => $fy->label,
    'created_by' => $manager->id
]);

$o2 = Objective::create([
    'user_id' => $employee->id,
    'type' => 'individual',
    'description' => 'Obj 2',
    'weightage' => 50,
    'financial_year' => $fy->label,
    'created_by' => $manager->id
]);

// Simulate the POST request
$controller = new \App\Http\Controllers\Appraisal\AppraisalController();

// Manually authenticate
auth()->login($superAdmin);

$request = new \Illuminate\Http\Request();
$request->replace([
    'achievements' => [
        ['id' => $o1->id, 'score' => 50, 'rating' => 0],
        ['id' => $o2->id, 'score' => 50, 'rating' => 0],
    ],
    'supervisor_comments' => 'Test'
]);

try {
    $response = $controller->conductYearEndSubmit($request, $employee->id);
    echo "Response: " . get_class($response) . "\n";
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        $session = $response->getSession();
        if ($session && $session->has('errors')) {
            $errors = $session->get('errors');
            echo "Validation errors: \n";
            foreach ($errors->all() as $error) {
                echo "  - $error\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Check if appraisal was created
$appraisal = Appraisal::where('user_id', $employee->id)->first();
if ($appraisal) {
    echo "Appraisal created: ID={$appraisal->id}, rating={$appraisal->rating}\n";
} else {
    echo "No appraisal created\n";
}
