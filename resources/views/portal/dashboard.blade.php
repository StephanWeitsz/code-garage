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

        <section class="stack">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Community pulse</p>
                    <h2>Active Discussions, Ads & Events</h2>
                </div>
                <a href="{{ route('posts.index') }}" class="button button-secondary">Open discussions</a>
            </div>

            @if ($activeAds->isNotEmpty())
                <div class="ad-card-grid">
                    @foreach ($activeAds as $ad)
                        <article class="ad-card">
                            @if ($ad->imageUrl())
                                <img src="{{ $ad->imageUrl() }}" alt="{{ $ad->title }}" class="ad-card-image">
                            @endif
                            <div class="ad-card-body">
                                <span class="pill">Ad</span>
                                <h3>{{ $ad->title }}</h3>
                                <p>{{ \Illuminate\Support\Str::limit($ad->body, 130) }}</p>
                                <div class="hero-actions">
                                    <a href="{{ route('posts.show', $ad->id) }}" class="button button-secondary">View details</a>
                                    @if ($ad->cta_url)
                                        <a href="{{ $ad->cta_url }}" class="button button-primary">{{ $ad->cta_label ?: 'Learn more' }}</a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="grid-two">
                <article class="panel">
                    <h2>Latest Discussions</h2>
                    <div class="card-list card-list-compact">
                        @forelse ($activeDiscussions as $post)
                            <a href="{{ route('posts.show', $post->id) }}" class="course-card">
                                <span class="pill">{{ str($post->type)->replace('_', ' ')->title() }}</span>
                                <strong>{{ $post->title }}</strong>
                                <p>{{ $post->course?->title ?? 'All courses' }} • {{ $post->author->name }}</p>
                            </a>
                        @empty
                            <p class="muted">No active discussions yet.</p>
                        @endforelse
                    </div>
                </article>

                <article class="panel">
                    <h2>Upcoming Events</h2>
                    <div class="card-list card-list-compact">
                        @forelse ($activeEvents as $event)
                            <a href="{{ route('events.show', $event->slug) }}" class="course-card">
                                <span class="pill">{{ $event->typeLabel() }}</span>
                                <strong>{{ $event->title }}</strong>
                                <p>{{ $event->starts_at->format('M j, Y H:i') }} • {{ $event->location ?: ($event->is_online ? 'Online' : 'Venue TBA') }}</p>
                            </a>
                        @empty
                            <p class="muted">No upcoming events published yet.</p>
                        @endforelse
                    </div>
                </article>
            </div>
        </section>
    </section>
@endsection
