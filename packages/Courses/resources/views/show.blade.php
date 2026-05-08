@extends('layouts.app', ['title' => $course->title])

@section('content')
    @php
        $canManageLessons = auth()->user()?->hasAnyRole(['admin', 'lecturer']) ?? false;
        $coverImage = null;
        if (filled($course->cover_image)) {
            $coverImage = str_starts_with($course->cover_image, 'http://') || str_starts_with($course->cover_image, 'https://')
                ? $course->cover_image
                : url('/storage/'.ltrim($course->cover_image, '/'));
        }
    @endphp

    <section class="stack">
        <div class="hero-actions">
            <a href="{{ route('courses.index') }}" class="button button-secondary">Back to courses</a>
        </div>

        <article class="course-hero">
            <div>
                <div class="course-meta">
                    <span class="pill">{{ $course->difficulty_level->value }}</span>
                    <span class="pill pill-muted">{{ $course->category }}</span>
                </div>
                <h1>{{ $course->title }}</h1>
                <div class="hero-copy course-description">{!! nl2br(e($course->description)) !!}</div>
            </div>

            <div class="course-sidecard">
                <div class="progress-ring">
                    <strong>{{ $completion }}%</strong>
                    <span>Completion</span>
                </div>

                <div class="course-lecturer-pricing-panel">
                    @if ($course->lecturer)
                        <a href="{{ route('lecturers.show', $course->lecturer) }}" class="course-lecturer-photo-link">
                            <img src="{{ $course->lecturer->profile_photo_url }}" alt="{{ $course->lecturer->name }}" class="course-lecturer-photo">
                        </a>
                    @endif

                    <div class="course-lecturer-pricing-details">
                        @if ($course->lecturer)
                            <a href="{{ route('lecturers.show', $course->lecturer) }}" class="course-lecturer-detail-link">
                                <span class="eyebrow">Lecturer</span>
                                <strong>{{ $course->lecturer->name }}</strong>
                                <span>{{ $course->lecturer->lecturer_headline ?: 'Meet your lecturer' }}</span>
                                @if (filled($course->lecturer->lecturer_bio))
                                    <p class="muted">{{ \Illuminate\Support\Str::limit($course->lecturer->lecturer_bio, 120) }}</p>
                                @endif
                            </a>
                        @endif

                        <article class="course-side-pricing">
                            <span class="eyebrow">Course pricing</span>
                            @if ($course->pricing_type === 'free')
                                <strong class="course-price-main">Free</strong>
                                <p class="muted">No payment required to enroll.</p>
                            @else
                                <strong class="course-price-main">{{ $course->pricing_currency }} {{ number_format((float) $course->pricing_amount, 2) }}</strong>
                                <p class="muted">
                                    @if ($course->pricing_type === 'once_off')
                                        Once-off payment
                                    @elseif ($course->pricing_type === 'per_lesson')
                                        Charged per lesson
                                    @elseif ($course->pricing_type === 'hourly')
                                        Charged per hour
                                    @endif
                                </p>
                            @endif
                        </article>
                    </div>
                </div>

                @auth
                    @if (! $isEnrolled)
                        @if ($course->pricing_type === 'free')
                            <form method="POST" action="{{ route('enrollments.store') }}">
                                @csrf
                                <input type="hidden" name="course_id" value="{{ $course->id }}">
                                <button type="submit" class="button button-primary">Enroll now</button>
                            </form>
                        @else
                            <a href="{{ route('payments.checkout', $course->id) }}" class="button button-primary">Pay & enroll</a>
                        @endif
                    @else
                        <span class="enrolled-badge">Enrolled</span>
                        @if (filled($meetingLink))
                            <a href="{{ $meetingLink }}" target="_blank" rel="noopener noreferrer" class="button button-secondary">Join live session</a>
                        @endif
                    @endif
                @else
                    <p class="muted">Sign in as a student to save progress and mark lessons complete.</p>
                @endauth
            </div>
        </article>

        <section class="panel course-detail-overview">
            <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-2 md:gap-10">
                <div class="w-full">
                    @if ($coverImage)
                        <img src="{{ $coverImage }}" alt="{{ $course->title }} cover image" class="course-detail-image">
                    @else
                        <div class="course-detail-image course-card-image-fallback" aria-hidden="true"></div>
                    @endif
                </div>

                <div class="w-full">
                    <div class="course-requirements-grid">
                        <div class="course-requirements-card pb-3">
                            <h3>Knowledge needed</h3>
                            @if (filled($course->knowledge_prerequisites))
                                <ul class="course-requirements-list">
                                    @foreach ($course->knowledge_prerequisites as $knowledgeRequirement)
                                        <li>{{ $knowledgeRequirement }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="muted">No knowledge prerequisites have been added yet.</p>
                            @endif
                        </div>
                    
                        <div class="course-requirements-card pb-3">
                            <h3>Equipment</h3>
                            @if (filled($course->equipment_requirements))
                                <ul class="course-requirements-list">
                                    @foreach ($course->equipment_requirements as $equipmentRequirement)
                                        <li>
                                            <strong>{{ $equipmentRequirement['name'] ?? 'Equipment' }}</strong>
                                            @if (filled($equipmentRequirement['url'] ?? null))
                                                <a href="{{ $equipmentRequirement['url'] }}" target="_blank" rel="noopener noreferrer">Buy link</a>
                                            @endif
                                            @if (filled($equipmentRequirement['notes'] ?? null))
                                                <div class="muted">{{ $equipmentRequirement['notes'] }}</div>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="muted">No equipment requirements have been added yet.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="section-heading">
                <div>
                    <h2>Ask about this course</h2>
                    <p class="muted">Send a course query before registering, or ask for more detail as a registered student.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('queries.course-queries.store', $course) }}" class="stack">
                @csrf

                @guest
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label>
                            <span class="auth-label">Name</span>
                            <input type="text" name="name" class="auth-input" value="{{ old('name') }}" required>
                            @error('name') <span class="auth-error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            <span class="auth-label">Email</span>
                            <input type="email" name="email" class="auth-input" value="{{ old('email') }}" required>
                            @error('email') <span class="auth-error">{{ $message }}</span> @enderror
                        </label>
                    </div>
                @else
                    <p class="muted">Logged as {{ auth()->user()->name }} using {{ auth()->user()->email }}.</p>
                @endguest

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <label>
                        <span class="auth-label">Mobile (optional)</span>
                        <input type="text" name="mobile" class="auth-input" value="{{ old('mobile', auth()->user()?->mobile) }}">
                        @error('mobile') <span class="auth-error">{{ $message }}</span> @enderror
                    </label>

                    <label>
                        <span class="auth-label">Subject (optional)</span>
                        <input type="text" name="subject" class="auth-input" value="{{ old('subject') }}" placeholder="Pricing, schedule, prerequisites...">
                        @error('subject') <span class="auth-error">{{ $message }}</span> @enderror
                    </label>
                </div>

                <label>
                    <span class="auth-label">Question</span>
                    <textarea name="message" class="auth-input" rows="5" required>{{ old('message') }}</textarea>
                    @error('message') <span class="auth-error">{{ $message }}</span> @enderror
                </label>

                <button type="submit" class="button button-primary">Log query</button>
            </form>

            @auth
                @if ($myCourseQueries->isNotEmpty())
                    <div class="stack">
                        <h3>Your recent course queries</h3>
                        @foreach ($myCourseQueries as $query)
                            <article class="course-requirements-card">
                                <div class="course-meta">
                                    <span class="pill">{{ $query->status }}</span>
                                    <span class="pill pill-muted">{{ $query->created_at->diffForHumans() }}</span>
                                </div>
                                <strong>{{ $query->subject ?: 'Course query' }}</strong>
                                <p class="muted">{{ $query->message }}</p>
                            </article>
                        @endforeach
                    </div>
                @endif
            @endauth
        </section>

        <section class="panel">
            <div class="section-heading">
                <div>
                    <h2>Course outline</h2>
                    <p class="muted">Preview lessons are open to everyone. Enroll to unlock the full course.</p>
                </div>
            </div>

            <div class="course-sections">
                @forelse ($sections as $section)
                    <article class="course-section-card">
                        <div class="course-section-header">
                            <div>
                                <span class="course-section-index">Section {{ $section->sequence }}</span>
                                <h3>{{ $section->title }}</h3>
                                @if ($section->description)
                                    <p class="muted">{{ $section->description }}</p>
                                @endif
                            </div>
                            <span class="pill pill-muted">{{ $section->lessons->count() }} lessons</span>
                        </div>

                        <div class="lesson-list">
                            @foreach ($section->lessons as $lesson)
                                @php
                                    $canOpenLesson = $lesson->is_preview || $isEnrolled || $canManageLessons;
                                @endphp

                                @if ($canOpenLesson)
                                    <a href="{{ route('lessons.show', [$course->slug, $lesson->slug]) }}" class="lesson-row">
                                        <span class="lesson-seq">{{ sprintf('%02d', $lesson->sequence) }}</span>
                                        <div>
                                            <strong>{{ $lesson->title }}</strong>
                                            <p>{{ $lesson->content_type->value }}{{ $lesson->is_preview ? ' - preview' : '' }}</p>
                                        </div>
                                    </a>
                                @else
                                    <div class="lesson-row lesson-row-locked" aria-disabled="true">
                                        <span class="lesson-seq">{{ sprintf('%02d', $lesson->sequence) }}</span>
                                        <div>
                                            <strong>{{ $lesson->title }}</strong>
                                            <p>{{ $lesson->content_type->value }} - enroll to unlock</p>
                                        </div>
                                        <span class="lesson-lock">Locked</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </article>
                @empty
                    <p class="muted">This course does not have any sections yet.</p>
                @endforelse
            </div>
        </section>

        @if ($canManageEnrollments)
            <section class="panel">
                <div class="section-heading">
                    <div>
                        <h2>Logged course queries</h2>
                        <p class="muted">Prospective and registered student questions for this course.</p>
                    </div>
                </div>

                @if ($courseQueries->isEmpty())
                    <p class="muted">No course queries have been logged yet.</p>
                @else
                    <div class="stack">
                        @foreach ($courseQueries as $query)
                            <article class="course-requirements-card">
                                <div class="course-meta">
                                    <span class="pill">{{ str_replace('_', ' ', $query->audience) }}</span>
                                    <span class="pill pill-muted">{{ $query->status }}</span>
                                    <span class="pill pill-muted">{{ $query->created_at->diffForHumans() }}</span>
                                </div>
                                <strong>{{ $query->subject ?: 'Course query' }}</strong>
                                <p>{{ $query->message }}</p>
                                <p class="muted">{{ $query->name }} - {{ $query->email }}{{ $query->mobile ? ' - '.$query->mobile : '' }}</p>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="panel">
                <div class="section-heading">
                    <div>
                        <h2>Student meeting links</h2>
                        <p class="muted">Set a student-specific meeting link. If left blank, students use the course default link.</p>
                    </div>
                </div>

                @if ($enrolledStudents->isEmpty())
                    <p class="muted">No enrolled students yet.</p>
                @else
                    <div class="stack">
                        @foreach ($enrolledStudents as $enrollmentRecord)
                            <article class="panel" style="margin: 0;">
                                <div>
                                    <strong>{{ $enrollmentRecord->user?->name ?? 'Student' }}</strong>
                                    <p class="muted">{{ $enrollmentRecord->user?->email }}</p>
                                </div>
                                <form method="POST" action="{{ route('enrollments.meeting-link', $enrollmentRecord->id) }}" class="stack">
                                    @csrf
                                    <label>
                                        <span class="auth-label">Meeting link override (optional)</span>
                                        <input
                                            type="url"
                                            name="meeting_url"
                                            class="auth-input"
                                            value="{{ $enrollmentRecord->meeting_url }}"
                                            placeholder="https://meet.google.com/... or https://teams.microsoft.com/..."
                                        >
                                    </label>
                                    <button type="submit" class="button button-secondary">Save meeting link</button>
                                </form>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif
    </section>
@endsection
