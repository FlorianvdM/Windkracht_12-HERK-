<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_each_role_is_redirected_to_its_own_home_page()
    {
        // /dashboard stuurt elke rol door naar de eigen homepage.
        $verwacht = [
            Role::Klant->value => 'klant.home',
            Role::Instructeur->value => 'instructeur.home',
            Role::Eigenaar->value => 'eigenaar.home',
        ];

        foreach ($verwacht as $rol => $routeNaam) {
            $user = User::factory()->create(['role' => $rol]);

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertRedirect(route($routeNaam));
        }
    }
}
