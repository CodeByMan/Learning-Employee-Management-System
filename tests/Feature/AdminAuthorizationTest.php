<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_routes(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login', absolute: false));
    }

    public function test_employee_cannot_access_admin_routes(): void
    {
        $employee = $this->createUserWithRole(User::ROLE_EMPLOYEE);

        $this->actingAs($employee)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($employee)->get('/admin/users')->assertForbidden();
        $this->actingAs($employee)->get('/admin/learners')->assertForbidden();
        $this->actingAs($employee)->get('/admin/attendance')->assertForbidden();
    }

    public function test_learner_cannot_access_admin_routes(): void
    {
        $learner = $this->createUserWithRole(User::ROLE_LEARNER);

        $this->actingAs($learner)->get('/admin/announcements')->assertForbidden();
        $this->actingAs($learner)->get('/admin/custom-email')->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
    }

    public function test_legacy_public_learner_resource_is_not_exposed(): void
    {
        $this->get('/learners')->assertNotFound();
        $this->post('/learners')->assertNotFound();
    }
}
