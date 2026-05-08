@extends('layouts.app', ['title' => 'Development Services'])

@section('content')
    <section class="stack">
        <div class="hero-grid">
            <div>
                <p class="eyebrow">Custom development</p>
                <h1>Turn a rough idea into a buildable software brief.</h1>
                <p class="hero-copy">
                    Code Garage can help plan and quote websites, portals, workflow tools, automations, dashboards,
                    integrations, and learning-adjacent systems. You do not need an account to request a quote.
                </p>
                <div class="hero-actions">
                    <a href="{{ route('development-requests.services.requirements.create') }}" class="button button-primary">Submit requirements</a>
                    <a href="{{ route('courses.index') }}" class="button button-secondary">Go to learning</a>
                </div>
            </div>

            <div class="panel">
                <p class="eyebrow">Requirement gathering</p>
                <h2>We ask the useful questions first.</h2>
                <p class="hero-copy">
                    The form captures the problem, users, must-have features, integrations, data, timeline, and budget
                    range so the first quote conversation starts with substance.
                </p>
            </div>
        </div>

        <section class="card-list">
            <article class="course-card">
                <div class="course-meta"><span class="pill">Plan</span></div>
                <strong>Clarify the requirement</strong>
                <p>Describe what you want to build, who will use it, and what a successful outcome looks like.</p>
            </article>

            <article class="course-card">
                <div class="course-meta"><span class="pill">Scope</span></div>
                <strong>Separate essentials from extras</strong>
                <p>List must-have and nice-to-have features so the quote can be realistic and phased if needed.</p>
            </article>

            <article class="course-card">
                <div class="course-meta"><span class="pill">Quote</span></div>
                <strong>Admin review and costing</strong>
                <p>The admin team reviews each request, records costing notes, and replies by email or phone.</p>
            </article>
        </section>
    </section>
@endsection
