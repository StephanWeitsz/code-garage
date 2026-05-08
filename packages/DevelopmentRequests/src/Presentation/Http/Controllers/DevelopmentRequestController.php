<?php

namespace CodeGarage\DevelopmentRequests\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use CodeGarage\DevelopmentRequests\Infrastructure\Persistence\Eloquent\Models\DevelopmentRequest;
use CodeGarage\DevelopmentRequests\Presentation\Http\Requests\StoreDevelopmentRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DevelopmentRequestController extends Controller
{
    public function index(): View
    {
        return view('development-requests::index');
    }

    public function create(): View
    {
        return view('development-requests::create');
    }

    public function store(StoreDevelopmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DevelopmentRequest::query()->create([
            ...$validated,
            'user_id' => $request->user()?->id,
            'status' => 'new',
            'quote_status' => 'not_started',
            'quote_currency' => 'ZAR',
        ]);

        return redirect()
            ->route('development-requests.services.requirements.thank-you')
            ->with('status', 'Your development request has been received. We will review it and contact you with next steps.');
    }

    public function thankYou(): View
    {
        return view('development-requests::thank-you');
    }
}
