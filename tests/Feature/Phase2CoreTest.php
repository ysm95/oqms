<?php

namespace Tests\Feature;

use App\Models\QmsOccurrence;
use App\Models\QmsReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase2CoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_users_have_expected_demo_access(): void
    {
        $this->seed();

        $this->assertTrue(Hash::check('Yahya@2026', User::where('email', 'yahya.alnaaimi@qms.test')->first()->password));
        $this->assertTrue(Hash::check('Mazin@2026', User::where('email', 'mazin.alfarsi@qms.test')->first()->password));
        $this->assertTrue(Hash::check('Dummy@2026', User::where('email', 'aisha.albalushi@qms.test')->first()->password));
        $this->assertTrue(Hash::check('Dummy@2026', User::where('email', 'omar.alharthy@qms.test')->first()->password));
    }

    public function test_login_and_dashboard_work(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'admin@qms.test',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Role-aware home')
            ->assertSee('What needs attention');
    }

    public function test_authenticated_user_can_submit_report_for_screening(): void
    {
        $this->seed();
        $this->actingAs(User::where('email', 'yahya.alnaaimi@qms.test')->first());

        $this->post('/occurrences', [
            'report_key' => 'dispatch-occurrence',
            'type' => 'Dispatch occurrence',
            'event_title' => 'Dispatch fuel planning concern',
            'event_date' => now()->toDateString(),
            'area_fleet' => 'Dispatch / B737',
            'sector_to' => 'DXB',
            'sector_diverted' => 'MCT',
            'location' => 'OQB Locations',
            'exact_location' => 'Ramp area',
            'reported_by' => 'Yahya Al Naaimi',
            'pilot_name' => 'Mazin Al Farsi',
            'description' => 'A test occurrence with enough operational detail for screening.',
            'confidential' => 0,
            'mor' => 1,
            'event_categories' => ['Flight Planning', 'Fuel', 'Flight phase: Cruise'],
            'aircraft_type' => 'B737',
            'aircraft_registration' => 'A4O-TEST',
            'flight_number' => 'WY123',
            'time_of_occurrence' => '12:30',
            'flight_cancelled' => 0,
            'flight_plan_details' => 'Flight plan amended before release.',
            'action_taken' => ['Informed supervisor', 'Issued revised flight plan'],
            'immediate_corrective_action' => 'Dispatcher revised the flight plan and briefed the crew.',
        ])->assertRedirect();

        $this->assertDatabaseHas('qms_reports', [
            'type' => 'Dispatch occurrence',
            'workflow_stage' => 'Screening',
            'title' => 'Dispatch fuel planning concern',
            'status' => 'Submitted',
        ]);
        $this->assertDatabaseMissing('qms_incidents', ['title' => 'Dispatch fuel planning concern']);
        $this->assertSame(2, QmsOccurrence::count());
        $this->assertSame(2, QmsReport::count());
    }

    public function test_commander_form_has_searchable_reference_fields(): void
    {
        $this->seed();
        $this->actingAs(User::where('email', 'admin@qms.test')->first());

        $this->get('/occurrences/create?report_type=commander-voyage')
            ->assertOk()
            ->assertSee('Search user by %text%', false)
            ->assertSee('Pilot name')
            ->assertSee('Sector to')
            ->assertSee('Flight phase')
            ->assertSee('A330 Fleet');
    }

    public function test_reporting_catalogue_loads(): void
    {
        $this->seed();
        $this->actingAs(User::where('email', 'admin@qms.test')->first());

        $this->get('/reporting')
            ->assertOk()
            ->assertSee('Central workspace')
            ->assertSee('Report list')
            ->assertSee('Dispatch Occurrence Report')
            ->assertSee('Safety Confidential Report');
    }
}
