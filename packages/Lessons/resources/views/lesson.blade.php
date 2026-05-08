@extends('layouts.app', ['title' => $lesson->title])

@section('content')
    <section class="stack">
        <div class="hero-actions">
            <a href="{{ route('courses.index') }}" class="button button-secondary">All courses</a>
            <a href="{{ route('courses.show', $course->slug) }}" class="button button-secondary">Back to {{ $course->title }}</a>
        </div>

        <div class="section-heading">
            <div>
                <p class="eyebrow">{{ $course->title }}</p>
                <h1>{{ $lesson->title }}</h1>
                @if ($lesson->section)
                    <p class="muted">Section {{ $lesson->section->sequence }} - {{ $lesson->section->title }}</p>
                @endif
            </div>
            @auth
                @if ($isEnrolled && $blockingAssignments->isNotEmpty() && ! $isCompleted)
                    <div class="course-requirements-card">
                        <strong>Complete required assignment(s) first</strong>
                        <p class="muted">You need to submit these before this lesson can be marked complete:</p>
                        <ul class="course-requirements-list">
                            @foreach ($blockingAssignments as $blockingAssignment)
                                <li>{{ $blockingAssignment->title }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if ($canComplete)
                    <form method="POST" action="{{ route('enrollments.complete-lesson', $lesson->id) }}">
                        @csrf
                        <button type="submit" class="button {{ $isCompleted ? 'button-secondary' : 'button-primary' }}">
                            {{ $isCompleted ? 'Completed' : 'Mark lesson complete' }}
                        </button>
                    </form>
                @endif
            @endauth
        </div>

        <div class="grid-two">
            <article class="panel prose-panel">
                @if ($lesson->content_type->value === 'code')
                    <pre><code>{{ $lesson->content }}</code></pre>
                @elseif ($lesson->content_type->value === 'video')
                    <p>Video lesson URL:</p>
                    <a href="{{ $lesson->content }}" target="_blank" rel="noreferrer">{{ $lesson->content }}</a>
                @elseif ($lesson->content_type->value === 'markdown')
                    {!! $renderedContent !!}
                @else
                    <p>{!! nl2br(e($lesson->content)) !!}</p>
                @endif
            </article>

            <aside class="panel stack">
                <h2>Lesson assignments</h2>
                @forelse ($assignments as $assignment)
                    @php($mySubmission = $assignment->relationLoaded('mySubmission') ? $assignment->getRelation('mySubmission') : null)
                    @php($effectiveDueAt = $mySubmission?->due_at ?? $assignment->due_at)
                    <article class="course-requirements-card">
                        <strong>{{ $assignment->title }}</strong>
                        @if ($assignment->requires_completion_before_lesson_complete)
                            <p class="muted"><strong>Completion required:</strong> Submit this assignment before you can continue to the next lesson.</p>
                        @endif
                        @if ($effectiveDueAt)
                            <p class="muted">Due {{ $effectiveDueAt->format('d M Y H:i') }}</p>
                            @if (! auth()->user()?->hasAnyRole(['admin', 'lecturer']))
                                @php($daysDelta = now()->startOfDay()->diffInDays($effectiveDueAt->copy()->startOfDay(), false))
                                <p class="muted">
                                    @if ($daysDelta > 0)
                                        Due in {{ $daysDelta }} day(s)
                                    @elseif ($daysDelta === 0)
                                        Due today
                                    @else
                                        Overdue by {{ abs($daysDelta) }} day(s)
                                    @endif
                                </p>
                            @endif
                        @elseif ($assignment->due_days_after_completion)
                            <p class="muted">Due {{ $assignment->due_days_after_completion }} day(s) after completion</p>
                        @else
                            <p class="muted">No due date set</p>
                        @endif
                        @if ($mySubmission)
                            <p class="muted">Status: {{ ucfirst($mySubmission->status) }}</p>
                        @endif
                        <a href="{{ route('assignments.show', $assignment->id) }}" class="button button-secondary">Open assignment</a>
                    </article>
                @empty
                    <p class="muted">No assignments linked to this lesson.</p>
                @endforelse

                <h2>Lesson discussion</h2>
                @if (auth()->check() && $isEnrolled)
                    <details class="course-requirements-card">
                        <summary class="button button-secondary">I have a question</summary>
                        <form method="POST" action="{{ route('posts.store') }}" class="stack mt-4">
                            @csrf
                            <input type="hidden" name="lesson_id" value="{{ $lesson->id }}">
                            <input type="hidden" name="type" value="discussion">
                            <div>
                                <label for="question-title">Question title</label>
                                <input id="question-title" name="title" type="text" class="auth-input" placeholder="Optional subject">
                            </div>
                            <div>
                                <label for="question-body">Question</label>
                                <textarea id="question-body" name="body" rows="4" class="auth-input" placeholder="Ask a question about this lesson..." required></textarea>
                            </div>
                            <button type="submit" class="button button-primary">Post question</button>
                        </form>
                    </details>
                @else
                    <p class="muted">Enroll in this course to ask lesson questions.</p>
                @endif

                <div class="stack">
                    @forelse ($lessonPosts as $post)
                        <article class="course-requirements-card">
                            <strong>{{ $post->title }}</strong>
                            <p class="muted">{{ str($post->type)->replace('_', ' ')->title() }} - {{ $post->author->name }} - {{ $post->created_at->diffForHumans() }}</p>
                            <p>{{ \Illuminate\Support\Str::limit($post->body, 160) }}</p>
                            <a href="{{ route('posts.show', $post->id) }}" class="button button-secondary">Open thread</a>
                        </article>
                    @empty
                        <p class="muted">No discussion threads yet for this lesson.</p>
                    @endforelse
                </div>

            </aside>
        </div>

        @auth
            @if ($canComplete)
                <div class="hero-actions">
                    <form method="POST" action="{{ route('enrollments.complete-lesson', $lesson->id) }}">
                        @csrf
                        <button type="submit" class="button {{ $isCompleted ? 'button-secondary' : 'button-primary' }}">
                            {{ $isCompleted ? 'Completed' : 'Mark lesson complete' }}
                        </button>
                    </form>
                </div>
            @endif
        @endauth

        <article class="panel">
            <h2>Course map</h2>
            <div class="course-sections course-sections-compact">
                @foreach ($sections as $section)
                    <article class="course-section-card course-section-card-compact">
                        <div class="course-section-header course-section-header-compact">
                            <div>
                                <span class="course-section-index">Section {{ $section->sequence }}</span>
                                <h3>{{ $section->title }}</h3>
                            </div>
                        </div>

                        <div class="lesson-list">
                            @foreach ($section->lessons as $item)
                                @php($isMapLessonCompleted = in_array($item->id, $completedLessonIds, true))
                                <a href="{{ route('lessons.show', [$course->slug, $item->slug]) }}" class="lesson-row {{ $item->id === $lesson->id ? 'lesson-row-active' : '' }} {{ $isMapLessonCompleted ? 'lesson-row-completed' : '' }}">
                                    <span class="lesson-seq">{{ sprintf('%02d', $item->sequence) }}</span>
                                    <div>
                                        <strong>{{ $item->title }}</strong>
                                        <p>{{ $item->content_type->value }}</p>
                                    </div>
                                    @if ($isMapLessonCompleted)
                                        <span class="lesson-completed-pill">Completed</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </article>
    </section>
@endsection
