<x-guest-layout>
    <section class="auth-shell">
        <div class="auth-form-panel">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <h1 class="auth-title">Log In</h1>
            <p class="auth-subtitle">Access your colocation space and continue managing shared expenses.</p>
            <a href="{{ route('home') }}" class="auth-link mt-3 inline-flex text-sm">&larr; Back to home</a>

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-6">
                @csrf

                <div>
                    <label for="email" class="auth-label">{{ __('Email') }}</label>
                    <div class="auth-input-line">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="auth-input" />
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
                        <input id="password" type="password" name="password" required autocomplete="current-password" class="auth-input" />
                        <svg class="auth-input-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M8 11V8a4 4 0 1 1 8 0v3" fill="none" stroke="currentColor" stroke-width="1.8" />
                            <rect x="6" y="11" width="12" height="9" rx="2" fill="none" stroke="currentColor" stroke-width="1.8" />
                        </svg>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label for="remember_me" class="inline-flex items-center gap-2 text-indigo-100/90">
                        <input id="remember_me" type="checkbox" name="remember" class="rounded border-indigo-200/50 bg-indigo-100/20 text-indigo-200 focus:ring-indigo-200/80">
                        <span>{{ __('Remember me') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="auth-link">{{ __('Forgot password?') }}</a>
                    @endif
                </div>

                <button type="submit" class="auth-cta">{{ __('Log in') }}</button>
            </form>

            @if (Route::has('register'))
                <p class="mt-8 text-center text-sm text-indigo-100/90">
                    {{ __("Don't have an account?") }}
                    <a href="{{ route('register') }}" class="auth-link">{{ __('Sign up') }}</a>
                </p>
            @endif
        </div>

        <div class="auth-visual-panel">
            <img src="{{ asset('images/logo.png') }}" alt="Decorative logo for authentication page" />
        </div>
    </section>
</x-guest-layout>
