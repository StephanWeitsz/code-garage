<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="muted">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </div>

        @if (session('status'))
            <div class="auth-alert auth-alert-success mt-4">
                {{ session('status') }}
            </div>
        @endif

        <x-validation-errors class="mt-4" />

        <form method="POST" action="{{ route('password.email') }}" class="auth-form mt-6">
            @csrf

            <div class="auth-field">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="auth-actions justify-end">
                <x-button>
                    {{ __('Email Password Reset Link') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
