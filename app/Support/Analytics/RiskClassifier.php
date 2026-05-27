<?php

namespace App\Support\Analytics;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RiskClassifier
{
    public function classify(Request $request): array
    {
        $host = Str::lower($request->getHost());
        $target = Str::lower('/'.$request->path().'?'.$request->getQueryString());
        $reasons = [];

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $reasons[] = 'direct_ip_host';
        }

        foreach (config('analytics.high_risk_query_keys', []) as $key) {
            if ($request->query->has($key)) {
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
            'request_host' => $host,
            'is_suspicious' => count($reasons) > 0,
            'risk_level' => count($reasons) > 0 ? 'high' : 'normal',
            'risk_reason' => $reasons ? implode(', ', $reasons) : null,
        ];
    }
}
