<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

class ImpersonationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Start impersonation: only super_admin may start
    public function start(Request $request, User $user)
    {
        $this->authorize('impersonate', $user);
        $current = $request->user();

        // Store the original user id so we can restore later
        $request->session()->put('impersonator_id', $current->id);
        $request->session()->put('impersonated_id', $user->id);

        // Log in as the target user
        Auth::loginUsingId($user->id);

        AuditLog::create([
            'user_id' => $current->id,
            'action' => 'impersonation_started',
            'table_name' => 'users',
            'record_id' => $user->id,
            'details' => "User {$current->id} started impersonating {$user->id}",
        ]);

        // Add a flash message
        return redirect()->route('dashboard')->with('success', "You are now impersonating {$user->name}");
    }

    // Stop impersonation and restore original user
    public function stop(Request $request)
    {
        $impersonatorId = $request->session()->pull('impersonator_id');
        $impersonatedId = $request->session()->pull('impersonated_id');

        if (!$impersonatorId) {
            return redirect()->route('dashboard');
        }

        // Re-login as the impersonator if still exists
        $impersonator = User::find($impersonatorId);
        if ($impersonator) {
            Auth::loginUsingId($impersonator->id);
            AuditLog::create([
                'user_id' => $impersonator->id,
                'action' => 'impersonation_stopped',
                'table_name' => 'users',
                'record_id' => $impersonatedId,
                'details' => "Impersonation ended; restored to user {$impersonator->id} from {$impersonatedId}",
            ]);
        } else {
            // If original user missing, logout
            Auth::logout();
            return redirect()->route('login');
        }

        return redirect()->route('dashboard')->with('success', 'Impersonation ended.');
    }
}
