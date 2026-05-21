<div class="space-y-6 p-6">
    <div>
        <a href="{{ route('admin.analytics.dashboard') }}" class="text-sm font-medium text-blue-600">Back to analytics</a>
        <h1 class="mt-2 text-2xl font-semibold text-white">Visitor Journey</h1>
        <p class="text-sm text-gray-500">
            {{ $visitorSession->user?->name ?: 'Anonymous visitor' }} · Last seen {{ $visitorSession->last_seen_at?->diffForHumans() }}
        </p>
    </div>

    <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <div class="font-medium text-gray-500">IP</div>
                <div class="mt-1 text-gray-950">{{ $visitorSession->ip_address ?: 'Unknown' }}</div>
            </div>
            <div>
                <div class="font-medium text-gray-500">Browser</div>
                <div class="mt-1 text-gray-950">{{ $visitorSession->browser ?: 'Unknown' }}</div>
            </div>
            <div>
                <div class="font-medium text-gray-500">Platform</div>
                <div class="mt-1 text-gray-950">{{ $visitorSession->platform ?: 'Unknown' }}</div>
            </div>
            <div>
                <div class="font-medium text-gray-500">Device</div>
                <div class="mt-1 text-gray-950">{{ $visitorSession->device_type ?: 'Unknown' }}</div>
            </div>
        </div>
    </section>

    <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 p-4">
            <h2 class="text-base font-semibold text-gray-950">Journey</h2>
        </div>
        <ol class="divide-y divide-gray-100">
            @foreach ($journey as $visit)
                <li class="flex gap-4 p-4">
                    <div class="mt-1 h-3 w-3 rounded-full bg-blue-600"></div>
                    <div class="min-w-0 flex-1">
                        <div class="truncate font-medium text-gray-950">
                            {{ $visit->page_title ?: $visit->route_name ?: $visit->url }}
                        </div>
                        <div class="mt-1 truncate text-sm text-gray-500">{{ $visit->url }}</div>
                    </div>
                    <time class="shrink-0 text-sm text-gray-500">{{ $visit->visited_at->format('M j, H:i') }}</time>
                </li>
            @endforeach
        </ol>
    </section>
</div>
