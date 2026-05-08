@extends('layouts.app', ['title' => $event->title])

@section('content')
    <section class="stack">
        <div class="hero-actions">
            <a href="{{ route('events.index') }}" class="button button-secondary">Back to events</a>
        </div>

        <article class="course-hero">
            <div>
                <div class="course-meta">
                    <span class="pill">{{ $event->typeLabel() }}</span>
                    <span class="pill pill-muted">{{ $event->starts_at->format('d M Y') }}</span>
                </div>
                <h1>{{ $event->title }}</h1>
                <div class="hero-copy">{{ $event->summary }}</div>
            </div>

            <div class="course-sidecard">
                <article class="course-side-pricing">
                    <span class="eyebrow">Date and time</span>
                    <strong class="course-price-main">{{ $event->starts_at->format('d M Y') }}</strong>
                    <p class="muted">
                        {{ $event->starts_at->format('H:i') }}
                        @if ($event->ends_at)
                            - {{ $event->ends_at->format('H:i') }}
                        @endif
                    </p>
                </article>

                <article class="course-side-pricing">
                    <span class="eyebrow">Place</span>
                    <strong class="course-price-main">{{ $event->is_online ? 'Online' : ($event->location ?: 'Venue to be confirmed') }}</strong>
                    @if ($event->capacity)
                        <p class="muted">Capacity: {{ $event->capacity }} people</p>
                    @endif
                </article>

                @if ($event->is_online && filled($event->meeting_url))
                    <a href="{{ $event->meeting_url }}" target="_blank" rel="noopener noreferrer" class="button button-primary">Open meeting link</a>
                @endif
            </div>
        </article>

        <section class="panel">
            <div class="section-heading">
                <div>
                    <h2>Event details</h2>
                </div>
            </div>

            @if (filled($event->description))
                <div class="hero-copy course-description">{!! nl2br(e($event->description)) !!}</div>
            @else
                <p class="muted">More details will be added closer to the event.</p>
            @endif
        </section>
    </section>
@endsection
