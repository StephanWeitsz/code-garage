@extends('layouts.app', ['title' => $lecturer->name])

@section('content')
    <section class="stack">
        <article class="panel lecturer-profile-hero">
            <div class="lecturer-identity lecturer-identity-large">
                <img src="{{ $lecturer->profile_photo_url }}" alt="{{ $lecturer->name }}" class="lecturer-avatar lecturer-avatar-large">
                <div>
                    <p class="eyebrow">Lecturer profile</p>
                    <h1>{{ $lecturer->name }}</h1>
                    <p class="lecturer-headline lecturer-headline-large">
                        {{ $lecturer->lecturer_headline ?: 'Guiding students through practical coding foundations.' }}
                    </p>
                    @if ($lecturer->lecturerSpecialtiesList())
                        <div class="course-meta">
                            @foreach ($lecturer->lecturerSpecialtiesList() as $specialty)
                                <span class="pill">{{ $specialty }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid-two">
                <div>
                    <h2>About this lecturer</h2>
                    <div class="lecturer-bio lecturer-bio-large">{!! $lecturer->lecturer_bio ? nl2br(e($lecturer->lecturer_bio)) : e('This lecturer has not added a full bio yet, but their published courses are listed below.') !!}</div>
                </div>
                <div class="panel lecturer-mini-panel">
                    <h3>Teaching overview</h3>
                    <div class="stats-grid lecturer-stats-grid">
                        <div class="stat-card"><strong>{{ $courses->count() }}</strong><span>Published courses</span></div>
                        <div class="stat-card"><strong>{{ $lecturer->email }}</strong><span>Contact email</span></div>
                    </div>
                </div>
            </div>
        </article>

        <section class="panel">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Learning tracks</p>
                    <h2>Courses presented by {{ $lecturer->name }}</h2>
                </div>
            </div>

            <div class="card-list">
                @forelse ($courses as $course)
                    <article class="course-card">
                        <div class="course-meta">
                            <span class="pill">{{ $course->difficulty_level->value }}</span>
                            <span class="pill pill-muted">{{ $course->category }}</span>
                        </div>
                        <strong>{{ $course->title }}</strong>
                        <p>{{ $course->description }}</p>
                        <a href="{{ route('courses.show', $course->slug) }}" class="button button-primary">Open course</a>
                    </article>
                @empty
                    <p class="muted">No published courses yet.</p>
                @endforelse
            </div>
        </section>
    </section>
@endsection
