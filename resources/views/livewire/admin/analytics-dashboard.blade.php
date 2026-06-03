<div class="space-y-6 p-6" wire:poll.15s="refreshDashboard">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-white">Visitor Analytics</h1>
            <p class="text-sm text-gray-500">Internal traffic, visitor activity, and course interest.</p>
        </div>
        <div class="text-sm text-gray-500">Updates every 15 seconds</div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        @foreach ([
            'Visitors Today' => $stats['visitors_today'] ?? 0,
            'Active Visitors' => $stats['active_visitors'] ?? 0,
            'Registered Today' => $stats['registered_users_today'] ?? 0,
            'Total Page Views' => $stats['total_page_views'] ?? 0,
            'Anonymous Visitors' => $stats['anonymous_visitors'] ?? 0,
            'High Risk Today' => $stats['high_risk_today'] ?? 0,
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
        <div class="flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm xl:col-span-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-base font-semibold text-gray-950">Graph Range</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($chartRangeOptions as $range => $label)
                    <button
                        type="button"
                        wire:click="setChartRange('{{ $range }}')"
                        @class([
                            'rounded-md px-3 py-2 text-sm font-medium transition',
                            'bg-blue-200 text-gray-600 shadow-sm' => $chartRange === $range,
                            'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50' => $chartRange !== $range,
                        ])
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
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
                <table class="w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-800">
                        <tr>
                            <th class="px-4 py-3">IP</th>
                            <th class="px-4 py-3">Browser</th>
                            <th class="px-4 py-3">Platform</th>
                            <th class="px-4 py-3">Last Seen</th>
                            <th class="px-4 py-3">Current Page</th>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3"></th>
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
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.analytics.visitors.show', $visitor) }}"
                                       class="text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-eye"></i>
                                    </a>
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
            <p class="mt-1 text-sm text-gray-500">Suspicious scanner traffic is excluded from this table.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 text-sm">
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

    <div class="grid gap-4 xl:grid-cols-2">
        <section class="rounded-lg border border-red-200 bg-white shadow-sm">
            <div class="border-b border-red-100 p-4">
                <h2 class="text-base font-semibold text-red-700">High Risk Activity</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-red-50 text-left text-xs uppercase tracking-wide text-red-800">
                        <tr>
                            <th class="px-4 py-3">URL</th>
                            <th class="px-4 py-3">Host</th>
                            <th class="px-4 py-3">Reason</th>
                            <th class="px-4 py-3">Seen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-600">
                        @forelse ($highRiskVisits as $visit)
                            <tr>
                                <td class="max-w-xs truncate px-4 py-3">{{ $visit->url }}</td>
                                <td class="px-4 py-3">{{ $visit->request_host ?: 'Unknown' }}</td>
                                <td class="max-w-xs truncate px-4 py-3">{{ $visit->risk_reason ?: 'Suspicious request' }}</td>
                                <td class="px-4 py-3">{{ $visit->visited_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500">No high-risk activity recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-lg border border-red-200 bg-white shadow-sm">
            <div class="border-b border-red-100 p-4">
                <h2 class="text-base font-semibold text-red-700">Risky Hosts</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-red-50 text-left text-xs uppercase tracking-wide text-red-800">
                        <tr>
                            <th class="px-4 py-3">Host</th>
                            <th class="px-4 py-3">Attempts</th>
                            <th class="px-4 py-3">Unique Visitors</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-600">
                        @forelse ($riskyHosts as $host)
                            <tr>
                                <td class="px-4 py-3">{{ $host->request_host ?: 'Unknown' }}</td>
                                <td class="px-4 py-3">{{ number_format($host->attempts) }}</td>
                                <td class="px-4 py-3">{{ number_format($host->unique_visitors) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-500">No risky hosts recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

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
