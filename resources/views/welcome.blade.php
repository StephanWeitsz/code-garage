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

        <div class="hero-panel stack">
            @if ($activeAds->isNotEmpty())
                <div class="ad-card-grid ad-card-grid-single">
                    @foreach ($activeAds as $ad)
                        <article class="ad-card">
                            @if ($ad->imageUrl())
                                <img src="{{ $ad->imageUrl() }}" alt="{{ $ad->title }}" class="ad-card-image">
                            @endif
                            <div class="ad-card-body">
                                <span class="pill">Ad</span>
                                <h2>{{ $ad->title }}</h2>
                                <p>{{ \Illuminate\Support\Str::limit($ad->body, 150) }}</p>
                                <div class="hero-actions">
                                    <a href="{{ route('posts.public-ad', $ad->id) }}" class="button button-secondary">View details</a>
                                    @if ($ad->cta_url)
                                        <a href="{{ $ad->cta_url }}" class="button button-primary">{{ $ad->cta_label ?: 'Learn more' }}</a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            @if ($featuredLecturers->isNotEmpty())
                <article class="lecturer-spotlight panel">
                    <div class="section-heading">
                        <div>
                            <p class="eyebrow">Featured lecturers</p>
                            <h2>Meet the people guiding the work</h2>
                        </div>
                    </div>

                    <div class="lecturer-card-grid">
                        @foreach ($featuredLecturers as $lecturer)
                            <a href="{{ route('lecturers.show', $lecturer) }}" class="lecturer-card-link lecturer-feature-card">
                                <div class="lecturer-identity">
                                    <img
                                        src="{{ $lecturer->profile_photo_url }}"
                                        alt="{{ $lecturer->name }}"
                                        class="lecturer-avatar"
                                    >
                                    <div>
                                        <h3>{{ $lecturer->name }}</h3>
                                        <p class="lecturer-headline">
                                            {{ $lecturer->lecturer_headline ?: 'Guiding students through practical coding foundations.' }}
                                        </p>
                                    </div>
                                </div>

                                @if ($lecturer->lecturer_bio)
                                    <p class="lecturer-bio">{{ \Illuminate\Support\Str::limit($lecturer->lecturer_bio, 120) }}</p>
                                @endif

                                @if ($lecturer->lecturerSpecialtiesList())
                                    <div class="course-meta">
                                        @foreach (array_slice($lecturer->lecturerSpecialtiesList(), 0, 3) as $specialty)
                                            <span class="pill">{{ $specialty }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </a>
                        @endforeach
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
