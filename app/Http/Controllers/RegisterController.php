<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\RegisterManagedUserRequest;
use App\Http\Requests\Admin\VerifyRegistrationOtpRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Mail\AdminRegistrationOtpMail;
use App\Mail\WelcomeMail;
use App\Models\EmailLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Throwable;

class RegisterController extends Controller
{
    private const OTP_SESSION_KEY = 'pending_registration';

    private const OTP_MAX_ATTEMPTS = 5;

    public function showForm(): View
    {
        return view('register');
    }

    public function register(RegisterUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated): User {
            Role::findOrCreate(User::ROLE_LEARNER, 'web');

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            $user->assignRole(User::ROLE_LEARNER);

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('verification.notice');
    }

    public function showAdminRegisterForm(): View
    {
        $this->authorize('viewAny', User::class);

        return view('admin.register-user', [
            'roles' => User::assignableRoles(),
        ]);
    }

    public function registerByAdmin(RegisterManagedUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $otp = (string) random_int(100000, 999999);

        Session::put(self::OTP_SESSION_KEY, [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'otp_hash' => Hash::make($otp),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10)->toIso8601String(),
        ]);

        try {
            Mail::to($validated['email'])->send(
                new AdminRegistrationOtpMail($validated['name'], $otp)
            );
        } catch (Throwable $exception) {
            Session::forget(self::OTP_SESSION_KEY);
            Log::error('Unable to send registration OTP.', [
                'error_code' => 'OTP_MAIL_DELIVERY_FAILED',
                'exception_class' => $exception::class,
            ]);

            return back()
                ->withInput($request->safe()->except(['password', 'password_confirmation']))
                ->withErrors(['email' => 'The registration code could not be sent. Check the mail configuration and try again.']);
        }

        return redirect()
            ->route('admin.otp.verify.form')
            ->with('otpSent', 'A registration code was sent to the user email address.');
    }

    public function showOtpForm(): View|RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        if (! Session::has(self::OTP_SESSION_KEY)) {
            return redirect()
                ->route('admin.register.form')
                ->withErrors(['otp' => 'Start a new user registration before entering a code.']);
        }

        return view('admin.verify-otp');
    }

    public function verifyOtp(VerifyRegistrationOtpRequest $request): RedirectResponse
    {
        /** @var array<string, mixed>|null $pending */
        $pending = Session::get(self::OTP_SESSION_KEY);

        if (! $pending || now()->greaterThan(Carbon::parse($pending['expires_at']))) {
            Session::forget(self::OTP_SESSION_KEY);

            return redirect()
                ->route('admin.register.form')
                ->withErrors(['otp' => 'The registration code expired. Start the registration again.']);
        }

        $attempts = (int) ($pending['attempts'] ?? 0) + 1;

        if (! Hash::check((string) $request->validated('otp'), (string) $pending['otp_hash'])) {
            if ($attempts >= self::OTP_MAX_ATTEMPTS) {
                Session::forget(self::OTP_SESSION_KEY);

                return redirect()
                    ->route('admin.register.form')
                    ->withErrors(['otp' => 'Too many invalid attempts. Start the registration again.']);
            }

            $pending['attempts'] = $attempts;
            Session::put(self::OTP_SESSION_KEY, $pending);

            return back()->withErrors(['otp' => 'The registration code is invalid.']);
        }

        if (User::where('email', $pending['email'])->exists()) {
            Session::forget(self::OTP_SESSION_KEY);

            return redirect()
                ->route('admin.register.form')
                ->withErrors(['email' => 'A user with this email address already exists.']);
        }

        $user = DB::transaction(function () use ($pending): User {
            Role::findOrCreate((string) $pending['role'], 'web');

            $user = User::create([
                'name' => $pending['name'],
                'email' => $pending['email'],
                'password' => $pending['password'],
                'email_verified_at' => now(),
            ]);

            $user->assignRole((string) $pending['role']);

            return $user;
        });

        Session::forget(self::OTP_SESSION_KEY);

        try {
            Mail::to($user->email)->send(new WelcomeMail($user));

            EmailLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'subject' => 'Welcome to Employee LEMS',
                'is_sent' => true,
                'sent_at' => now(),
            ]);
        } catch (Throwable $exception) {
            EmailLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'subject' => 'Welcome to Employee LEMS',
                'is_sent' => false,
                'error_message' => class_basename($exception),
            ]);

            Log::warning('User created, but the welcome email could not be sent.', [
                'error_code' => 'WELCOME_MAIL_DELIVERY_FAILED',
                'user_id' => $user->id,
                'exception_class' => $exception::class,
            ]);

            return redirect()
                ->route('admin.users.index')
                ->with('warning', 'The user was created, but the welcome email could not be sent.');
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'The user was verified and registered successfully.');
    }
}
