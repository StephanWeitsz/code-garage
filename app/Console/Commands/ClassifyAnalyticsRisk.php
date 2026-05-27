<?php

namespace App\Console\Commands;

use App\Models\PageVisit;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ClassifyAnalyticsRisk extends Command
{
    protected $signature = 'analytics:classify-risk {--chunk=500 : Number of page visits to process per chunk}';

    protected $description = 'Backfill request host and risk classification for existing page visits.';

    public function handle(): int
    {
        $updated = 0;

        PageVisit::query()
            ->select(['id', 'url'])
            ->chunkById((int) $this->option('chunk'), function ($visits) use (&$updated): void {
                foreach ($visits as $visit) {
                    $classification = $this->classifyUrl($visit->url);

                    PageVisit::query()
                        ->whereKey($visit->id)
                        ->update($classification);

                    $updated++;
                }
            });

        $this->info("Classified {$updated} page visits.");

        return self::SUCCESS;
    }

    private function classifyUrl(string $url): array
    {
        $host = Str::lower((string) parse_url($url, PHP_URL_HOST));
        $query = (string) parse_url($url, PHP_URL_QUERY);
        $target = Str::lower($url);
        $reasons = [];

        if ($host !== '' && filter_var($host, FILTER_VALIDATE_IP)) {
            $reasons[] = 'direct_ip_host';
        }

        parse_str($query, $queryValues);

        foreach (config('analytics.high_risk_query_keys', []) as $key) {
            if (array_key_exists($key, $queryValues)) {
                $reasons[] = 'high_risk_query:'.$key;
            }
        }

        foreach (config('analytics.high_risk_patterns', []) as $pattern) {
            if (Str::contains($target, Str::lower($pattern))) {
                $reasons[] = 'high_risk_pattern:'.$pattern;
            }
        }

        $reasons = array_values(array_unique($reasons));

        return [
            'request_host' => $host ?: null,
            'is_suspicious' => count($reasons) > 0,
            'risk_level' => count($reasons) > 0 ? 'high' : 'normal',
            'risk_reason' => $reasons ? implode(', ', $reasons) : null,
        ];
    }
}
