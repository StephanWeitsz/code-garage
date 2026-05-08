@extends('layouts.app', ['title' => 'My Learning'])

@section('content')
    <section class="stack">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Student zone</p>
                <h1>My Learning</h1>
            </div>
        </div>

        <div class="card-list">
            @forelse ($enrollments as $enrollment)
                <article class="course-card">
                    <span class="pill">{{ $enrollment->status->value }}</span>
                    <strong>{{ $enrollment->course->title }}</strong>
                    <p>{{ $enrollment->course->category }} - {{ $enrollment->course->difficulty_level->value }}</p>
                    <a href="{{ route('courses.show', $enrollment->course->slug) }}" class="button button-secondary">Continue</a>
                    @php
                        $liveMeetingUrl = $enrollment->meeting_url ?: ($enrollment->course->default_meeting_url ?? null);
                    @endphp
                    @if (filled($liveMeetingUrl))
                        <a href="{{ $liveMeetingUrl }}" target="_blank" rel="noopener noreferrer" class="button button-secondary">Join live session</a>
                    @endif
                </article>
            @empty
                <p class="muted">You have not enrolled in any courses yet.</p>
            @endforelse
        </div>
    </section>
@endsection
