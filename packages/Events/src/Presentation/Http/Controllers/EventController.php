<?php

namespace CodeGarage\Events\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use CodeGarage\Events\Infrastructure\Persistence\Eloquent\Models\Event;
use CodeGarage\Events\Presentation\Http\Requests\StoreEventRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class EventController extends Controller
{
    public function index(): View
    {
        return view('events::index', [
            'events' => Event::query()
                ->published()
                ->upcoming()
                ->orderBy('starts_at')
                ->get(),
            'pastEvents' => Event::query()
                ->published()
                ->where('starts_at', '<', now()->startOfDay())
                ->orderByDesc('starts_at')
                ->limit(6)
                ->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $event = Event::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('events::show', [
            'event' => $event,
        ]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = $request->user()->id;
        $validated['is_online'] = (bool) ($validated['is_online'] ?? false);

        $event = Event::query()->create($validated);

        if ($event->status === 'published') {
            return redirect()->route('events.show', $event->slug)
                ->with('status', 'Event saved.');
        }

        return redirect()->route('events.index')
            ->with('status', 'Draft event saved.');
    }
}
