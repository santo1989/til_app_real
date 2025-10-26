<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Start impersonation: only super_admin may start
    public function start(Request $request, User $user)
    {
        $current = $request->user();
        if ($current->role !== 'super_admin') {
            abort(403);
        }

        // Prevent impersonating another super admin
        if ($user->role === 'super_admin') {
            return redirect()->back()->withErrors(['impersonate' => 'Cannot impersonate another Super Admin.']);
        }

        // Store the original user id so we can restore later
        $request->session()->put('impersonator_id', $current->id);
        $request->session()->put('impersonated_id', $user->id);

        // Log in as the target user
        Auth::loginUsingId($user->id);

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
        } else {
            // If original user missing, logout
            Auth::logout();
            return redirect()->route('login');
        }

        return redirect()->route('dashboard')->with('success', 'Impersonation ended.');
    }
}
