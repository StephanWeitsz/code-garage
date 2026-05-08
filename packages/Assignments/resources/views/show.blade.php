@extends('layouts.app', ['title' => $assignment->title])

@section('content')
    <section class="stack">
        <div class="hero-actions">
            <a href="{{ route('assignments.index') }}" class="button button-secondary">Back to assignments</a>
        </div>

        <article class="panel stack">
            <span class="pill">{{ $assignment->course->title }}</span>
            <h1>{{ $assignment->title }}</h1>
            <p class="muted">Max points: {{ $assignment->max_points }}</p>
            <div class="course-description">{!! nl2br(e($assignment->instructions)) !!}</div>
            <p class="muted">
                @if ($mySubmission?->due_at)
                    Due {{ $mySubmission->due_at->format('d M Y H:i') }}
                @elseif ($assignment->due_at)
                    Due {{ $assignment->due_at->format('d M Y H:i') }}
                @elseif ($assignment->due_days_after_completion)
                    Due {{ $assignment->due_days_after_completion }} day(s) after lesson completion
                @else
                    No due date set
                @endif
            </p>
        </article>

        @if (! $canManage)
            <article class="panel stack">
                <h2>Submit your work</h2>
                <form method="POST" action="{{ route('assignments.submit', $assignment->id) }}" class="stack" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <label for="content">Response</label>
                        <textarea id="content" name="content" rows="7" class="auth-input">{{ old('content', $mySubmission?->content) }}</textarea>
                    </div>
                    <div>
                        <label for="attachment">Attachment (optional)</label>
                        <input id="attachment" name="attachment" type="file" class="auth-input">
                    </div>
                    <button type="submit" class="button button-primary">Submit assignment</button>
                </form>

                @if ($mySubmission)
                    <p class="muted">Current status: {{ ucfirst($mySubmission->status) }}</p>
                    @if ($mySubmission->attachment_path)
                        <p>
                            <a href="{{ asset('storage/'.ltrim($mySubmission->attachment_path, '/')) }}" target="_blank" rel="noopener noreferrer" class="button button-secondary">View uploaded file</a>
                        </p>
                    @endif
                    @if ($mySubmission->score !== null)
                        <p class="muted">Score: {{ number_format((float) $mySubmission->score, 2) }}</p>
                    @endif
                    @if ($mySubmission->feedback)
                        <div class="course-requirements-card">
                            <h3>Feedback</h3>
                            <p>{{ $mySubmission->feedback }}</p>
                        </div>
                    @endif
                @endif
            </article>
        @endif

        @if ($canManage)
            <article class="panel stack">
                <h2>Submissions</h2>
                @forelse ($assignment->submissions as $submission)
                    <article class="course-requirements-card stack">
                        <div>
                            <strong>{{ $submission->student->name }}</strong>
                            <p class="muted">{{ ucfirst($submission->status) }}{{ $submission->submitted_at ? ' • '.$submission->submitted_at->diffForHumans() : '' }}</p>
                        </div>
                        @if ($submission->content)
                            <p>{{ $submission->content }}</p>
                        @endif
                        @if ($submission->attachment_path)
                            <p>
                                <a href="{{ asset('storage/'.ltrim($submission->attachment_path, '/')) }}" target="_blank" rel="noopener noreferrer" class="button button-secondary">Open attached file</a>
                            </p>
                        @endif

                        <form method="POST" action="{{ route('assignments.grade', [$assignment->id, $submission->id]) }}" class="stack">
                            @csrf
                            <div class="grid-two">
                                <div>
                                    <label for="score-{{ $submission->id }}">Score</label>
                                    <input
                                        id="score-{{ $submission->id }}"
                                        name="score"
                                        type="number"
                                        min="0"
                                        max="{{ $assignment->max_points }}"
                                        value="{{ $submission->score }}"
                                        class="auth-input"
                                    >
                                </div>
                            </div>
                            <div>
                                <label for="feedback-{{ $submission->id }}">Feedback</label>
                                <textarea id="feedback-{{ $submission->id }}" name="feedback" rows="3" class="auth-input">{{ $submission->feedback }}</textarea>
                            </div>
                            <button type="submit" class="button button-secondary">Save grading</button>
                        </form>
                    </article>
                @empty
                    <p class="muted">No submissions yet.</p>
                @endforelse
            </article>
        @endif
    </section>
@endsection
