@extends('layouts.app', ['title' => 'Posts & Discussions'])

@section('content')
    <section class="stack">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Community</p>
                <h1>Posts & Discussions</h1>
            </div>
        </div>

        @if ($canCreateAnnouncement)
            <article class="panel stack">
                <h2>Create announcement or discussion</h2>
                <form method="POST" action="{{ route('posts.store') }}" class="stack">
                    @csrf
                    <div>
                        <label for="lesson_id">Lesson</label>
                        <select id="lesson_id" name="lesson_id" class="auth-input">
                            <option value="">All users (no lesson)</option>
                            @foreach ($lessons as $lessonId => $label)
                                <option value="{{ $lessonId }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="muted">Discussions require a lesson. Announcements can target all users or a specific lesson.</p>
                    </div>
                    <div>
                        <label for="title">Title</label>
                        <input id="title" name="title" type="text" class="auth-input" required>
                    </div>
                    <div>
                        <label for="body">Message</label>
                        <textarea id="body" name="body" rows="4" class="auth-input" required></textarea>
                    </div>
                    <div>
                        <label for="type">Type</label>
                        <select id="type" name="type" class="auth-input">
                            <option value="discussion">Discussion</option>
                            <option value="announcement">Announcement</option>
                        </select>
                    </div>
                    <button type="submit" class="button button-primary">Publish post</button>
                </form>
            </article>
        @endif

        @if ($canCreateAbsence)
            <article class="panel stack">
                <h2>Submit absence notice</h2>
                <form method="POST" action="{{ route('posts.store') }}" class="stack">
                    @csrf
                    <input type="hidden" name="type" value="absence_notice">
                    <div>
                        <label for="absence-lesson-id">Lesson you will miss</label>
                        <select id="absence-lesson-id" name="lesson_id" class="auth-input" required>
                            <option value="">Select lesson</option>
                            @foreach ($lessons as $lessonId => $label)
                                <option value="{{ $lessonId }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="absence-reason">Reason</label>
                        <textarea id="absence-reason" name="body" rows="4" class="auth-input" placeholder="I cannot attend because..." required></textarea>
                    </div>
                    <button type="submit" class="button button-primary">Send notice</button>
                </form>
            </article>
        @endif

        <article class="panel stack">
            <h2>Filter Threads</h2>
            <div class="hero-actions">
                <a href="{{ route('posts.index', array_filter(['lesson_id' => $selectedLessonId])) }}" class="button {{ $selectedType === '' ? 'button-primary' : 'button-secondary' }}">All</a>
                <a href="{{ route('posts.index', array_filter(['type' => 'announcement', 'lesson_id' => $selectedLessonId])) }}" class="button {{ $selectedType === 'announcement' ? 'button-primary' : 'button-secondary' }}">Announcements</a>
                <a href="{{ route('posts.index', array_filter(['type' => 'discussion', 'lesson_id' => $selectedLessonId])) }}" class="button {{ $selectedType === 'discussion' ? 'button-primary' : 'button-secondary' }}">Discussions</a>
                <a href="{{ route('posts.index', array_filter(['type' => 'absence_notice', 'lesson_id' => $selectedLessonId])) }}" class="button {{ $selectedType === 'absence_notice' ? 'button-primary' : 'button-secondary' }}">Absence Notices</a>
            </div>

            <form method="GET" action="{{ route('posts.index') }}" class="grid-two">
                @if ($selectedType)
                    <input type="hidden" name="type" value="{{ $selectedType }}">
                @endif
                <div>
                    <label for="filter-lesson-id">Filter by lesson</label>
                    <select id="filter-lesson-id" name="lesson_id" class="auth-input">
                        <option value="">All lessons</option>
                        @foreach ($lessons as $lessonId => $label)
                            <option value="{{ $lessonId }}" @selected((string) $selectedLessonId === (string) $lessonId)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="hero-actions">
                    <button type="submit" class="button button-secondary">Apply filter</button>
                    <a href="{{ route('posts.index', array_filter(['type' => $selectedType])) }}" class="button button-secondary">Reset lesson</a>
                </div>
            </form>
        </article>

        <div class="card-list">
            @forelse ($posts as $post)
                <article class="course-card">
                    <span class="pill">{{ str($post->type)->replace('_', ' ')->title() }}</span>
                    <strong>{{ $post->title }}</strong>
                    <p class="muted">Status: {{ str($post->status)->replace('_', ' ')->title() }}</p>
                    <p>{{ $post->course?->title ?? 'All courses' }} • {{ $post->lesson?->title ?? 'All users' }} • by {{ $post->author->name }}</p>
                    <p>{{ \Illuminate\Support\Str::limit($post->body, 120) }}</p>
                    <a href="{{ route('posts.show', $post->id) }}" class="button button-secondary">Open thread</a>
                </article>
            @empty
                <p class="muted">No posts yet.</p>
            @endforelse
        </div>

        {{ $posts->links() }}
    </section>
@endsection
