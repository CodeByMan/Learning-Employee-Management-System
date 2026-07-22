<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\SendAnnouncementRequest;
use App\Http\Requests\Admin\StoreAnnouncementRequest;
use App\Mail\AnnouncementEmail;
use App\Models\Announcement;
use App\Models\AnnouncementLog;
use App\Models\AnnouncementTarget;
use App\Models\Learner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class AnnouncementController extends Controller
{
    private const MAX_SYNC_RECIPIENTS = 25;

    public function index(): View
    {
        $this->authorize('viewAny', Announcement::class);

        return view('admin.announcements.index', [
            'announcements' => Announcement::withCount('targets')->latest()->paginate(10),
        ]);
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        Announcement::create([
            ...$request->validated(),
            'sent_by' => $request->user()->name,
        ]);

        return back()->with('success', 'Announcement created successfully.');
    }

    public function sendForm(): View
    {
        $this->authorize('viewAny', Announcement::class);

        return view('admin.announcements.send', [
            'announcements' => Announcement::latest()->get(['id', 'title']),
            'gradeLevels' => Learner::whereNotNull('grade_level')->distinct()->orderBy('grade_level')->pluck('grade_level'),
            'sections' => Learner::whereNotNull('section')->distinct()->orderBy('section')->pluck('section'),
        ]);
    }

    public function processSend(SendAnnouncementRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $announcement = Announcement::findOrFail($validated['announcement_id']);
        $this->authorize('send', $announcement);

        $recipientQuery = Learner::query()
            ->whereNotNull('email')
            ->when($validated['grade_level'] ?? null, fn (Builder $query, string $grade): Builder => $query->where('grade_level', $grade))
            ->when($validated['section'] ?? null, fn (Builder $query, string $section): Builder => $query->where('section', $section));

        if ((clone $recipientQuery)->count() > self::MAX_SYNC_RECIPIENTS) {
            return back()
                ->withInput()
                ->with('warning', 'The selected audience exceeds 25 learners. Narrow the filters before sending.');
        }

        $recipients = $recipientQuery->get();

        if ($recipients->isEmpty()) {
            return back()->with('warning', 'No learners matched the selected audience.');
        }

        AnnouncementTarget::create([
            'announcement_id' => $announcement->id,
            'grade_level' => $validated['grade_level'] ?? 'All',
            'section' => $validated['section'] ?? 'All',
        ]);

        [$sent, $failed] = $this->deliver($announcement, $recipients);

        return redirect()
            ->route('admin.announcements.send-form')
            ->with($failed > 0 ? 'warning' : 'success', $this->deliveryMessage($sent, $failed));
    }

    public function resend(Announcement $announcement): RedirectResponse
    {
        $this->authorize('send', $announcement);
        $targets = $announcement->targets()->get();

        if ($targets->isEmpty()) {
            return back()->with('warning', 'This announcement has no saved audience to resend to.');
        }

        $query = Learner::query()->whereNotNull('email');

        if (! $targets->contains(fn (AnnouncementTarget $target): bool => $target->grade_level === 'All' && $target->section === 'All')) {
            $query->where(function (Builder $audienceQuery) use ($targets): void {
                foreach ($targets as $target) {
                    $audienceQuery->orWhere(function (Builder $targetQuery) use ($target): void {
                        if ($target->grade_level !== 'All') {
                            $targetQuery->where('grade_level', $target->grade_level);
                        }

                        if ($target->section !== 'All') {
                            $targetQuery->where('section', $target->section);
                        }
                    });
                }
            });
        }

        if ((clone $query)->distinct()->count('learners.id') > self::MAX_SYNC_RECIPIENTS) {
            return back()->with('warning', 'The saved audience now exceeds 25 learners. Narrow the audience in a new send.');
        }

        $recipients = $query->get()->unique('id')->values();

        if ($recipients->isEmpty()) {
            return back()->with('warning', 'No current learners match the saved audience.');
        }

        [$sent, $failed] = $this->deliver($announcement, $recipients);

        return back()->with($failed > 0 ? 'warning' : 'success', $this->deliveryMessage($sent, $failed));
    }

    public function logs(): View
    {
        $this->authorize('viewAny', Announcement::class);

        $logs = AnnouncementLog::with(['announcement', 'learner'])
            ->latest('created_at')
            ->paginate(10);

        return view('admin.announcements.logs', compact('logs'));
    }

    /**
     * @param  Collection<int, Learner>  $recipients
     * @return array{0: int, 1: int}
     */
    private function deliver(Announcement $announcement, Collection $recipients): array
    {
        $sent = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new AnnouncementEmail($announcement));

                AnnouncementLog::create([
                    'announcement_id' => $announcement->id,
                    'learner_id' => $recipient->id,
                    'recipient_name' => trim("{$recipient->fname} {$recipient->mname} {$recipient->lname}"),
                    'recipient_email' => $recipient->email,
                    'is_sent' => true,
                    'sent_at' => now(),
                ]);

                $sent++;
            } catch (Throwable $exception) {
                AnnouncementLog::create([
                    'announcement_id' => $announcement->id,
                    'learner_id' => $recipient->id,
                    'recipient_name' => trim("{$recipient->fname} {$recipient->mname} {$recipient->lname}"),
                    'recipient_email' => $recipient->email,
                    'is_sent' => false,
                    'error_message' => class_basename($exception),
                    'sent_at' => null,
                ]);

                $failed++;
                Log::error('Announcement email delivery failed.', [
                    'error_code' => 'ANNOUNCEMENT_MAIL_DELIVERY_FAILED',
                    'announcement_id' => $announcement->id,
                    'learner_id' => $recipient->id,
                    'exception_class' => $exception::class,
                ]);
            }
        }

        return [$sent, $failed];
    }

    private function deliveryMessage(int $sent, int $failed): string
    {
        if ($failed === 0) {
            return "Announcement sent to {$sent} learner(s).";
        }

        return "Announcement sent to {$sent} learner(s); {$failed} delivery attempt(s) failed.";
    }
}
