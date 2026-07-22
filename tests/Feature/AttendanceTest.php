<?php

namespace Tests\Feature;

use App\Models\Learner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_log_only_one_value_per_session_each_day(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        $learner = Learner::create([
            'fname' => 'Hamza',
            'lname' => 'Ali',
            'email' => 'hamza@example.com',
            'grade_level' => '3rd Year',
            'section' => 'A',
            'qr_code' => 'LEMS-TEST-HAMZA',
        ]);

        $first = $this->actingAs($admin)->postJson(route('admin.attendance.store'), [
            'qr_code' => $learner->qr_code,
            'session' => 'am_in',
        ]);
        $first->assertOk()->assertJson(['status' => 'success']);

        $savedTime = $learner->attendance()->firstOrFail()->am_in;

        $second = $this->actingAs($admin)->postJson(route('admin.attendance.store'), [
            'learner_id' => $learner->id,
            'session' => 'am_in',
        ]);
        $second->assertOk()->assertJson(['status' => 'warning']);

        $this->assertDatabaseCount('learner_attendance', 1);
        $this->assertSame($savedTime, $learner->attendance()->firstOrFail()->am_in);
    }

    public function test_unknown_qr_code_is_rejected(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        $this->actingAs($admin)->postJson(route('admin.attendance.store'), [
            'qr_code' => 'UNKNOWN-CODE',
            'session' => 'am_in',
        ])->assertUnprocessable()->assertJson(['status' => 'warning']);

        $this->assertDatabaseCount('learner_attendance', 0);
    }

    public function test_employee_cannot_submit_attendance(): void
    {
        $employee = $this->createUserWithRole(User::ROLE_EMPLOYEE);

        $this->actingAs($employee)->postJson(route('admin.attendance.store'), [
            'qr_code' => 'ANY',
            'session' => 'am_in',
        ])->assertForbidden();
    }
}
