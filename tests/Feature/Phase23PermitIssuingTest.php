<?php

namespace Tests\Feature;

use App\Models\QmsAuditLog;
use App\Models\QmsPermit;
use App\Models\QmsPermitActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase23PermitIssuingTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_user_can_create_page_based_control_of_work_permit(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@qms.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/permits/create')
            ->assertOk()
            ->assertSee('Work Information')
            ->assertSee('Description &amp; Risk', false)
            ->assertSee('Controls')
            ->assertSee('Approval')
            ->assertSee('Review &amp; Submit', false);

        $this->actingAs($admin)->post('/permits', $this->validPermitPayload([
            'submit_action' => 'submit',
        ]))->assertRedirect();

        $this->assertDatabaseHas('qms_permits', [
            'permit_type' => 'Hot Work',
            'title' => 'Replace temporary ramp light',
            'status' => 'Pending Review',
            'risk_rating' => 'High',
            'residual_risk' => 'Medium',
            'isolation_required' => true,
            'gas_test_required' => true,
        ]);

        $permit = QmsPermit::firstOrFail();
        $this->assertStringStartsWith('PTW-', $permit->reference);
        $this->assertContains('Fire or ignition', $permit->hazards);
        $this->assertContains('Fire watch assigned', $permit->controls);
    }

    public function test_permit_lifecycle_records_activity_and_audit_trail(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@qms.test')->firstOrFail();
        $permit = QmsPermit::create([
            'reference' => 'PTW-2026-000001',
            'permit_type' => 'Hot Work',
            'title' => 'Welding repair at bay door',
            'work_description' => 'Repair a small bracket at the bay door.',
            'requester' => 'Yahya Al Naaimi',
            'department' => 'Engineering',
            'area' => 'Ground Operations',
            'unit' => 'MCT',
            'planned_start_at' => now()->addHour(),
            'planned_end_at' => now()->addHours(8),
            'status' => 'Pending Review',
            'risk_rating' => 'High',
            'residual_risk' => 'Medium',
            'owner' => 'Maintenance Supervisor',
            'current_approver' => 'HSE Reviewer',
            'hazards' => ['Fire or ignition'],
            'controls' => ['Fire watch assigned'],
            'required_ppe' => ['Eye protection'],
        ]);

        $this->actingAs($admin)->patch('/permits/' . $permit->id . '/transition', [
            'action' => 'approve',
            'transition_note' => 'Controls reviewed and accepted.',
        ])->assertRedirect();

        $this->actingAs($admin)->patch('/permits/' . $permit->id . '/transition', [
            'action' => 'issue',
            'transition_note' => 'Issued after field briefing.',
        ])->assertRedirect();

        $this->actingAs($admin)->patch('/permits/' . $permit->id . '/transition', [
            'action' => 'suspend',
            'transition_note' => 'Weather conditions changed.',
        ])->assertRedirect();

        $this->actingAs($admin)->patch('/permits/' . $permit->id . '/transition', [
            'action' => 'close',
            'closeout_summary' => 'Work area inspected and returned to normal.',
        ])->assertRedirect();

        $permit->refresh();

        $this->assertSame('Closed', $permit->status);
        $this->assertNotNull($permit->issued_at);
        $this->assertNotNull($permit->closed_at);
        $this->assertSame(4, QmsPermitActivity::where('qms_permit_id', $permit->id)->count());
        $this->assertSame(4, QmsAuditLog::where('auditable_type', QmsPermit::class)->where('auditable_id', $permit->id)->count());
    }

    public function test_reporter_cannot_access_internal_permit_workspace(): void
    {
        $this->seed();
        $reporter = User::factory()->create(['email' => 'permit.reporter@qms.test']);
        $reporter->forceFill(['qms_role' => 'Reporter', 'is_active' => true])->save();

        $this->actingAs($reporter)
            ->get('/permits')
            ->assertForbidden();

        $this->actingAs($reporter)
            ->get('/reporter')
            ->assertOk()
            ->assertDontSee('Permit Issuing')
            ->assertDontSee('Permit reviewer');
    }

    public function test_my_work_includes_open_permits(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@qms.test')->firstOrFail();

        QmsPermit::create([
            'reference' => 'PTW-2026-000099',
            'permit_type' => 'Confined Space',
            'title' => 'Tank entry preparation',
            'work_description' => 'Prepare controlled entry for inspection.',
            'requester' => 'Yahya Al Naaimi',
            'department' => 'HSE',
            'area' => 'Engineering',
            'unit' => 'Hangar',
            'planned_start_at' => now()->addDay(),
            'planned_end_at' => now()->addDays(2),
            'status' => 'Approved',
            'risk_rating' => 'Critical',
            'residual_risk' => 'High',
            'owner' => 'Engineering',
            'current_approver' => 'Permit Issuer',
        ]);

        $this->actingAs($admin)
            ->get('/my-work')
            ->assertOk()
            ->assertSee('PTW-2026-000099')
            ->assertSee('Permit')
            ->assertSee('Tank entry preparation');
    }

    private function validPermitPayload(array $overrides = []): array
    {
        return array_merge([
            'submit_action' => 'draft',
            'permit_type' => 'Hot Work',
            'title' => 'Replace temporary ramp light',
            'work_description' => 'Replace damaged temporary lighting near the ramp access point.',
            'requester' => 'Yahya Al Naaimi',
            'department' => 'Engineering',
            'contractor' => 'Internal maintenance',
            'area' => 'Ground Operations',
            'unit' => 'MCT',
            'asset' => 'Ramp light RL-22',
            'planned_start_date' => now()->addDay()->toDateString(),
            'planned_start_time' => '08:00',
            'planned_end_date' => now()->addDay()->toDateString(),
            'planned_end_time' => '16:00',
            'risk_rating' => 'High',
            'residual_risk' => 'Medium',
            'owner' => 'Maintenance Supervisor',
            'current_approver' => 'HSE Reviewer',
            'hazards' => ['Fire or ignition', 'Energy isolation'],
            'controls' => ['Fire watch assigned', 'LOTO applied'],
            'required_ppe' => ['Eye protection', 'Gloves'],
            'required_training' => ['Permit receiver'],
            'loto_points' => "Electrical panel A\nBreaker 12",
            'linked_documents' => "JSA-HOT-001\nElectrical isolation procedure",
            'isolation_required' => 1,
            'gas_test_required' => 1,
            'fire_watch_required' => 1,
            'standby_required' => 0,
        ], $overrides);
    }
}
