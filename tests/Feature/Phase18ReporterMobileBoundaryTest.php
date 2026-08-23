<?php

namespace Tests\Feature;

use App\Models\QmsPublicReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase18ReporterMobileBoundaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporter_home_shows_only_authorized_report_types(): void
    {
        $this->seed();
        $reporter = $this->reporterUser();

        $this->actingAs($reporter)
            ->get('/reporter')
            ->assertOk()
            ->assertSee('QMS Reporter')
            ->assertSee('Air Safety Report')
            ->assertSee('Ground Occurrence Report')
            ->assertSee('Hazard Reporting Form')
            ->assertDontSee('Commander Voyage Report')
            ->assertDontSee('Dangerous Goods Occurrence Report');
    }

    public function test_public_reporter_home_does_not_show_internal_workspace(): void
    {
        $this->seed();

        $this->get('/reporter')
            ->assertOk()
            ->assertSee('Report an observation or concern')
            ->assertSee('Observation')
            ->assertSee('Ground Occurrence Report')
            ->assertDontSee('Screening Queue')
            ->assertDontSee('Admin Center')
            ->assertDontSee('Investigations');
    }

    public function test_reporter_cannot_access_internal_qms_routes(): void
    {
        $this->seed();
        $reporter = $this->reporterUser();

        $this->actingAs($reporter)->get('/reporting')->assertForbidden();
        $this->actingAs($reporter)->get('/dashboard')->assertForbidden();
    }

    public function test_reporter_submission_gets_receipt_without_internal_workflow_leakage(): void
    {
        $this->seed();
        $reporter = $this->reporterUser();

        $this->actingAs($reporter)->post('/reporter/report/hazard', [
            'form_version' => 1,
            'title' => 'Loose equipment near walkway',
            'location' => 'Hangar 2',
            'description' => 'Equipment is not secured and could create a trip hazard.',
        ])->assertRedirect();

        $report = QmsPublicReport::firstOrFail();

        $this->assertSame($reporter->id, $report->reporter_user_id);
        $this->assertSame('Submitted', $report->public_status);
        $this->assertSame('New', $report->status);
        $this->assertStringStartsWith('PUB-', $report->reference);

        $this->get('/reporter/receipts/' . $report->receipt_token)
            ->assertOk()
            ->assertSee($report->reference)
            ->assertSee('Submitted')
            ->assertDontSee('Screening')
            ->assertDontSee('workflow_stage')
            ->assertDontSee('Incident')
            ->assertDontSee('Reviewer');
    }

    public function test_reporter_receipt_shows_only_reporter_visible_messages(): void
    {
        $this->seed();
        $reporter = $this->reporterUser();

        $report = QmsPublicReport::create([
            'reference' => 'PUB-2026-777777',
            'report_type_key' => 'hazard',
            'reporter_user_id' => $reporter->id,
            'category' => 'Hazard',
            'location' => 'Hangar 2',
            'anonymous' => false,
            'confidential' => false,
            'status' => 'New',
            'public_status' => 'Information Required',
            'receipt_token' => 'safe-receipt-token',
            'form_version' => 1,
            'description' => 'Reporter-facing summary.',
            'submitted_payload' => ['report_type_title' => 'Hazard Reporting Form'],
            'client_context' => [
                'internal_comment' => 'Do not expose this internal note.',
            ],
            'reporter_visible_messages' => [
                [
                    'visibility' => 'REPORTER_VISIBLE',
                    'message' => 'Please add the exact bay number.',
                ],
            ],
        ]);

        $this->actingAs($reporter)
            ->get('/reporter/receipts/' . $report->receipt_token)
            ->assertOk()
            ->assertSee('Please add the exact bay number.')
            ->assertDontSee('Do not expose this internal note.')
            ->assertDontSee('internal_comment')
            ->assertDontSee('Screening')
            ->assertDontSee('Reviewer');
    }

    public function test_reporter_mobile_api_returns_authorized_forms_without_internal_fields(): void
    {
        $this->seed();
        $reporter = $this->reporterUser();

        $response = $this->actingAs($reporter)
            ->getJson('/api/reporter/report-types')
            ->assertOk()
            ->assertJsonFragment(['key' => 'air-safety'])
            ->assertJsonFragment(['key' => 'hazard']);

        $payload = $response->getContent();
        $this->assertStringNotContainsString('workflow_stage', $payload);
        $this->assertStringNotContainsString('internal_status', $payload);
        $this->assertStringNotContainsString('screening', $payload);
    }

    public function test_offline_draft_validation_rejects_obsolete_form_version(): void
    {
        $this->seed();
        $reporter = $this->reporterUser();

        $this->actingAs($reporter)->postJson('/api/reporter/offline/validate', [
            'report_type_key' => 'hazard',
            'form_version' => 2,
        ])->assertOk()
            ->assertJson([
                'compatible' => false,
                'current_form_version' => 1,
            ]);

        $this->actingAs($reporter)->postJson('/api/reporter/reports', [
            'report_type_key' => 'hazard',
            'form_version' => 2,
            'title' => 'Offline stale draft',
            'location' => 'Station office',
            'description' => 'This draft should be rejected until the current form is loaded.',
        ])->assertStatus(409);
    }

    private function reporterUser(): User
    {
        $user = User::factory()->create([
            'name' => 'Reporter User',
            'email' => 'reporter.user@qms.test',
        ]);

        $user->forceFill([
            'qms_role' => 'Reporter',
            'is_active' => true,
        ])->save();

        return $user;
    }
}
