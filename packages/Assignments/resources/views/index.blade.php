@extends('layouts.app', ['title' => 'Assignments'])

@section('content')
    <section class="stack">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Phase 2</p>
                <h1>Assignments</h1>
            </div>
        </div>

        @if ($canManage)
            <article class="panel">
                <h2>Lecturer setup</h2>
                <p class="muted">Create and manage assignments from the Lecturer/Admin panel so each assignment is linked to the correct course and lesson.</p>
            </article>
        @endif

        <div class="card-list">
            @forelse ($assignments as $assignment)
                @php($submission = $assignment->relationLoaded('mySubmission') ? $assignment->getRelation('mySubmission') : null)
                <article class="course-card">
                    <span class="pill">{{ $assignment->course->title }}</span>
                    <strong>{{ $assignment->title }}</strong>
                    @if ($assignment->lesson)
                        <p>Lesson: {{ $assignment->lesson->title }}</p>
                    @endif
                    @php($effectiveDueAt = $submission?->due_at ?? $assignment->due_at ?? null)
                    <p>
                        @if (isset($effectiveDueAt) && $effectiveDueAt)
                            Due {{ $effectiveDueAt->format('d M Y H:i') }}
                        @elseif ($assignment->due_days_after_completion)
                            Due {{ $assignment->due_days_after_completion }} day(s) after lesson completion
                        @else
                            No due date
                        @endif
                    </p>
                    @if (! $canManage)
                        @if (isset($effectiveDueAt) && $effectiveDueAt)
                            @php($daysDelta = now()->startOfDay()->diffInDays($effectiveDueAt->copy()->startOfDay(), false))
                            <p>
                                @if ($daysDelta > 0)
                                    Due in {{ $daysDelta }} day(s)
                                @elseif ($daysDelta === 0)
                                    Due today
                                @else
                                    Overdue by {{ abs($daysDelta) }} day(s)
                                @endif
                            </p>
                        @endif
                        <p>
                            @if ($submission)
                                Submission status: {{ ucfirst($submission->status) }}
                            @else
                                Not submitted yet
                            @endif
                        </p>
                    @endif
                    <a href="{{ route('assignments.show', $assignment->id) }}" class="button button-secondary">Open</a>
                </article>
            @empty
                <p class="muted">No assignments available yet.</p>
            @endforelse
        </div>
    </section>
@endsection
