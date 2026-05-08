@extends('layouts.app', ['title' => 'Courses'])

@section('content')
    <section class="stack">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Course catalog</p>
                <h1>Published courses</h1>
            </div>
        </div>

        <div class="card-list">
            @foreach ($courses as $course)
                @php
                    $coverImage = null;
                    if (filled($course->cover_image)) {
                        $coverImage = str_starts_with($course->cover_image, 'http://') || str_starts_with($course->cover_image, 'https://')
                            ? $course->cover_image
                            : url('/storage/'.ltrim($course->cover_image, '/'));
                    }
                @endphp
                <article class="course-card course-card-catalog">
                    <div class="course-meta">
                        <span class="pill">{{ $course->difficulty_level->value }}</span>
                        <span class="pill pill-muted">{{ $course->category }}</span>
                    </div>
                    <strong class="course-card-title">{{ $course->title }}</strong>
                    @if ($coverImage)
                        <img src="{{ $coverImage }}" alt="{{ $course->title }} cover image" class="course-card-image">
                    @else
                        <div class="course-card-image course-card-image-fallback" aria-hidden="true"></div>
                    @endif
                    <p>{{ \Illuminate\Support\Str::limit($course->description, 100) }}</p>
                    <a href="{{ route('courses.show', $course->slug) }}" class="button button-primary">Open course</a>
                </article>
            @endforeach
        </div>
    </section>
@endsection
