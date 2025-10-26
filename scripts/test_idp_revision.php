<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Idp;
use App\Models\IdpRevision;
use App\Http\Controllers\Appraisal\IdpController;
use Illuminate\Http\Request as HttpRequest;

echo "Starting IDP revision smoke test...\n";

$user = User::first();
if (!$user) {
    echo "No users found in DB; cannot create IDP. Exiting.\n";
    exit(1);
}

// Create an IDP using only defined columns in migration
$idp = Idp::create([
    'user_id' => $user->id,
    'description' => 'Smoke test IDP description',
    'review_date' => date('Y-m-d'),
    'progress_till_dec' => 'none',
    'revised_description' => null,
    'accomplishment' => null,
]);

echo "Created Idp id={$idp->id}\n";

// Invoke the controller update to exercise the revision-capture logic
$controller = new IdpController();
$reqPayload = [
    'user_id' => $user->id,
    'description' => 'Updated description via smoke test',
    'review_date' => date('Y-m-d'),
    'progress_till_dec' => 'progressing',
    'revised_description' => 'Revised desc',
    'accomplishment' => 'Completed initial steps',
    'status' => null,
];
$httpReq = HttpRequest::create('/idps/' . $idp->id, 'POST', $reqPayload);

// Call controller update (route-model binding not used but passing model instance should work)
try {
    $controller->update($httpReq, $idp);
} catch (\Exception $e) {
    echo "Controller update threw exception: " . $e->getMessage() . "\n";
}

// Read back any revisions
$rev = IdpRevision::where('idp_id', $idp->id)->orderByDesc('id')->first();
if ($rev) {
    echo "Revision recorded: id={$rev->id}\n";
    echo json_encode($rev->changes, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "No revision recorded — controller logic may not have created a revision.\n";
}

echo "Done.\n";
