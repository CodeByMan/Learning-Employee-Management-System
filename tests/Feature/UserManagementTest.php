<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_another_user_with_an_allowed_role(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        $managedUser = $this->createUserWithRole(User::ROLE_LEARNER);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $managedUser), [
            'name' => 'Updated Employee',
            'email' => 'UPDATED@example.com',
            'role' => User::ROLE_EMPLOYEE,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('admin.users.index'));
        $managedUser->refresh();
        $this->assertSame('Updated Employee', $managedUser->name);
        $this->assertSame('updated@example.com', $managedUser->email);
        $this->assertNull($managedUser->email_verified_at);
        $this->assertTrue($managedUser->hasRole(User::ROLE_EMPLOYEE));
    }

    public function test_non_admin_cannot_update_a_user_by_id(): void
    {
        $employee = $this->createUserWithRole(User::ROLE_EMPLOYEE);
        $target = $this->createUserWithRole(User::ROLE_LEARNER);

        $this->actingAs($employee)
            ->put(route('admin.users.update', $target), [
                'name' => 'Unauthorized Change',
                'email' => $target->email,
                'role' => User::ROLE_ADMIN,
            ])
            ->assertForbidden();

        $this->assertTrue($target->fresh()->hasRole(User::ROLE_LEARNER));
    }

    public function test_admin_cannot_change_their_own_role(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        $response = $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->put(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => User::ROLE_EMPLOYEE,
            ]);

        $response->assertSessionHasErrors('role');
        $this->assertTrue($admin->fresh()->hasRole(User::ROLE_ADMIN));
    }

    public function test_final_admin_cannot_be_deleted_from_user_management(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        $secondUser = $this->createUserWithRole(User::ROLE_EMPLOYEE);

        $response = $this->actingAs($secondUser)->delete(route('admin.users.destroy', $admin));
        $response->assertForbidden();

        $response = $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $admin));
        $response->assertSessionHasErrors('user');
        $this->assertNotNull($admin->fresh());
    }

    public function test_final_administrator_is_rechecked_after_another_admin_is_demoted(): void
    {
        $firstAdmin = $this->createUserWithRole(User::ROLE_ADMIN);
        $remainingAdmin = $this->createUserWithRole(User::ROLE_ADMIN);

        $this->actingAs($remainingAdmin)
            ->put(route('admin.users.update', $firstAdmin), [
                'name' => $firstAdmin->name,
                'email' => $firstAdmin->email,
                'role' => User::ROLE_EMPLOYEE,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame(1, User::role(User::ROLE_ADMIN)->count());
        $this->assertTrue($firstAdmin->fresh()->hasRole(User::ROLE_EMPLOYEE));

        $this->actingAs($remainingAdmin)
            ->from(route('admin.profile.edit'))
            ->delete(route('admin.profile.destroy'), ['password' => 'Password123'])
            ->assertSessionHasErrorsIn('userDeletion', 'password');

        $this->assertSame(1, User::role(User::ROLE_ADMIN)->count());
        $this->assertNotNull($remainingAdmin->fresh());
    }

    public function test_final_administrator_is_rechecked_after_another_admin_is_deleted(): void
    {
        $deletedAdmin = $this->createUserWithRole(User::ROLE_ADMIN);
        $remainingAdmin = $this->createUserWithRole(User::ROLE_ADMIN);

        $this->actingAs($remainingAdmin)
            ->delete(route('admin.users.destroy', $deletedAdmin))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame(1, User::role(User::ROLE_ADMIN)->count());
        $this->assertNull($deletedAdmin->fresh());

        $this->actingAs($remainingAdmin)
            ->from(route('admin.profile.edit'))
            ->delete(route('admin.profile.destroy'), ['password' => 'Password123'])
            ->assertSessionHasErrorsIn('userDeletion', 'password');

        $this->assertSame(1, User::role(User::ROLE_ADMIN)->count());
        $this->assertNotNull($remainingAdmin->fresh());
    }

    public function test_final_admin_cannot_delete_their_profile(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        $response = $this->actingAs($admin)
            ->from(route('admin.profile.edit'))
            ->delete(route('admin.profile.destroy'), ['password' => 'Password123']);

        $response->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertNotNull($admin->fresh());
    }
}
