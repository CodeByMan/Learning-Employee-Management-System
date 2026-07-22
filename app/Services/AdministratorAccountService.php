<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AdministratorAccountService
{
    public function updateManagedUser(
        User $user,
        string $name,
        string $email,
        string $role
    ): void {
        DB::transaction(function () use ($email, $name, $role, $user): void {
            $this->lockAdministratorRole();

            $lockedUser = User::query()
                ->lockForUpdate()
                ->findOrFail($user->getKey());

            if ($lockedUser->hasRole(User::ROLE_ADMIN) && $role !== User::ROLE_ADMIN) {
                $this->ensureAnotherAdministratorExists(
                    $lockedUser,
                    'role',
                    'The final administrator cannot be demoted.'
                );
            }

            $emailChanged = $lockedUser->email !== $email;

            $lockedUser->update([
                'name' => $name,
                'email' => $email,
                'email_verified_at' => $emailChanged ? null : $lockedUser->email_verified_at,
            ]);
            $lockedUser->syncRoles([$role]);
        }, 3);
    }

    public function deleteManagedUser(User $user): void
    {
        $this->deleteUser(
            $user,
            'user',
            'The final administrator cannot be deleted.'
        );
    }

    public function deleteOwnProfile(User $user): void
    {
        $this->deleteUser(
            $user,
            'password',
            'The final administrator account cannot be deleted.',
            'userDeletion'
        );
    }

    private function deleteUser(
        User $user,
        string $errorField,
        string $message,
        ?string $errorBag = null
    ): void {
        DB::transaction(function () use ($errorBag, $errorField, $message, $user): void {
            $this->lockAdministratorRole();

            $lockedUser = User::query()
                ->lockForUpdate()
                ->findOrFail($user->getKey());

            if ($lockedUser->hasRole(User::ROLE_ADMIN)) {
                $this->ensureAnotherAdministratorExists(
                    $lockedUser,
                    $errorField,
                    $message,
                    $errorBag
                );
            }

            $lockedUser->delete();
        }, 3);
    }

    /**
     * Lock the one row shared by every administrator mutation.
     *
     * Using the role row as the mutex ensures that two requests cannot both
     * pass the final-administrator recheck before either mutation commits.
     */
    private function lockAdministratorRole(): ?Role
    {
        return Role::query()
            ->where('name', User::ROLE_ADMIN)
            ->where('guard_name', 'web')
            ->lockForUpdate()
            ->first();
    }

    private function ensureAnotherAdministratorExists(
        User $user,
        string $errorField,
        string $message,
        ?string $errorBag = null
    ): void {
        $anotherAdministratorExists = User::role(User::ROLE_ADMIN)
            ->where($user->getQualifiedKeyName(), '!=', $user->getKey())
            ->exists();

        if ($anotherAdministratorExists) {
            return;
        }

        $exception = ValidationException::withMessages([
            $errorField => $message,
        ]);

        if ($errorBag !== null) {
            $exception->errorBag($errorBag);
        }

        throw $exception;
    }
}
