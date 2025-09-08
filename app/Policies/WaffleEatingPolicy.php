<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WaffleEating;

class WaffleEatingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, WaffleEating $waffleEating): bool
    {
        return $user->isAdmin() ||
            $waffleEating->user_id === $user->id ||
            $waffleEating->entered_by_user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, WaffleEating $waffleEating): bool
    {
        return $user->isAdmin() ||
            $waffleEating->user_id === $user->id ||
            $waffleEating->entered_by_user_id === $user->id;
    }

    public function delete(User $user, WaffleEating $waffleEating): bool
    {
        return $user->isAdmin() ||
            $waffleEating->user_id === $user->id ||
            $waffleEating->entered_by_user_id === $user->id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}

