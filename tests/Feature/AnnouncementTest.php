<?php

namespace Tests\Feature;

use App\Mail\AnnouncementEmail;
use App\Models\Announcement;
use App\Models\Learner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_announcement_author_is_derived_from_authenticated_admin(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN, ['name' => 'Authorized Admin']);

        $response = $this->actingAs($admin)->post(route('admin.announcements.store'), [
            'title' => 'Schedule Update',
            'content' => 'Classes begin at 9:00 AM.',
            'sent_by' => 'Forged Author',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('announcements', [
            'title' => 'Schedule Update',
            'sent_by' => 'Authorized Admin',
        ]);
    }

    public function test_announcement_title_rejects_header_newlines(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        $response = $this->actingAs($admin)->post(route('admin.announcements.store'), [
            'title' => "Safe title\r\nBcc: attacker@example.com",
            'content' => 'Message content',
        ]);

        $response->assertSessionHasErrors('title');
        $this->assertDatabaseCount('announcements', 0);
    }

    public function test_admin_can_send_announcement_and_delivery_is_logged(): void
    {
        Mail::fake();
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        $announcement = Announcement::create([
            'title' => 'Exam Reminder',
            'content' => 'Bring your identification card.',
            'sent_by' => $admin->name,
        ]);
        $learner = Learner::create([
            'fname' => 'Sara',
            'lname' => 'Ahmed',
            'email' => 'sara@example.com',
            'grade_level' => '2nd Year',
            'section' => 'B',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.announcements.process-send'), [
            'announcement_id' => $announcement->id,
            'grade_level' => '2nd Year',
            'section' => 'B',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('admin.announcements.send-form'));
        Mail::assertSent(AnnouncementEmail::class, fn (AnnouncementEmail $mail): bool => $mail->hasTo($learner->email));
        $this->assertDatabaseHas('announcement_logs', [
            'announcement_id' => $announcement->id,
            'learner_id' => $learner->id,
            'recipient_email' => $learner->email,
            'is_sent' => true,
        ]);
    }

    public function test_synchronous_announcement_delivery_rejects_more_than_twenty_five_recipients(): void
    {
        Mail::fake();
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        $announcement = Announcement::create([
            'title' => 'Audience Limit',
            'content' => 'This message should not be delivered synchronously.',
            'sent_by' => $admin->name,
        ]);

        for ($index = 1; $index <= 26; $index++) {
            Learner::create([
                'fname' => 'Learner',
                'lname' => (string) $index,
                'email' => "learner{$index}@example.com",
                'grade_level' => '1st Year',
                'section' => 'A',
            ]);
        }

        $response = $this->actingAs($admin)
            ->from(route('admin.announcements.send-form'))
            ->post(route('admin.announcements.process-send'), [
                'announcement_id' => $announcement->id,
                'grade_level' => '1st Year',
                'section' => 'A',
            ]);

        $response->assertRedirect(route('admin.announcements.send-form'))
            ->assertSessionHas('warning');
        Mail::assertNothingSent();
        $this->assertDatabaseCount('announcement_targets', 0);
        $this->assertDatabaseCount('announcement_logs', 0);
    }
}
