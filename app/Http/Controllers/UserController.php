<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\SendCustomEmailRequest;
use App\Http\Requests\Admin\SendWelcomeEmailRequest;
use App\Http\Requests\Admin\UpdateManagedUserRequest;
use App\Mail\CustomMessageMail;
use App\Mail\WelcomeMail;
use App\Models\EmailLog;
use App\Models\User;
use App\Services\AdministratorAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function __construct(
        private readonly AdministratorAccountService $administratorAccounts
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('roles')
            ->latest()
            ->paginate(10);

        return view('users.index', [
            'users' => $users,
            'roles' => User::assignableRoles(),
        ]);
    }

    public function update(UpdateManagedUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $currentRole = $user->getRoleNames()->first();

        if ($request->user()->is($user) && $validated['role'] !== $currentRole) {
            throw ValidationException::withMessages([
                'role' => 'You cannot change your own role.',
            ]);
        }

        $this->administratorAccounts->updateManagedUser(
            $user,
            $validated['name'],
            $validated['email'],
            $validated['role']
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User details were updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if ($request->user()->is($user)) {
            throw ValidationException::withMessages([
                'user' => 'You cannot delete your own account from user management.',
            ]);
        }

        $this->administratorAccounts->deleteManagedUser($user);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function sendMail(SendWelcomeEmailRequest $request): RedirectResponse
    {
        $users = User::whereIn('id', $request->validated('recipients'))->get();
        [$sent, $failed] = $this->sendMessages(
            $users,
            fn (User $user): WelcomeMail => new WelcomeMail($user),
            'Welcome to Employee LEMS'
        );

        return back()->with(
            $failed > 0 ? 'warning' : 'success',
            $this->deliveryMessage($sent, $failed)
        );
    }

    public function customEmailForm(): View
    {
        $this->authorize('viewAny', User::class);

        return view('custom-email', [
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function sendCustomEmail(SendCustomEmailRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $users = User::whereIn('id', $validated['recipients'])->get();

        [$sent, $failed] = $this->sendMessages(
            $users,
            fn (User $user): CustomMessageMail => new CustomMessageMail(
                $validated['subject'],
                $validated['content']
            ),
            $validated['subject']
        );

        return back()->with(
            $failed > 0 ? 'warning' : 'success',
            $this->deliveryMessage($sent, $failed)
        );
    }

    /**
     * @param  iterable<int, User>  $users
     * @param  callable(User): Mailable  $mailableFactory
     * @return array{0: int, 1: int}
     */
    private function sendMessages(iterable $users, callable $mailableFactory, string $subject): array
    {
        $sent = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                Mail::to($user->email)->send($mailableFactory($user));

                EmailLog::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'subject' => $subject,
                    'is_sent' => true,
                    'sent_at' => now(),
                ]);

                $sent++;
            } catch (Throwable $exception) {
                EmailLog::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'subject' => $subject,
                    'is_sent' => false,
                    'error_message' => class_basename($exception),
                ]);

                $failed++;
                Log::error('Email delivery failed.', [
                    'error_code' => 'MAIL_DELIVERY_FAILED',
                    'user_id' => $user->id,
                    'exception_class' => $exception::class,
                ]);
            }
        }

        return [$sent, $failed];
    }

    private function deliveryMessage(int $sent, int $failed): string
    {
        if ($failed === 0) {
            return "Email sent to {$sent} recipient(s).";
        }

        return "Email sent to {$sent} recipient(s); {$failed} delivery attempt(s) failed.";
    }
}
