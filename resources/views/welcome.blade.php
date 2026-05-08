@extends('layouts.app', ['title' => 'Code Garage'])

@section('content')
    <section class="hero-grid" style="align-items: flex-start;">
        <div>
            <p class="eyebrow">Learn and build</p>
            <h1>Code Garage helps people learn to code and shape software ideas into real projects.</h1>
            <p class="hero-copy">
                Choose the learning center for structured coding journeys, or use the services portal to submit
                development requirements for quoting and follow-up.
            </p>
            <div class="hero-actions">
                <a href="{{ route('courses.index') }}" class="button button-primary">Explore learning</a>
                <a href="{{ route('development-requests.services.index') }}" class="button button-secondary">View services</a>
                <a href="{{ route('dashboard') }}" class="button button-secondary">Open dashboard</a>
            </div>
        </div>

        <div class="hero-panel">
            @if ($featuredLecturer)
                <article class="lecturer-spotlight panel">
                    <p class="eyebrow">Featured lecturer</p>
                    <a href="{{ route('lecturers.show', $featuredLecturer) }}" class="lecturer-card-link">
                        <div class="lecturer-identity">
                            <img
                                src="{{ $featuredLecturer->profile_photo_url }}"
                                alt="{{ $featuredLecturer->name }}"
                                class="lecturer-avatar"
                            >
                            <div>
                                <h2>{{ $featuredLecturer->name }}</h2>
                                <p class="lecturer-headline">
                                    {{ $featuredLecturer->lecturer_headline ?: 'Guiding students through practical coding foundations.' }}
                                </p>
                            </div>
                        </div>
                    </a>

                    @if ($featuredLecturer->lecturer_bio)
                        <div class="lecturer-bio">{!! nl2br(e($featuredLecturer->lecturer_bio)) !!}</div>
                    @endif

                    @if ($featuredLecturer->lecturerSpecialtiesList())
                        <div class="course-meta">
                            @foreach ($featuredLecturer->lecturerSpecialtiesList() as $specialty)
                                <span class="pill">{{ $specialty }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="lecturer-course-preview">
                        <div class="section-heading">
                            <div>
                                <p class="eyebrow">Courses by this lecturer</p>
                                <h3>{{ $featuredLecturer->published_courses_count }} published course{{ $featuredLecturer->published_courses_count === 1 ? '' : 's' }}</h3>
                            </div>
                            <a href="{{ route('lecturers.show', $featuredLecturer) }}" class="auth-link">View full profile</a>
                        </div>

                        <div class="lecturer-course-list">
                            @forelse ($featuredLecturer->taughtCourses->take(3) as $course)
                                <a href="{{ route('courses.show', $course->slug) }}" class="lecturer-course-item">
                                    <strong>{{ $course->title }}</strong>
                                    <span>{{ $course->category }} � {{ $course->difficulty_level->value }}</span>
                                </a>
                            @empty
                                <p class="muted">This lecturer profile is ready. Their published courses will appear here once added.</p>
                            @endforelse
                        </div>
                    </div>
                </article>
            @else
                <article class="panel">
                    <p class="eyebrow">Meet the team</p>
                    <h2>Lecturer profiles are coming online.</h2>
                    <p class="hero-copy">As soon as lecturers complete their public profiles and publish courses, students will be able to explore them here.</p>
                </article>
            @endif
        </div>
    </section>
@endsection
