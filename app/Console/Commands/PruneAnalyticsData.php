<?php

namespace App\Console\Commands;

use App\Models\PageVisit;
use App\Models\VisitorSession;
use Illuminate\Console\Command;

class PruneAnalyticsData extends Command
{
    protected $signature = 'analytics:prune {--days= : Override configured retention days}';

    protected $description = 'Remove analytics data older than the configured retention period.';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('analytics.retention_days', 180));
        $cutoff = now()->subDays($days);

        PageVisit::query()
            ->where('visited_at', '<', $cutoff)
            ->delete();

        VisitorSession::query()
            ->where('last_seen_at', '<', $cutoff)
            ->doesntHave('pageVisits')
            ->delete();

        $this->info("Analytics data older than {$days} days was pruned.");

        return self::SUCCESS;
    }
}
