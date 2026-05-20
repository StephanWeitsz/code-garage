<?php

namespace App\Livewire\Admin;

use App\Models\VisitorSession;
use App\Services\AnalyticsService;
use Livewire\Component;

class VisitorActivity extends Component
{
    public VisitorSession $visitorSession;

    public function mount(VisitorSession $visitorSession): void
    {
        $this->visitorSession = $visitorSession->load('user:id,name,email');
    }

    public function render(AnalyticsService $analytics)
    {
        return view('livewire.admin.visitor-activity', [
            'journey' => $analytics->visitorJourney($this->visitorSession->id),
        ])->layout('layouts.app');
    }
}
