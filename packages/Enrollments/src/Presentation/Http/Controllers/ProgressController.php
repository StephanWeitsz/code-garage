<?php

namespace CodeGarage\Enrollments\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use CodeGarage\Enrollments\Application\Services\EnrollmentService;

class ProgressController extends Controller
{
    public function __invoke(Request $request, EnrollmentService $enrollments): View
    {
        abort_unless($request->user()?->hasRole('student'), 403);

        $progress = $enrollments->progressForUser($request->user()->id);

        return view('enrollments::progress', [
            'overview' => $progress['overview'],
            'courses' => $progress['courses'],
        ]);
    }
}
