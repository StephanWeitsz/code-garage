<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="muted">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </div>

        <x-validation-errors class="mt-4" />

        <form method="POST" action="{{ route('password.confirm') }}" class="auth-form mt-6">
            @csrf

            <div class="auth-field">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block w-full" type="password" name="password" required autocomplete="current-password" autofocus />
            </div>

            <div class="auth-actions justify-end">
                <x-button>
                    {{ __('Confirm') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
