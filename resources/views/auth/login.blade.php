<x-guest-layout>
    <section class="auth-grid">
        <div class="auth-panel auth-panel-feature">
            <a href="{{ route('welcome') }}" class="brand">
                <span class="brand-mark"><img src="{{ asset('icons/icon.svg') }}" alt="Code Garage icon" class="brand-icon"></span>
                <span>
                    <strong>Code Garage</strong>
                    <small>Learn, build and upgrade your skills</small>
                </span>
            </a>

            <div class="auth-copy">
                <p class="eyebrow">Welcome back</p>
                <h1>Sign in and continue your learning sprint.</h1>
                <p class="hero-copy">
                    Access your dashboard, course catalog, lesson progress, and role-based teaching tools from one place.
                </p>
            </div>

            <div class="terminal-card">
                <div class="terminal-bar">
                    <span></span><span></span><span></span>
                </div>
                <pre><code>$ portal login
> verify credentials
> restore active session
> sync learning workspace</code></pre>
            </div>
        </div>

        <div class="auth-panel auth-panel-form">
            <div class="auth-form-header">
                <p class="eyebrow">Account access</p>
                <h2>Log in</h2>
                <p class="muted">Use your student, lecturer, or admin account to enter the portal.</p>
            </div>

            <x-validation-errors class="auth-alert auth-alert-error" />

            @if (session('status'))
                <div class="auth-alert auth-alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf

                <div class="auth-field">
                    <label for="email" class="auth-label">{{ __('Email') }}</label>
                    <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
                </div>

                <div class="auth-field">
                    <label for="password" class="auth-label">{{ __('Password') }}</label>
                    <input id="password" class="auth-input" type="password" name="password" required autocomplete="current-password" />
                </div>

                <label for="remember_me" class="auth-check">
                    <input id="remember_me" name="remember" type="checkbox" class="auth-checkbox" />
                    <span>{{ __('Remember me') }}</span>
                </label>

                <div class="auth-actions">
                    @if (Route::has('password.request'))
                        <a class="auth-link" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif

                    <button type="submit" class="button button-primary">
                        {{ __('Log in') }}
                    </button>
                </div>
            </form>

            <p class="auth-switch">
                Need an account?
                <a href="{{ route('register') }}" class="auth-link">{{ __('Create one here') }}</a>
            </p>
        </div>
    </section>
</x-guest-layout>


