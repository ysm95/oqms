<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeededUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_qms_seeded_users_are_available_with_expected_demo_passwords(): void
    {
        $this->seed();

        $expectedUsers = [
            'admin@qms.test' => ['QMS Administrator', 'password'],
            'yahya.alnaaimi@qms.test' => ['Yahya Al Naaimi', 'Yahya@2026'],
            'mazin.alfarsi@qms.test' => ['Mazin Al Farsi', 'Mazin@2026'],
            'aisha.albalushi@qms.test' => ['Aisha Al Balushi', 'Dummy@2026'],
            'omar.alharthy@qms.test' => ['Omar Al Harthy', 'Dummy@2026'],
        ];

        foreach ($expectedUsers as $email => [$name, $password]) {
            $user = User::where('email', $email)->first();

            $this->assertNotNull($user, "Missing seeded user {$email}");
            $this->assertSame($name, $user->name);
            $this->assertTrue(Hash::check($password, $user->password), "Invalid seeded password for {$email}");
        }
    }

    public function test_dashboard_route_loads_the_qms_application(): void
    {
        $this->seed();

        $this->actingAs(User::where('email', 'admin@qms.test')->first());

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Role-aware home')
            ->assertSee('Review reports');
    }
}
