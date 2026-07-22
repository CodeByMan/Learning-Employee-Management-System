<?php

namespace Tests\Feature;

use App\Mail\AdminRegistrationOtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_registration_requires_valid_otp_before_user_is_created(): void
    {
        Mail::fake();
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        $otp = null;

        $response = $this->actingAs($admin)->post(route('admin.register.user'), [
            'name' => 'Managed Employee',
            'email' => 'managed@example.com',
            'password' => 'ManagedPassword123',
            'password_confirmation' => 'ManagedPassword123',
            'role' => User::ROLE_EMPLOYEE,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('admin.otp.verify.form'));
        $this->assertDatabaseMissing('users', ['email' => 'managed@example.com']);

        Mail::assertSent(AdminRegistrationOtpMail::class, function (AdminRegistrationOtpMail $mail) use (&$otp): bool {
            $otp = $mail->otp;

            return $mail->hasTo('managed@example.com');
        });

        $this->assertNotNull($otp);

        $verifyResponse = $this->actingAs($admin)->post(route('admin.otp.verify.submit'), [
            'otp' => $otp,
        ]);

        $verifyResponse->assertSessionHasNoErrors()->assertRedirect(route('admin.users.index'));
        $managedUser = User::where('email', 'managed@example.com')->firstOrFail();
        $this->assertTrue($managedUser->hasRole(User::ROLE_EMPLOYEE));
        $this->assertNotNull($managedUser->email_verified_at);
    }

    public function test_invalid_otp_does_not_create_user(): void
    {
        Mail::fake();
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        $this->actingAs($admin)->post(route('admin.register.user'), [
            'name' => 'Managed Learner',
            'email' => 'managed-learner@example.com',
            'password' => 'ManagedPassword123',
            'password_confirmation' => 'ManagedPassword123',
            'role' => User::ROLE_LEARNER,
        ]);

        $this->actingAs($admin)->post(route('admin.otp.verify.submit'), ['otp' => '000000'])
            ->assertSessionHasErrors('otp');

        $this->assertDatabaseMissing('users', ['email' => 'managed-learner@example.com']);
    }
}
