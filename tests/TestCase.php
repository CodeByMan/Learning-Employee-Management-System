<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    protected function createUserWithRole(string $role, array $attributes = []): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (User::assignableRoles() as $assignableRole) {
            Role::findOrCreate($assignableRole, 'web');
        }

        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }
}
