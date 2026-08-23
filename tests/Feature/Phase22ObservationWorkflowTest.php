<?php

namespace Tests\Feature;

use App\Models\QmsAction;
use App\Models\QmsIncident;
use App\Models\QmsOccurrence;
use App\Models\QmsPublicReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase22ObservationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_user_can_submit_page_based_unsafe_condition_observation(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@qms.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/observations/create')
            ->assertOk()
            ->assertSee('Information')
            ->assertSee('Description')
            ->assertSee('Action Taken')
            ->assertSee('Attachments')
            ->assertSee('Review and Submit')
            ->assertSee('Unsafe Act')
            ->assertSee('Unsafe Condition');

        $this->actingAs($admin)->post('/observations', [
            'observation_type' => 'Unsafe Condition',
            'area' => 'Ground Operations',
            'unit' => 'MCT',
            'observed_on' => '2026-08-23',
            'observed_at' => '09:20',
            'observer' => 'Yahya Al Naaimi',
            'location' => 'Ramp',
            'exact_location' => 'Gate 4',
            'department_name' => 'HSE',
            'event_title' => 'Missing barricade at temporary work area',
            'description' => 'Temporary barricade was missing from an active work area.',
            'potential_consequence' => 'People could enter the area without warning.',
            'action_taken' => ['Supervisor informed'],
            'immediate_corrective_action' => 'Supervisor informed and cone placed.',
            'temporary_control' => 'Temporary warning cone placed.',
        ])->assertRedirect();

        $this->assertDatabaseHas('qms_occurrences', [
            'record_family' => 'Observation',
            'observation_type' => 'Unsafe Condition',
            'title' => 'Missing barricade at temporary work area',
            'workflow_stage' => 'HSE Review',
            'status' => 'Submitted',
        ]);
    }

    public function test_hse_review_and_action_tracker_are_inside_observation_record(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@qms.test')->firstOrFail();
        $observation = QmsOccurrence::where('record_family', 'Observation')->firstOrFail();

        $this->actingAs($admin)
            ->get('/observations/' . $observation->id)
            ->assertOk()
            ->assertSee('HSE Review')
            ->assertSee('Is the observation valid?')
            ->assertSee('Action Tracker')
            ->assertSee('Add Entry');

        $this->actingAs($admin)->patch('/observations/' . $observation->id . '/review', [
            'review_decision' => 'Valid',
            'reviewer_comments' => 'Observation is valid and requires a follow-up action.',
            'risk_rating' => 'High',
            'action_required' => 1,
            'reporter_visible_message' => 'The observation was reviewed by HSE.',
        ])->assertRedirect();

        $this->assertDatabaseHas('qms_occurrences', [
            'id' => $observation->id,
            'review_decision' => 'Valid',
            'workflow_stage' => 'Action Tracker',
            'risk_rating' => 'High',
            'action_required' => true,
        ]);

        $this->actingAs($admin)->post('/observations/' . $observation->id . '/actions', [
            'action_type' => 'Corrective Action',
            'description' => 'Restore barricade control and brief the work team.',
            'owner' => 'Engineering',
            'responsible_department' => 'Engineering',
            'due_date' => '2026-08-30',
            'priority' => 'High',
            'evidence_required' => 1,
            'verification_required' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('qms_actions', [
            'source_reference' => $observation->reference,
            'owner' => 'Engineering',
            'priority' => 'High',
            'status' => 'Open',
        ]);
        $this->assertSame(1, QmsAction::where('source_reference', $observation->reference)->count());
    }

    public function test_reporter_sees_observation_first_and_cannot_access_internal_observation_review(): void
    {
        $this->seed();
        $reporter = User::factory()->create(['email' => 'observer@qms.test']);
        $reporter->forceFill(['qms_role' => 'Reporter', 'is_active' => true])->save();

        $this->actingAs($reporter)
            ->get('/reporter')
            ->assertOk()
            ->assertSee('Observation')
            ->assertSee('Unsafe Act')
            ->assertDontSee('Action Tracker');

        $this->actingAs($reporter)
            ->get('/observations')
            ->assertForbidden();
    }

    public function test_reporter_observation_submission_stays_reporter_intake_and_does_not_create_incident(): void
    {
        $this->seed();
        $reporter = User::factory()->create(['email' => 'field.reporter@qms.test']);
        $reporter->forceFill(['qms_role' => 'Reporter', 'is_active' => true])->save();

        $beforeIncidents = QmsIncident::count();

        $this->actingAs($reporter)->post('/reporter/report/observation', [
            'form_version' => 1,
            'title' => 'Unsafe act near loading bay',
            'location' => 'Cargo',
            'description' => 'A person crossed the marked loading path while equipment was moving.',
            'observation_type' => 'Unsafe Act',
            'area' => 'Cargo',
            'unit' => 'MCT',
            'action_taken' => ['Supervisor informed'],
            'immediate_corrective_action' => 'Supervisor was informed immediately.',
        ])->assertRedirect();

        $this->assertSame($beforeIncidents, QmsIncident::count());
        $this->assertDatabaseHas('qms_public_reports', [
            'report_type_key' => 'observation',
            'category' => 'Observation',
            'status' => 'New',
        ]);
        $this->assertSame('Unsafe Act', QmsPublicReport::where('report_type_key', 'observation')->latest()->first()->submitted_payload['observation_type']);
    }
}
