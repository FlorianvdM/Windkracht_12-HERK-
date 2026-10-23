<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Bewijs voor deelvraag 2: pagina's zijn beveiligd per gebruikersrol.
 */
class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // In de tests hoeven we de React-bestanden niet echt te bouwen: we testen alleen wat Laravel teruggeeft.
        $this->withoutVite();
    }

    /** Maakt een gebruiker met een bepaalde rol (de factory mag de rol wel invullen). */
    private function gebruiker(Role $rol): User
    {
        return User::factory()->create(['role' => $rol]);
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        foreach (['klant.home', 'instructeur.home', 'eigenaar.home'] as $routeNaam) {
            $this->get(route($routeNaam))->assertRedirect(route('login'));
        }
    }

    public function test_customer_sees_only_the_customer_pages(): void
    {
        $klant = $this->gebruiker(Role::Klant);

        $this->actingAs($klant)->get(route('klant.home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('klant/home'));

        $this->actingAs($klant)->get(route('instructeur.home'))->assertForbidden();
        $this->actingAs($klant)->get(route('eigenaar.home'))->assertForbidden();
    }

    public function test_instructor_sees_only_the_instructor_pages(): void
    {
        $instructeur = $this->gebruiker(Role::Instructeur);

        $this->actingAs($instructeur)->get(route('instructeur.home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('instructeur/home'));

        $this->actingAs($instructeur)->get(route('klant.home'))->assertForbidden();
        $this->actingAs($instructeur)->get(route('eigenaar.home'))->assertForbidden();
    }

    public function test_owner_can_see_all_pages(): void
    {
        $eigenaar = $this->gebruiker(Role::Eigenaar);

        $this->actingAs($eigenaar)->get(route('eigenaar.home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('eigenaar/home'));

        $this->actingAs($eigenaar)->get(route('klant.home'))->assertOk();
        $this->actingAs($eigenaar)->get(route('instructeur.home'))->assertOk();
    }

    public function test_the_logged_in_role_is_shared_with_every_page(): void
    {
        // De zijbalk toont "Ingelogd als ... (rol)". Daarvoor moet de rol bij de pagina worden meegegeven.
        $this->actingAs($this->gebruiker(Role::Instructeur))
            ->get(route('instructeur.home'))
            ->assertInertia(fn (Assert $page) => $page->where('auth.user.role', 'instructeur'));
    }
}
