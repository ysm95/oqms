<?php

namespace Tests\Feature;

use App\Models\QmsCapaCase;
use App\Models\QmsFeedbackItem;
use App\Models\QmsInspection;
use App\Models\QmsNonconformance;
use App\Models\QmsPublicReport;
use App\Models\QmsRecordSimilarity;
use App\Models\QmsSafetyPromotion;
use App\Models\QmsStandard;
use App\Models\QmsStandardRequirement;
use App\Models\QmsTaxonomyTerm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase21V7StandardsCultureTest extends TestCase
{
    use RefreshDatabase;

    public function test_v7_seed_creates_versioned_standards_taxonomy_and_culture_records(): void
    {
        $this->seed();

        $this->assertDatabaseHas('qms_standards', [
            'code' => 'ISO-9001-2026',
            'publication_status' => 'Under publication',
        ]);
        $this->assertDatabaseHas('qms_standards', [
            'code' => 'ISO-14001-2026',
            'publication_status' => 'Published',
        ]);
        $this->assertDatabaseHas('qms_taxonomy_terms', [
            'taxonomy' => 'event-phase',
            'code' => 'PHASE-APPROACH',
            'active' => true,
        ]);

        $this->assertGreaterThanOrEqual(6, QmsStandard::count());
        $this->assertGreaterThanOrEqual(6, QmsStandardRequirement::count());
        $this->assertGreaterThanOrEqual(6, QmsTaxonomyTerm::count());
        $this->assertSame(1, QmsInspection::count());
        $this->assertSame(1, QmsNonconformance::count());
        $this->assertSame(1, QmsCapaCase::count());
        $this->assertSame(1, QmsRecordSimilarity::count());
        $this->assertSame(1, QmsSafetyPromotion::count());
    }

    public function test_compliance_page_exposes_registry_without_storing_licensed_standard_text(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@qms.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/compliance')
            ->assertOk()
            ->assertSee('Standards registry')
            ->assertSee('ISO-9001-2026')
            ->assertSee('Under publication')
            ->assertSee('Change impact')
            ->assertSee('Taxonomy registry')
            ->assertSee('No licensed text stored')
            ->assertDontSee('certifies compliance');
    }

    public function test_unified_shell_uses_v7_navigation_and_no_nested_module_shells(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@qms.test')->firstOrFail();

        $response = $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Safety')
            ->assertSee('Quality')
            ->assertSee('Observations')
            ->assertSee('Knowledge')
            ->assertSee('Create')
            ->assertSee('Help')
            ->assertSee('What needs attention');

        $html = $response->getContent();

        $this->assertSame(1, substr_count($html, 'class="app-shell"'));
        $this->assertSame(1, substr_count($html, 'class="sidebar"'));
        $this->assertStringNotContainsString('Syncing', $html);
    }

    public function test_audit_inspection_ncr_and_capa_are_distinct_user_workspaces(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@qms.test')->firstOrFail();

        $this->actingAs($admin)->get('/audits')->assertOk()->assertSee('ISO 9001:2015 / ICAO SMS');
        $this->actingAs($admin)->get('/inspections')->assertOk()->assertSee('Pass / Fail / N/A')->assertSee('Ramp FOD and GSE control inspection');
        $this->actingAs($admin)->get('/nonconformances')->assertOk()->assertSee('Requirement + evidence + statement')->assertSee('NCR-2026-00019');
        $this->actingAs($admin)->get('/capa')->assertOk()->assertSee('Root cause to effectiveness')->assertSee('CAPA-CASE-2026-00007');
    }

    public function test_feedback_is_separate_from_reporter_safety_reporting(): void
    {
        $this->seed();
        $reporter = User::factory()->create(['email' => 'feedback.reporter@qms.test']);
        $reporter->forceFill(['qms_role' => 'Reporter', 'is_active' => true])->save();

        $beforeReports = QmsPublicReport::count();

        $this->actingAs($reporter)
            ->get('/feedback')
            ->assertOk()
            ->assertSee('Separate from safety reporting')
            ->assertDontSee('CAPA')
            ->assertDontSee('Incident');

        $this->actingAs($reporter)->post('/feedback', [
            'feedback_type' => 'Accessibility issue',
            'context' => 'Reporter form',
            'message' => 'The attachment button needs clearer keyboard focus.',
        ])->assertRedirect();

        $this->assertSame($beforeReports, QmsPublicReport::count());
        $this->assertDatabaseHas('qms_feedback_items', [
            'user_id' => $reporter->id,
            'feedback_type' => 'Accessibility issue',
            'context' => 'Reporter form',
        ]);
        $this->assertTrue(QmsFeedbackItem::where('user_id', $reporter->id)->exists());
    }
}
