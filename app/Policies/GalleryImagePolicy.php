<?php

namespace App\Policies;

use App\Models\GalleryImage;
use App\Models\User;

class GalleryImagePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, GalleryImage $galleryImage): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, GalleryImage $galleryImage): bool
    {
        return $user->isAdmin() || $galleryImage->user_id === $user->id;
    }

    public function delete(User $user, GalleryImage $galleryImage): bool
    {
        return $user->isAdmin() || $galleryImage->user_id === $user->id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
