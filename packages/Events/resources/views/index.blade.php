@extends('layouts.app', ['title' => 'Events'])

@section('content')
    <section class="stack">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Code Garage events</p>
                <h1>Upcoming events</h1>
                <p class="muted">Coding days, build sessions, workshops, and graduation moments for the Code Garage community.</p>
            </div>
        </div>

        @auth
            @if (auth()->user()->hasAnyRole(['admin', 'lecturer']))
                <section class="panel">
                    <div class="section-heading">
                        <div>
                            <h2>Add event</h2>
                            <p class="muted">Create draft or published events for future community sessions.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('events.store') }}" class="stack">
                        @csrf
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label>
                                <span class="auth-label">Title</span>
                                <input type="text" name="title" class="auth-input" value="{{ old('title') }}" required>
                                @error('title') <span class="auth-error">{{ $message }}</span> @enderror
                            </label>

                            <label>
                                <span class="auth-label">Type</span>
                                <select name="type" class="auth-input" required>
                                    <option value="coding_day" @selected(old('type') === 'coding_day')>Coding day</option>
                                    <option value="project_day" @selected(old('type') === 'project_day')>Project day</option>
                                    <option value="workshop" @selected(old('type') === 'workshop')>Workshop</option>
                                    <option value="graduation" @selected(old('type') === 'graduation')>Graduation</option>
                                </select>
                                @error('type') <span class="auth-error">{{ $message }}</span> @enderror
                            </label>
                        </div>

                        <label>
                            <span class="auth-label">Summary</span>
                            <textarea name="summary" class="auth-input" rows="3" required>{{ old('summary') }}</textarea>
                            @error('summary') <span class="auth-error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            <span class="auth-label">Description (optional)</span>
                            <textarea name="description" class="auth-input" rows="5">{{ old('description') }}</textarea>
                            @error('description') <span class="auth-error">{{ $message }}</span> @enderror
                        </label>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label>
                                <span class="auth-label">Starts at</span>
                                <input type="datetime-local" name="starts_at" class="auth-input" value="{{ old('starts_at') }}" required>
                                @error('starts_at') <span class="auth-error">{{ $message }}</span> @enderror
                            </label>

                            <label>
                                <span class="auth-label">Ends at (optional)</span>
                                <input type="datetime-local" name="ends_at" class="auth-input" value="{{ old('ends_at') }}">
                                @error('ends_at') <span class="auth-error">{{ $message }}</span> @enderror
                            </label>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label>
                                <span class="auth-label">Location</span>
                                <input type="text" name="location" class="auth-input" value="{{ old('location') }}" placeholder="Campus, classroom, venue, or online">
                                @error('location') <span class="auth-error">{{ $message }}</span> @enderror
                            </label>

                            <label>
                                <span class="auth-label">Capacity (optional)</span>
                                <input type="number" name="capacity" class="auth-input" min="1" value="{{ old('capacity') }}">
                                @error('capacity') <span class="auth-error">{{ $message }}</span> @enderror
                            </label>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label>
                                <span class="auth-label">Meeting URL (optional)</span>
                                <input type="url" name="meeting_url" class="auth-input" value="{{ old('meeting_url') }}">
                                @error('meeting_url') <span class="auth-error">{{ $message }}</span> @enderror
                            </label>

                            <label>
                                <span class="auth-label">Status</span>
                                <select name="status" class="auth-input" required>
                                    <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                                    <option value="published" @selected(old('status') === 'published')>Published</option>
                                </select>
                                @error('status') <span class="auth-error">{{ $message }}</span> @enderror
                            </label>
                        </div>

                        <label class="inline-flex items-center gap-3 text-sm text-slate-200">
                            <input type="checkbox" name="is_online" value="1" @checked(old('is_online'))>
                            Online event
                        </label>

                        <button type="submit" class="button button-primary">Save event</button>
                    </form>
                </section>
            @endif
        @endauth

        <div class="card-list">
            @forelse ($events as $event)
                <article class="course-card course-card-catalog">
                    <div class="course-meta">
                        <span class="pill">{{ $event->typeLabel() }}</span>
                        <span class="pill pill-muted">{{ $event->starts_at->format('d M Y') }}</span>
                    </div>
                    <strong class="course-card-title">{{ $event->title }}</strong>
                    <p>{{ \Illuminate\Support\Str::limit($event->summary, 130) }}</p>
                    <p class="muted">
                        {{ $event->starts_at->format('H:i') }}
                        @if ($event->ends_at)
                            - {{ $event->ends_at->format('H:i') }}
                        @endif
                        @if ($event->location)
                            · {{ $event->location }}
                        @elseif ($event->is_online)
                            · Online
                        @endif
                    </p>
                    <a href="{{ route('events.show', $event->slug) }}" class="button button-primary">Open event</a>
                </article>
            @empty
                <section class="panel">
                    <h2>No upcoming events yet</h2>
                    <p class="muted">Published coding days, project sessions, workshops, and graduations will appear here.</p>
                </section>
            @endforelse
        </div>

        @if ($pastEvents->isNotEmpty())
            <section class="panel">
                <div class="section-heading">
                    <div>
                        <h2>Past events</h2>
                        <p class="muted">Recent community moments from Code Garage.</p>
                    </div>
                </div>

                <div class="stack">
                    @foreach ($pastEvents as $event)
                        <article class="course-requirements-card">
                            <div class="course-meta">
                                <span class="pill">{{ $event->typeLabel() }}</span>
                                <span class="pill pill-muted">{{ $event->starts_at->format('d M Y') }}</span>
                            </div>
                            <strong>{{ $event->title }}</strong>
                            <p class="muted">{{ $event->summary }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </section>
@endsection
