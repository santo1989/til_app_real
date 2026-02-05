<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Pip;
use Illuminate\Auth\Access\HandlesAuthorization;

class PipPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['hr_admin', 'super_admin']);
    }

    public function view(User $user, Pip $pip): bool
    {
        return in_array($user->role, ['hr_admin', 'super_admin']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['hr_admin', 'super_admin']);
    }

    public function update(User $user, Pip $pip): bool
    {
        return in_array($user->role, ['hr_admin', 'super_admin']);
    }

    public function delete(User $user, Pip $pip): bool
    {
        return in_array($user->role, ['hr_admin', 'super_admin']);
    }

    public function close(User $user, Pip $pip): bool
    {
        return in_array($user->role, ['hr_admin', 'super_admin']);
    }
}
