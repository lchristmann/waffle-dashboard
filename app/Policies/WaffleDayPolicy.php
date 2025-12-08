<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WaffleDay;

class WaffleDayPolicy
{
    public function viewAny(User $user): bool
    {
        // Only admins can see the waffle days list
        return $user->isAdmin();
    }

    public function view(User $user, WaffleDay $waffleDay): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, WaffleDay $waffleDay): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, WaffleDay $waffleDay): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
