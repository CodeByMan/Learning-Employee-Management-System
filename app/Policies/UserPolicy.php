<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasRole(User::ROLE_ADMIN);
    }

    public function update(User $actor, User $user): bool
    {
        return $actor->hasRole(User::ROLE_ADMIN);
    }

    public function delete(User $actor, User $user): bool
    {
        return $actor->hasRole(User::ROLE_ADMIN);
    }
}
