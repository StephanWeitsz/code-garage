<div class="space-y-6 p-6" wire:poll.15s="refreshDashboard">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-white">Visitor Analytics</h1>
            <p class="text-sm text-gray-500">Internal traffic, visitor activity, and course interest.</p>
        </div>
        <div class="text-sm text-gray-500">Updates every 15 seconds</div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            'Visitors Today' => $stats['visitors_today'] ?? 0,
            'Active Visitors' => $stats['active_visitors'] ?? 0,
            'Registered Today' => $stats['registered_users_today'] ?? 0,
            'Total Page Views' => $stats['total_page_views'] ?? 0,
            'Anonymous Visitors' => $stats['anonymous_visitors'] ?? 0,
        ] as $label => $value)
            <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-sm font-medium text-gray-500">{{ $label }}</div>
                <div class="mt-3 text-3xl font-semibold text-gray-950">{{ number_format($value) }}</div>
            </section>
        @endforeach
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="text-base font-semibold text-gray-950">Daily Visitors</h2>
            <div class="mt-4 h-72" wire:ignore><canvas id="dailyVisitorsChart" class="h-full w-full"></canvas></div>
        </section>
        <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="text-base font-semibold text-gray-950">Page Views Over Time</h2>
            <div class="mt-4 h-72" wire:ignore><canvas id="pageViewsChart" class="h-full w-full"></canvas></div>
        </section>
        <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="text-base font-semibold text-gray-950">Top Courses Viewed</h2>
            <div class="mt-4 h-72" wire:ignore><canvas id="topCoursesChart" class="h-full w-full"></canvas></div>
        </section>
        <section class="grid gap-4 md:grid-cols-2">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h2 class="text-base font-semibold text-gray-950">Device Breakdown</h2>
                <div class="mt-4 h-64" wire:ignore><canvas id="deviceChart" class="h-full w-full"></canvas></div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h2 class="text-base font-semibold text-gray-950">Browser Breakdown</h2>
                <div class="mt-4 h-64" wire:ignore><canvas id="browserChart" class="h-full w-full"></canvas></div>
            </div>
        </section>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <section class="rounded-lg border border-gray-200 bg-white shadow-sm xl:col-span-2">
            <div class="border-b border-gray-200 p-4">
                <h2 class="text-base font-semibold text-gray-950">Recent Visitors</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-800">
                        <tr>
                            <th class="px-4 py-3">IP</th>
                            <th class="px-4 py-3">Browser</th>
                            <th class="px-4 py-3">Platform</th>
                            <th class="px-4 py-3">Last Seen</th>
                            <th class="px-4 py-3">Current Page</th>
                            <th class="px-4 py-3">User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-500">
                        @foreach ($recentVisitors as $visitor)
                            <tr>
                                <td class="px-4 py-3">{{ $visitor->ip_address ?: 'Unknown' }}</td>
                                <td class="px-4 py-3">{{ $visitor->browser ?: 'Unknown' }}</td>
                                <td class="px-4 py-3">{{ $visitor->platform ?: 'Unknown' }}</td>
                                <td class="px-4 py-3">{{ $visitor->last_seen_at?->diffForHumans() }}</td>
                                <td class="max-w-xs truncate px-4 py-3">{{ $visitor->pageVisits->first()?->url ?: 'Unknown' }}</td>
                                <td class="px-4 py-3">
                                    @if ($visitor->user)
                                        {{ $visitor->user->name }}
                                    @else
                                        <span class="text-gray-400">Anonymous</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 p-4">
                <h2 class="text-base font-semibold text-gray-950">Live Page Activity</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ($activityFeed as $visit)
                    <div class="p-4 text-sm">
                        <div class="truncate font-medium text-gray-900">{{ $visit->url }}</div>
                        <div class="mt-1 text-gray-500">{{ $visit->visited_at->diffForHumans() }}</div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 p-4">
            <h2 class="text-base font-semibold text-gray-950">Most Visited Pages</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-800">
                    <tr>
                        <th class="px-4 py-3">URL</th>
                        <th class="px-4 py-3">Visits</th>
                        <th class="px-4 py-3">Unique Visitors</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-500">
                    @foreach ($mostVisitedPages as $page)
                        <tr>
                            <td class="max-w-3xl truncate px-4 py-3">{{ $page->url }}</td>
                            <td class="px-4 py-3">{{ number_format($page->visits) }}</td>
                            <td class="px-4 py-3">{{ number_format($page->unique_visitors) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const analyticsCharts = {};

            function upsertChart(id, type, labels, data, label) {
                const canvas = document.getElementById(id);
                if (!canvas || typeof Chart === 'undefined') return;

                if (analyticsCharts[id]) {
                    analyticsCharts[id].data.labels = labels;
                    analyticsCharts[id].data.datasets[0].data = data;
                    requestAnimationFrame(() => {
                        analyticsCharts[id].resize();
                        analyticsCharts[id].update('none');
                    });
                    return;
                }

                requestAnimationFrame(() => {
                    analyticsCharts[id] = new Chart(canvas, {
                        type,
                        data: {
                            labels,
                            datasets: [{
                                label,
                                data,
                                borderColor: '#2563eb',
                                backgroundColor: ['#2563eb', '#16a34a', '#f59e0b', '#dc2626', '#7c3aed', '#0891b2'],
                                tension: 0.35
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            resizeDelay: 100,
                            plugins: { legend: { display: type !== 'line' } },
                            scales: type === 'doughnut' ? {} : { y: { beginAtZero: true, ticks: { precision: 0 } } }
                        }
                    });
                });
            }

            document.addEventListener('livewire:init', () => {
                Livewire.on('analytics-updated', ({ charts }) => {
                    upsertChart('dailyVisitorsChart', 'line', charts.dailyVisitors.labels, charts.dailyVisitors.data, 'Visitors');
                    upsertChart('pageViewsChart', 'line', charts.pageViews.labels, charts.pageViews.data, 'Page views');
                    upsertChart('topCoursesChart', 'bar', charts.topCourses.labels, charts.topCourses.data, 'Views');
                    upsertChart('deviceChart', 'doughnut', charts.devices.labels, charts.devices.data, 'Devices');
                    upsertChart('browserChart', 'doughnut', charts.browsers.labels, charts.browsers.data, 'Browsers');
                });
            });
        </script>
    @endonce
</div>
