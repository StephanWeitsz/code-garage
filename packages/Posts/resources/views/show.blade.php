@extends('layouts.app', ['title' => $post->title])

@section('content')
    <section class="stack">
        <div class="hero-actions">
            <a href="{{ $isPublicAd ?? false ? route('welcome') : route('posts.index') }}" class="button button-secondary">Back</a>
        </div>

        <article class="panel stack">
            <span class="pill">{{ str($post->type)->replace('_', ' ')->title() }}</span>
            <h1>{{ $post->title }}</h1>
            <p class="muted">
                {{ $post->course?->title ?? 'All courses' }} • {{ $post->lesson?->title ?? 'All users' }} •
                Posted by {{ $post->author->name }} {{ $post->created_at->diffForHumans() }}
            </p>
            <p class="muted">Status: {{ str($post->status)->replace('_', ' ')->title() }}</p>

            @if ($post->imageUrl())
                <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" class="{{ $post->isAd() ? 'ad-detail-image' : 'post-hero-image' }}">
            @endif

            @if ($canManageLifecycle)
                <div class="hero-actions">
                    @if ($post->status === 'published')
                        <form method="POST" action="{{ route('posts.close', $post->id) }}">
                            @csrf
                            <button type="submit" class="button button-secondary">Close discussion</button>
                        </form>
                        <form method="POST" action="{{ route('posts.archive', $post->id) }}">
                            @csrf
                            <button type="submit" class="button button-secondary">Archive discussion</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('posts.reopen', $post->id) }}">
                            @csrf
                            <button type="submit" class="button button-secondary">Reopen discussion</button>
                        </form>
                    @endif
                </div>
            @endif
            <div class="course-description">{!! nl2br(e($post->body)) !!}</div>

            @if ($post->isAd() && $post->cta_url)
                <div class="hero-actions">
                    <a href="{{ $post->cta_url }}" class="button button-primary">{{ $post->cta_label ?: 'Learn more' }}</a>
                </div>
            @endif
        </article>

        @unless ($post->isAd())
        <article class="panel stack">
            <h2>Replies</h2>
            @if ($isReplyLocked)
                <p class="muted">Replies are locked because this discussion is {{ $post->status }}.</p>
            @else
                <form method="POST" action="{{ route('posts.reply', $post->id) }}" class="stack">
                    @csrf
                    <div>
                        <label for="body">Add reply</label>
                        <textarea id="body" name="body" rows="4" class="auth-input" required></textarea>
                    </div>
                    <button type="submit" class="button button-primary">Post reply</button>
                </form>
            @endif

            @forelse ($post->replies as $reply)
                <article class="course-requirements-card">
                    <strong>{{ $reply->author->name }}</strong>
                    <p class="muted">{{ $reply->created_at->diffForHumans() }}</p>
                    <p>{{ $reply->body }}</p>
                </article>
            @empty
                <p class="muted">No replies yet. Start the conversation.</p>
            @endforelse
        </article>
        @endunless
    </section>
@endsection
