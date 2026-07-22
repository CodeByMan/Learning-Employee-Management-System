<?php

namespace Tests\Feature;

use App\Mail\CustomMessageMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class CustomEmailSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_email_content_is_rendered_as_escaped_plain_text(): void
    {
        Mail::fake();
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        $recipient = $this->createUserWithRole(User::ROLE_EMPLOYEE);

        $response = $this->actingAs($admin)->post(route('admin.custom-email.send'), [
            'subject' => 'Security Test',
            'content' => '<script>alert("xss")</script>',
            'recipients' => [$recipient->id],
        ]);

        $response->assertSessionHasNoErrors();

        Mail::assertSent(CustomMessageMail::class, function (CustomMessageMail $mail) use ($recipient): bool {
            $html = $mail->render();

            return $mail->hasTo($recipient->email)
                && ! str_contains($html, '<script>')
                && str_contains($html, '&lt;script&gt;');
        });

        $this->assertDatabaseHas('email_logs', [
            'user_id' => $recipient->id,
            'subject' => 'Security Test',
            'is_sent' => true,
        ]);
    }

    public function test_custom_email_subject_rejects_header_newlines(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        $recipient = $this->createUserWithRole(User::ROLE_EMPLOYEE);

        $response = $this->actingAs($admin)->post(route('admin.custom-email.send'), [
            'subject' => "Safe subject\r\nBcc: attacker@example.com",
            'content' => 'Message content',
            'recipients' => [$recipient->id],
        ]);

        $response->assertSessionHasErrors('subject');
        $this->assertDatabaseCount('email_logs', 0);
    }

    public function test_mail_failure_logs_only_safe_exception_metadata(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        $recipient = $this->createUserWithRole(User::ROLE_EMPLOYEE);

        Log::spy();
        Mail::shouldReceive('to')
            ->once()
            ->with($recipient->email)
            ->andThrow(new RuntimeException('Sensitive SMTP provider response'));

        $response = $this->actingAs($admin)->post(route('admin.custom-email.send'), [
            'subject' => 'Delivery test',
            'content' => 'Message content',
            'recipients' => [$recipient->id],
        ]);

        $response->assertSessionHas('warning');
        $this->assertDatabaseHas('email_logs', [
            'user_id' => $recipient->id,
            'is_sent' => false,
            'error_message' => 'RuntimeException',
        ]);

        Log::shouldHaveReceived('error')
            ->once()
            ->with('Email delivery failed.', Mockery::on(function (array $context) use ($recipient): bool {
                return $context === [
                    'error_code' => 'MAIL_DELIVERY_FAILED',
                    'user_id' => $recipient->id,
                    'exception_class' => RuntimeException::class,
                ];
            }));
    }
}
