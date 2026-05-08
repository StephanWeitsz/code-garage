<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#0f172a">
        <link rel="manifest" href="/manifest.webmanifest">
        <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
        <link rel="apple-touch-icon" href="/icons/icon.svg">
        <title>{{ $title ?? 'Code Garage' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="app-shell">
        <header class="site-header">
            <div class="container nav-row">
                <a href="{{ route('welcome') }}" class="brand">
                    <span class="brand-mark"><img src="{{ asset('icons/icon.svg') }}" alt="Code Garage icon" class="brand-icon"></span>
                    <span>
                        <strong>Code Garage</strong>
                        <small>Learn, build and upgrade your skills</small>
                    </span>
                </a>

                <nav class="main-nav">
                    <div class="main-nav-top">
                        <a href="{{ route('development-requests.services.index') }}">Services</a>
                        <a href="{{ route('courses.index') }}">Courses</a>
                        <a href="{{ route('events.index') }}">Events</a>
                        @auth
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                            <a href="{{ route('posts.index') }}">Discussions</a>
                        @else
                            <a href="{{ route('login') }}">Login</a>
                            <a href="{{ route('register') }}">Register</a>
                        @endauth
                    </div>

                    @auth
                        <div class="main-nav-secondary">
                            <details class="nav-dropdown">
                                <summary>Learning</summary>
                                <div class="nav-dropdown-menu">
                                    <a href="{{ route('enrollments.index') }}">My Learning</a>
                                    <a href="{{ route('assignments.index') }}">Assignments</a>
                                    @if (auth()->user()->hasRole('student') || auth()->user()->can('payments.view'))
                                        <a href="{{ route('payments.index') }}">Payments</a>
                                    @endif
                                </div>
                            </details>

                            <details class="nav-dropdown">
                                <summary>Account</summary>
                                <div class="nav-dropdown-menu">
                                    <a href="{{ route('profile.show') }}">Profile</a>
                                    @if (auth()->user()->hasAnyRole(['admin', 'lecturer']))
                                        <a href="{{ url('/lecturer') }}">Lecturer Panel</a>
                                    @endif
                                    @if (auth()->user()->hasRole('admin'))
                                        <a href="{{ url('/admin') }}">Admin Panel</a>
                                    @endif
                                    @if (auth()->user()->hasRole('admin') && config('deployment_tools.enabled'))
                                        <a href="{{ route('deployment-tools.index') }}">Development Tools</a>
                                    @endif
                                </div>
                            </details>

                            <form method="POST" action="{{ route('logout') }}" class="nav-logout-form">
                                @csrf
                                <button type="submit" class="button button-secondary">Logout</button>
                            </form>
                        </div>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="container page-content">
            @if (session('status'))
                <div class="flash-message">{{ session('status') }}</div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </body>
</html>
