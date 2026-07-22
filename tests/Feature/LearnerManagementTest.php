<?php

namespace Tests\Feature;

use App\Models\Learner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearnerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_learner_and_server_generates_qr_code(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        $response = $this->actingAs($admin)->post(route('admin.learners.store'), [
            'fname' => ' Ayaan ',
            'mname' => '',
            'lname' => ' Khan ',
            'email' => 'AYAAN@example.com',
            'grade_level' => '1st Year',
            'section' => 'A',
            'qr_code' => 'ATTACKER-CONTROLLED',
        ]);

        $response->assertSessionHasNoErrors();
        $learner = Learner::where('email', 'ayaan@example.com')->firstOrFail();
        $this->assertSame('Ayaan', $learner->fname);
        $this->assertNull($learner->mname);
        $this->assertNotSame('ATTACKER-CONTROLLED', $learner->qr_code);
        $this->assertStringStartsWith('LEMS-', $learner->qr_code);
    }

    public function test_non_admin_cannot_modify_learner_by_id(): void
    {
        $employee = $this->createUserWithRole(User::ROLE_EMPLOYEE);
        $learner = Learner::create([
            'fname' => 'Sara',
            'lname' => 'Ahmed',
            'email' => 'sara@example.com',
            'grade_level' => '2nd Year',
            'section' => 'B',
        ]);

        $this->actingAs($employee)
            ->delete(route('admin.learners.destroy', $learner))
            ->assertForbidden();

        $this->assertNotNull($learner->fresh());
    }

    public function test_duplicate_learner_email_is_rejected(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        Learner::create([
            'fname' => 'Existing',
            'lname' => 'Learner',
            'email' => 'learner@example.com',
            'grade_level' => '1st Year',
            'section' => 'A',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.learners.store'), [
            'fname' => 'Duplicate',
            'lname' => 'Learner',
            'email' => 'LEARNER@example.com',
            'grade_level' => '1st Year',
            'section' => 'A',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('learners', 1);
    }
}
