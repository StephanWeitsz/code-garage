<?php

namespace App\Http\Controllers;

use App\Models\CourseView;
use App\Models\VisitorSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsCourseEventController
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => ['nullable', 'integer'],
            'course_slug' => ['nullable', 'string', 'max:255'],
            'course_title' => ['nullable', 'string', 'max:255'],
            'event_type' => ['required', 'in:enroll_click,registration_conversion'],
        ]);

        $session = VisitorSession::query()->where('session_id', $request->session()->getId())->first();

        if (! $session) {
            return response()->json(['tracked' => false]);
        }

        CourseView::query()->create([
            'visitor_session_id' => $session->id,
            'user_id' => $request->user()?->getKey(),
            'course_id' => $validated['course_id'] ?? null,
            'course_slug' => $validated['course_slug'] ?? null,
            'course_title' => $validated['course_title'] ?? null,
            'event_type' => $validated['event_type'],
            'occurred_at' => now(),
        ]);

        return response()->json(['tracked' => true]);
    }
}
