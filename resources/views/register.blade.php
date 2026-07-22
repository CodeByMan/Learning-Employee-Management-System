<x-guest-layout>
    <div class="auth-title">Create learner account</div>
    <p class="auth-subtitle">Register, verify your email, and access your learner dashboard with the new UI experience.</p>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" id="registerForm">
        @csrf

        <div class="mb-4">
            <x-input-label for="name" :value="__('Full name')" />
            <x-text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" maxlength="255" placeholder="Enter your full name" />
        </div>

        <div class="mb-4">
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" class="block mt-2 w-full" type="email" name="email" :value="old('email')" required autocomplete="email" maxlength="255" placeholder="Enter your email address" />
        </div>

        <div class="mb-4">
            <x-input-label for="password" :value="__('Password')" />
            <div class="position-relative mt-2">
                <x-text-input
                    id="password"
                    class="block w-full pe-5"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="Create a secure password"
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
            <div class="form-text mt-2">Use at least 8 characters with letters and numbers.</div>
        </div>

        <div class="mb-4">
            <x-input-label for="password_confirmation" :value="__('Confirm password')" />
            <div class="position-relative mt-2">
                <x-text-input
                    id="password_confirmation"
                    class="block w-full pe-5"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Confirm your password"
                />
                <button
                    type="button"
                    class="password-toggle position-absolute top-50 end-0 translate-middle-y border-0 bg-transparent text-muted me-2 p-2"
                    data-password-toggle="password_confirmation"
                    aria-label="Show password confirmation"
                    aria-pressed="false"
                >
                    <i class="bi bi-eye" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <a href="{{ route('login') }}" class="small text-muted">Already registered?</a>
            <x-primary-button id="registerButton">{{ __('Register') }}</x-primary-button>
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

        document.getElementById('registerForm')?.addEventListener('submit', function () {
            const button = document.getElementById('registerButton');
            button.disabled = true;
            button.textContent = 'Registering...';
        });
    </script>
</x-guest-layout>
