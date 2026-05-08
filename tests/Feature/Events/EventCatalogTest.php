<?php

namespace Tests\Feature\Events;

use App\Models\User;
use CodeGarage\Events\Infrastructure\Persistence\Eloquent\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_events_page_only_shows_published_upcoming_events(): void
    {
        Event::query()->create([
            'title' => 'Code Garage Coding Day',
            'type' => 'coding_day',
            'summary' => 'Build a small project together.',
            'starts_at' => now()->addWeek(),
            'status' => 'published',
        ]);

        Event::query()->create([
            'title' => 'Draft Graduation',
            'type' => 'graduation',
            'summary' => 'Not ready for publishing yet.',
            'starts_at' => now()->addWeeks(2),
            'status' => 'draft',
        ]);

        Event::query()->create([
            'title' => 'Past Project Day',
            'type' => 'project_day',
            'summary' => 'Already happened.',
            'starts_at' => now()->subMonth(),
            'status' => 'published',
        ]);

        $response = $this->get('/events');

        $response->assertOk();
        $response->assertSeeText('Code Garage Coding Day');
        $response->assertSeeText('Past Project Day');
        $response->assertDontSeeText('Draft Graduation');
    }

    public function test_event_detail_page_displays_published_event_details(): void
    {
        $event = Event::query()->create([
            'title' => 'Graduation Showcase',
            'type' => 'graduation',
            'summary' => 'Celebrate students and their finished projects.',
            'description' => 'Students present their projects and receive certificates.',
            'location' => 'Main campus',
            'starts_at' => now()->addMonth(),
            'ends_at' => now()->addMonth()->addHours(2),
            'capacity' => 80,
            'status' => 'published',
        ]);

        $response = $this->get("/events/{$event->slug}");

        $response->assertOk();
        $response->assertSeeText('Graduation Showcase');
        $response->assertSeeText('Graduation');
        $response->assertSeeText('Main campus');
        $response->assertSeeText('Capacity: 80 people');
        $response->assertSeeText('Students present their projects and receive certificates.');
    }

    public function test_lecturer_can_create_a_published_event(): void
    {
        Role::findOrCreate('lecturer', 'web');
        $lecturer = User::factory()->create();
        $lecturer->assignRole('lecturer');

        $response = $this->actingAs($lecturer)->post('/events', [
            'title' => 'Build Day',
            'type' => 'project_day',
            'summary' => 'Students work together on a practical project.',
            'description' => 'Bring your laptop and current project ideas.',
            'location' => 'Code Garage Lab',
            'starts_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(10)->addHours(3)->format('Y-m-d H:i:s'),
            'capacity' => 24,
            'status' => 'published',
        ]);

        $event = Event::query()->where('title', 'Build Day')->firstOrFail();

        $response->assertRedirect("/events/{$event->slug}");
        $this->assertNotNull($event->published_at);
        $this->assertDatabaseHas('events', [
            'title' => 'Build Day',
            'type' => 'project_day',
            'created_by' => $lecturer->id,
            'status' => 'published',
        ]);
    }
}
