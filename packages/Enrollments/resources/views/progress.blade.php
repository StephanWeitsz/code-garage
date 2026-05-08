@extends('layouts.app', ['title' => 'Progress Tracking'])

@section('content')
    <section class="stack">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Phase 2</p>
                <h1>Progress Tracking</h1>
            </div>
        </div>

        <div class="stats-grid">
            <article class="stat-card">
                <strong>{{ $overview['courses'] }}</strong>
                <span>Active courses</span>
            </article>
            <article class="stat-card">
                <strong>{{ $overview['lesson_completion'] }}%</strong>
                <span>Lesson completion</span>
            </article>
            <article class="stat-card">
                <strong>{{ $overview['assignments_submitted'] }}</strong>
                <span>Assignments submitted</span>
            </article>
        </div>

        <div class="card-list">
            @forelse ($courses as $entry)
                <article class="course-card">
                    <span class="pill">{{ $entry['course']->title }}</span>
                    <strong>{{ $entry['completion_percent'] }}% completed</strong>
                    <p>Lessons: {{ $entry['lessons_done'] }} / {{ $entry['lessons_total'] }}</p>
                    <p>Assignments: {{ $entry['assignments_submitted'] }} / {{ $entry['assignments_total'] }}</p>
                    @if ($entry['average_score'] !== null)
                        <p>Average score: {{ number_format((float) $entry['average_score'], 1) }}</p>
                    @endif
                    <a href="{{ route('courses.show', $entry['course']->slug) }}" class="button button-secondary">Open course</a>
                </article>
            @empty
                <p class="muted">No progress data yet. Enroll in a course to begin tracking.</p>
            @endforelse
        </div>
    </section>
@endsection
