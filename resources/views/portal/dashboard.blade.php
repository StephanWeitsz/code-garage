@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
    <section class="stack">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Learning cockpit</p>
                <h1>Dashboard</h1>
            </div>
        </div>

        <div class="grid-two">
            <article class="panel">
                <h2>Featured Courses</h2>
                <div class="dashboard-featured-course-list">
                    @foreach ($featuredCourses as $course)
                        <a href="{{ route('courses.show', $course->slug) }}" class="course-card">
                            <span class="pill">{{ $course->difficulty_level->value }}</span>
                            <strong>{{ $course->title }}</strong>
                            <p class="dashboard-course-description">{{ \Illuminate\Support\Str::limit($course->description, 150) }}</p>
                        </a>
                    @endforeach
                </div>
            </article>

            <article class="panel">
                <h2>My Learning</h2>
                @auth
                    <div class="card-list">
                        @forelse ($myCourses as $enrollment)
                            <a href="{{ route('courses.show', $enrollment->course->slug) }}" class="course-card">
                                <span class="pill">{{ $enrollment->status->value }}</span>
                                <strong>{{ $enrollment->course->title }}</strong>
                                <p>Enrolled {{ $enrollment->enrolled_at->diffForHumans() }}</p>
                            </a>
                        @empty
                            <p class="muted">No active enrollments yet. Start with a published course.</p>
                        @endforelse
                    </div>
                @else
                    <p class="muted">Sign in to track your learning journey and completed lessons.</p>
                @endauth
            </article>
        </div>
    </section>
@endsection
