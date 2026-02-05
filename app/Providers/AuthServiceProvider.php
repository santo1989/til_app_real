<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Objective::class => \App\Policies\ObjectivePolicy::class,
        \App\Models\Idp::class => \App\Policies\IdpPolicy::class,
        \App\Models\Appraisal::class => \App\Policies\AppraisalPolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Pip::class => \App\Policies\PipPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Define basic gates used by controllers & views
        Gate::define('view-own-objectives', function ($user, $targetUserId) {
            return $user->id === $targetUserId || in_array($user->role, ['hr_admin', 'super_admin', 'dept_head', 'board']);
        });

        Gate::define('enter-achieved', function ($user) {
            return in_array($user->role, ['line_manager', 'hr_admin', 'super_admin']);
        });

        Gate::define('approve-item', function ($user) {
            return in_array($user->role, ['hr_admin', 'super_admin']);
        });
    }
}
