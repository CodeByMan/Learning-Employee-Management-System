<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="auth-title">Welcome back</div>
    <p class="auth-subtitle">Sign in to continue managing your learner and employee workflows.</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-4">
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" class="block mt-2 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="Enter your email address" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="small">Forgot password?</a>
                @endif
            </div>

            <div class="position-relative mt-2">
                <x-text-input
                    id="password"
                    class="block w-full pe-5"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                />
                <button
                    type="button"
                    class="password-toggle position-absolute top-50 end-0 translate-middle-y border-0 bg-transparent text-muted me-2 p-2"
                    data-password-toggle="password"
                    aria-label="Show password"
                    aria-pressed="false"
                >
                    <i class="bi bi-eye" aria-hidden="true"></i>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
            <label for="remember_me" class="d-inline-flex align-items-center gap-2 text-muted small mb-0">
                <input id="remember_me" type="checkbox" class="form-check-input mt-0" name="remember">
                <span>{{ __('Remember me') }}</span>
            </label>
            <a href="{{ url('/') }}" class="small text-muted">Back to Home</a>
        </div>

        <x-primary-button class="w-100 justify-center">
            {{ __('Log in') }}
        </x-primary-button>

        <div class="auth-footer text-center">
            Need an account? <a href="{{ route('register') }}">Create a learner account</a>
        </div>
    </form>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.passwordToggle);
                const icon = button.querySelector('i');
                const isHidden = input.type === 'password';

                input.type = isHidden ? 'text' : 'password';
                button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                button.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                icon.classList.toggle('bi-eye', ! isHidden);
                icon.classList.toggle('bi-eye-slash', isHidden);
            });
        });
    </script>
</x-guest-layout>
