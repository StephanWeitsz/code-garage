<?php

namespace App\Jobs;

use App\Models\CourseView;
use App\Models\PageVisit;
use App\Models\VisitorSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Detection\MobileDetect;
use Throwable;

class TrackPageVisitJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $timeout = 10;

    public function __construct(public array $payload)
    {
        $this->onQueue('analytics');
    }

    public function handle(): void
    {
        try {
            $agent = new MobileDetect();
            $agent->setUserAgent(Arr::get($this->payload, 'user_agent'));

            $visitedAt = now();
            $session = VisitorSession::query()->updateOrCreate(
                ['session_id' => $this->payload['session_id']],
                [
                    'user_id' => $this->payload['user_id'],
                    'ip_address' => $this->payload['ip_address'],
                    'user_agent' => $this->payload['user_agent'],
                    'referrer' => $this->payload['referrer'],
                    'device_type' => $this->deviceType($agent),
                    'browser' => $this->browser(),
                    'platform' => $this->platform(),
                    'first_seen_at' => VisitorSession::query()
                        ->where('session_id', $this->payload['session_id'])
                        ->value('first_seen_at') ?? $visitedAt,
                    'last_seen_at' => $visitedAt,
                ],
            );

            PageVisit::query()->create([
                'visitor_session_id' => $session->id,
                'user_id' => $this->payload['user_id'],
                'url' => $this->payload['url'],
                'request_host' => $this->payload['request_host'] ?? null,
                'route_name' => $this->payload['route_name'],
                'page_title' => $this->payload['page_title'],
                'method' => $this->payload['method'],
                'visited_at' => $visitedAt,
                'response_time' => $this->payload['response_time'],
                'is_suspicious' => $this->payload['is_suspicious'] ?? false,
                'risk_level' => $this->payload['risk_level'] ?? 'normal',
                'risk_reason' => $this->payload['risk_reason'] ?? null,
            ]);

            $this->trackCourseView($session, $visitedAt);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function deviceType(MobileDetect $agent): string
    {
        return match (true) {
            $agent->isTablet() => 'tablet',
            $agent->isMobile() => 'mobile',
            default => 'desktop',
        };
    }

    private function browser(): ?string
    {
        $userAgent = (string) Arr::get($this->payload, 'user_agent', '');

        return match (true) {
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'OPR/') || str_contains($userAgent, 'Opera') => 'Opera',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Chrome/') => 'Chrome',
            str_contains($userAgent, 'Safari/') => 'Safari',
            default => null,
        };
    }

    private function platform(): ?string
    {
        $userAgent = (string) Arr::get($this->payload, 'user_agent', '');

        return match (true) {
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Mac OS X') || str_contains($userAgent, 'Macintosh') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => null,
        };
    }

    private function trackCourseView(VisitorSession $session, mixed $occurredAt): void
    {
        $course = Arr::get($this->payload, 'course');

        if (! is_array($course)) {
            return;
        }

        CourseView::query()->create([
            'visitor_session_id' => $session->id,
            'user_id' => $this->payload['user_id'],
            'course_id' => Arr::get($course, 'id'),
            'course_slug' => Arr::get($course, 'slug'),
            'course_title' => Arr::get($course, 'title'),
            'event_type' => Arr::get($course, 'event_type', CourseView::EVENT_VIEW),
            'occurred_at' => $occurredAt,
        ]);
    }
}
