<?php

namespace Tests\Feature\DevelopmentRequests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevelopmentRequestSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_services_without_registering(): void
    {
        $response = $this->get('/services');

        $response->assertOk();
        $response->assertSeeText('Custom development');
        $response->assertSeeText('Submit requirements');
    }

    public function test_guest_can_submit_development_requirements(): void
    {
        $response = $this->post('/services/requirements', [
            'client_name' => 'Sam Client',
            'client_email' => 'sam@example.com',
            'client_phone' => '0820000000',
            'company_name' => 'Sam Co',
            'preferred_contact_method' => 'email',
            'project_name' => 'Client Portal',
            'project_type' => 'Client portal',
            'project_goal' => 'We need a secure portal where customers can submit support requests and track progress.',
            'target_users' => 'Customers and internal support staff.',
            'current_process' => 'Email inbox and spreadsheets.',
            'must_have_features' => [
                'Customer request form',
                'Admin status tracking',
                '',
            ],
            'nice_to_have_features' => [
                'Dashboard reporting',
            ],
            'integrations' => 'Email notifications',
            'content_and_data' => 'Existing customer spreadsheet',
            'timeline' => '1 to 3 months',
            'budget_range' => 'R15,000 - R50,000',
            'success_measure' => 'Support requests are easier to track.',
            'additional_context' => 'We may add payments later.',
        ]);

        $response->assertRedirect('/services/requirements/thank-you');
        $response->assertSessionHas('status', 'Your development request has been received. We will review it and contact you with next steps.');

        $this->assertDatabaseHas('development_requests', [
            'user_id' => null,
            'client_name' => 'Sam Client',
            'client_email' => 'sam@example.com',
            'project_name' => 'Client Portal',
            'status' => 'new',
            'quote_status' => 'not_started',
            'quote_currency' => 'ZAR',
        ]);

        $this->assertSame(
            ['Customer request form', 'Admin status tracking'],
            json_decode((string) \DB::table('development_requests')->value('must_have_features'), true),
        );
    }

    public function test_authenticated_user_submission_is_linked_to_their_account(): void
    {
        $user = User::factory()->create([
            'name' => 'Account Client',
            'email' => 'account@example.com',
        ]);

        $this->actingAs($user)->post('/services/requirements', [
            'client_name' => 'Account Client',
            'client_email' => 'account@example.com',
            'preferred_contact_method' => 'email',
            'project_name' => 'Automation',
            'project_type' => 'Automation',
            'project_goal' => 'Automate a weekly report from a spreadsheet into a dashboard for managers.',
        ])->assertRedirect('/services/requirements/thank-you');

        $this->assertDatabaseHas('development_requests', [
            'user_id' => $user->id,
            'client_email' => 'account@example.com',
            'project_name' => 'Automation',
        ]);
    }
}
