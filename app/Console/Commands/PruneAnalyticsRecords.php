<?php

namespace App\Console\Commands;

use App\Models\CourseView;
use App\Models\PageVisit;
use App\Models\VisitorSession;
use Illuminate\Console\Command;

class PruneAnalyticsRecords extends Command
{
    protected $signature = 'analytics:prune-records {--days= : Override configured retention days}';

    protected $description = 'Remove old visitor analytics records, including course analytics.';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('analytics.retention_days', 180));
        $cutoff = now()->subDays($days);

        PageVisit::query()->where('visited_at', '<', $cutoff)->delete();
        CourseView::query()->where('occurred_at', '<', $cutoff)->delete();

        VisitorSession::query()
            ->where('last_seen_at', '<', $cutoff)
            ->doesntHave('pageVisits')
            ->doesntHave('courseViews')
            ->delete();

        $this->info("Analytics records older than {$days} days were pruned.");

        return self::SUCCESS;
    }
}
