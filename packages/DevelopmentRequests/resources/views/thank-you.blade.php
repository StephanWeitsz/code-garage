@extends('layouts.app', ['title' => 'Request Received'])

@section('content')
    <section class="stack">
        <article class="panel">
            <p class="eyebrow">Request received</p>
            <h1>Thanks, we have your requirements.</h1>
            <p class="hero-copy">
                The admin team can now review the brief, do initial costing, and contact you for a quote or any missing
                information.
            </p>
            <div class="hero-actions">
                <a href="{{ route('development-requests.services.index') }}" class="button button-primary">View services</a>
                <a href="{{ route('courses.index') }}" class="button button-secondary">Explore learning</a>
            </div>
        </article>
    </section>
@endsection
