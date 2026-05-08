@extends('layouts.app', ['title' => 'Profile'])

@section('content')
    <section class="stack account-shell">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Account center</p>
                <h1>Profile & security</h1>
                <p class="hero-copy">Manage your account details, lecturer profile, password, and security settings from one place.</p>
            </div>
        </div>

        @if (Laravel\Fortify\Features::canUpdateProfileInformation())
            @livewire('profile.update-profile-information-form')

            <x-section-border />
        @endif

        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
            @livewire('profile.update-password-form')

            <x-section-border />
        @endif

        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            @livewire('profile.two-factor-authentication-form')

            <x-section-border />
        @endif

        @livewire('profile.logout-other-browser-sessions-form')

        @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
            <x-section-border />

            @livewire('profile.delete-user-form')
        @endif
    </section>
@endsection
