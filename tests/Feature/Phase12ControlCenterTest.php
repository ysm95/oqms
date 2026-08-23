<?php

namespace Tests\Feature;

use App\Models\QmsConfigurationPackage;
use App\Models\QmsModuleLicense;
use App\Models\QmsNumberingRule;
use App\Models\QmsSystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase12ControlCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_enterprise_control_center_defaults_are_seeded_and_visible(): void
    {
        $this->seed();
        $this->actingAs(User::where('email', 'admin@qms.test')->first());

        $this->get('/platform')
            ->assertOk()
            ->assertSee('Branding and system settings')
            ->assertSee('Numbering designer')
            ->assertSee('Configuration package')
            ->assertSee('QMS-CORE')
            ->assertSee('NUM-INC')
            ->assertSee('NUM-PUB')
            ->assertSee('CFG-BASELINE-001');

        $this->assertDatabaseHas('qms_system_settings', ['key' => 'brand.primary']);
        $this->assertDatabaseHas('qms_module_licenses', ['code' => 'SMS', 'enabled' => true]);
        $this->assertDatabaseHas('qms_numbering_rules', ['code' => 'NUM-INC', 'prefix' => 'INC']);
        $this->assertDatabaseHas('qms_configuration_packages', ['code' => 'CFG-BASELINE-001', 'status' => 'Validated']);
        $this->assertSame(6, QmsModuleLicense::count());
        $this->assertSame(8, QmsNumberingRule::count());
        $this->assertSame(1, QmsSystemSetting::count());
    }

    public function test_admin_can_create_numbering_rule_and_configuration_package(): void
    {
        $this->seed();
        $this->actingAs(User::where('email', 'admin@qms.test')->first());

        $this->post('/platform/numbering-rules', [
            'code' => 'NUM-MOC',
            'module' => 'Management of Change',
            'prefix' => 'MOC',
            'pattern' => '{PREFIX}-{YYYY}-{SEQ:6}',
            'next_sequence' => 25,
            'reset_annually' => 1,
            'status' => 'Active',
        ])->assertRedirect('/platform');

        $this->post('/platform/configuration-packages', [
            'code' => 'CFG-UAT-002',
            'name' => 'UAT controlled workflow package',
            'status' => 'Validated',
            'effective_date' => '2026-09-01',
            'payload_summary' => 'Workflow, numbering and notification changes',
            'validation_summary' => 'No missing dependencies.',
        ])->assertRedirect('/platform');

        $this->assertDatabaseHas('qms_numbering_rules', ['code' => 'NUM-MOC', 'next_sequence' => 25]);
        $this->assertDatabaseHas('qms_configuration_packages', ['code' => 'CFG-UAT-002', 'status' => 'Validated']);
        $this->assertSame('UAT controlled workflow package', QmsConfigurationPackage::where('code', 'CFG-UAT-002')->first()->name);
    }
}
