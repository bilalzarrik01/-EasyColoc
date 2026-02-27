<x-guest-layout>
    <section class="auth-shell">
        <div class="auth-form-panel">
            <h1 class="auth-title">Sign Up</h1>
            <p class="auth-subtitle">Create your account and start tracking shared rent and expenses.</p>
            <a href="{{ route('home') }}" class="auth-link mt-3 inline-flex text-sm">&larr; Back to home</a>

            <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-6">
                @csrf

                <div>
                    <label for="name" class="auth-label">{{ __('Name') }}</label>
                    <div class="auth-input-line">
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="auth-input" />
                        <svg class="auth-input-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="1.8" />
                            <path d="M5 19c1.8-3.2 4.2-4.8 7-4.8s5.2 1.6 7 4.8" fill="none" stroke="currentColor" stroke-width="1.8" />
                        </svg>
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <label for="email" class="auth-label">{{ __('Email') }}</label>
                    <div class="auth-input-line">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="auth-input" />
                        <svg class="auth-input-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 7h16v10H4z" fill="none" stroke="currentColor" stroke-width="1.8" />
                            <path d="m4 8 8 6 8-6" fill="none" stroke="currentColor" stroke-width="1.8" />
                        </svg>
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <label for="password" class="auth-label">{{ __('Password') }}</label>
                    <div class="auth-input-line">
                        <input id="password" type="password" name="password" required autocomplete="new-password" class="auth-input" />
                        <svg class="auth-input-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M8 11V8a4 4 0 1 1 8 0v3" fill="none" stroke="currentColor" stroke-width="1.8" />
                            <rect x="6" y="11" width="12" height="9" rx="2" fill="none" stroke="currentColor" stroke-width="1.8" />
                        </svg>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <label for="password_confirmation" class="auth-label">{{ __('Confirm Password') }}</label>
                    <div class="auth-input-line">
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="auth-input" />
                        <svg class="auth-input-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M8 11V8a4 4 0 1 1 8 0v3" fill="none" stroke="currentColor" stroke-width="1.8" />
                            <rect x="6" y="11" width="12" height="9" rx="2" fill="none" stroke="currentColor" stroke-width="1.8" />
                            <path d="m10 15 1.5 1.5L14.5 13.5" fill="none" stroke="currentColor" stroke-width="1.8" />
                        </svg>
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <button type="submit" class="auth-cta">{{ __('Sign up') }}</button>
            </form>

            <p class="mt-8 text-center text-sm text-indigo-100/90">
                {{ __('Already have an account?') }}
                <a href="{{ route('login') }}" class="auth-link">{{ __('Log in') }}</a>
            </p>
        </div>

        <div class="auth-visual-panel">
            <img src="{{ asset('images/logo.png') }}" alt="Decorative logo for authentication page" />
        </div>
    </section>
</x-guest-layout>
