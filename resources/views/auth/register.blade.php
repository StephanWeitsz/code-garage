<x-guest-layout>
    <section class="auth-grid">
        <div class="auth-panel auth-panel-feature">
            <a href="{{ route('welcome') }}" class="brand">
                <span class="brand-mark"><img src="{{ asset('icons/icon.png') }}" alt="Code Garage icon" class="brand-icon"></span>
                <span>
                    <strong>Code Garage</strong>
                    <small>Learn, build and upgrade your skills</small>
                </span>
            </a>

            <div class="auth-copy">
                <p class="eyebrow">Start learning</p>
                <h1>Create your student account and join the portal.</h1>
                <p class="hero-copy">
                    Register once, explore structured courses, and track your progress from coding basics through to advanced automation topics.
                </p>
            </div>

            <div class="stats-grid">
                <div class="stat-card"><strong>Course access</strong><span>Browse published learning tracks</span></div>
                <div class="stat-card"><strong>Lesson flow</strong><span>Follow sequenced modules clearly</span></div>
                <div class="stat-card"><strong>Role-aware</strong><span>Students, lecturers, and admins</span></div>
                <div class="stat-card"><strong>PWA-ready</strong><span>Installable portal experience</span></div>
            </div>
        </div>

        <div class="auth-panel auth-panel-form">
            <div class="auth-form-header">
                <p class="eyebrow">New account</p>
                <h2>Register</h2>
                <p class="muted">New signups are created as student accounts automatically.</p>
            </div>

            <x-validation-errors class="auth-alert auth-alert-error" />

            <form method="POST" action="{{ route('register') }}" class="auth-form">
                @csrf

                <div class="auth-field">
                    <label for="name" class="auth-label">{{ __('Name') }}</label>
                    <input id="name" class="auth-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" />
                </div>

                <div class="auth-field">
                    <label for="email" class="auth-label">{{ __('Email') }}</label>
                    <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
                </div>

                <div class="auth-field">
                    <label for="mobile" class="auth-label">{{ __('Mobile') }}</label>
                    <input id="mobile" class="auth-input" type="text" name="mobile" value="{{ old('mobile') }}" required autocomplete="tel" />
                </div>

                <div class="auth-field">
                    <label for="password" class="auth-label">{{ __('Password') }}</label>
                    <input id="password" class="auth-input" type="password" name="password" required autocomplete="new-password" />
                </div>

                <div class="auth-field">
                    <label for="password_confirmation" class="auth-label">{{ __('Confirm Password') }}</label>
                    <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required autocomplete="new-password" />
                </div>

                @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                    <label for="terms" class="auth-check">
                        <input name="terms" id="terms" type="checkbox" class="auth-checkbox" required />
                        <span>
                            {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="auth-link">'.__('Terms of Service').'</a>',
                                'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="auth-link">'.__('Privacy Policy').'</a>',
                            ]) !!}
                        </span>
                    </label>
                @endif

                <div class="auth-actions">
                    <a href="{{ route('login') }}" class="auth-link">
                        {{ __('Already registered?') }}
                    </a>

                    <button type="submit" class="button button-primary">
                        {{ __('Register') }}
                    </button>
                </div>
            </form>
        </div>
    </section>
</x-guest-layout>


