<?php

namespace App\Policies;

use App\Models\Learner;
use App\Models\User;

class LearnerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN);
    }

    public function update(User $user, Learner $learner): bool
    {
        return $user->hasRole(User::ROLE_ADMIN);
    }

    public function delete(User $user, Learner $learner): bool
    {
        return $user->hasRole(User::ROLE_ADMIN);
    }
}
