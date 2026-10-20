<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Reservation;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Bewijs voor deelvraag 1: een klant kan de eigen reserveringen bekijken ("Mijn pakketten").
 */
class ReservationOverviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // De React-bestanden hoeven in de tests niet gebouwd te zijn.
        $this->withoutVite();

        // Testdata: gebruikers, pakketten, locaties en twee voorbeeldreserveringen van Lara Visser.
        $this->seed(DatabaseSeeder::class);
    }

    /** Een klant die geen reserveringen heeft. */
    private function klant(): User
    {
        return User::factory()->create(['role' => Role::Klant]);
    }

    public function test_customer_only_sees_own_reservations(): void
    {
        $lara = User::where('email', 'lara.visser@email.nl')->firstOrFail();

        // Lara heeft twee reserveringen (testdata), de nieuwste bovenaan.
        $this->actingAs($lara)->get(route('klant.reservations.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('klant/reserveringen')
                ->has('reservations', 2)
                ->where('reservations.0.number', 'WK12-1007')
                ->where('reservations.0.status', 'voorlopig')
                ->has('reservations.0.lessons', 3));

        // Een andere klant ziet er geen enkele.
        $this->actingAs($this->klant())->get(route('klant.reservations.index'))
            ->assertInertia(fn (Assert $page) => $page->has('reservations', 0));
    }

    public function test_confirmation_page_is_only_for_the_owner(): void
    {
        $lara = User::where('email', 'lara.visser@email.nl')->firstOrFail();
        $reservering = Reservation::where('number', 'WK12-1007')->firstOrFail();

        $this->actingAs($lara)->get(route('klant.reservations.show', $reservering))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('klant/reservering')
                ->where('reservation.number', 'WK12-1007')
                ->where('reservation.total_cents', 75000)
                ->where('reservation.per_person_cents', 37500)
                ->where('reservation.location.instructor', 'Duco Veenstra'));

        // Een andere klant krijgt een 404: die mag niet weten dat dit nummer bestaat.
        $this->actingAs($this->klant())->get(route('klant.reservations.show', $reservering))->assertNotFound();
    }
}
