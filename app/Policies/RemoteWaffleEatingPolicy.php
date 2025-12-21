<?php

namespace App\Policies;

use App\Models\RemoteWaffleEating;
use App\Models\User;

class RemoteWaffleEatingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RemoteWaffleEating $waffleEating): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, RemoteWaffleEating $waffleEating): bool
    {
        return $user->isAdmin() ||
            ($waffleEating->user_id === $user->id && !$waffleEating->isApproved());
    }

    public function delete(User $user, RemoteWaffleEating $waffleEating): bool
    {
        return $user->isAdmin() ||
            $waffleEating->user_id === $user->id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
