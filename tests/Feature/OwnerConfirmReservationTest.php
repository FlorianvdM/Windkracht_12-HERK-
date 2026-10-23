<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Enums\Role;
use App\Models\Reservation;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * De eigenaar maakt een reservering definitief (casus 3j). Dit hoort bij de status van een boeking.
 */
class OwnerConfirmReservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // De React-bestanden hoeven in de tests niet gebouwd te zijn.
        $this->withoutVite();

        // Testdata: twee voorlopige reserveringen van Lara Visser (WK12-1001 en WK12-1007).
        $this->seed(DatabaseSeeder::class);
    }

    private function eigenaar(): User
    {
        return User::where('email', 'terence@kitesurfschool-windkracht12.nl')->firstOrFail();
    }

    private function lara(): User
    {
        return User::where('email', 'lara.visser@email.nl')->firstOrFail();
    }

    private function reservering(string $nummer = 'WK12-1007'): Reservation
    {
        return Reservation::where('number', $nummer)->firstOrFail();
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $reservering = $this->reservering();

        $this->get(route('eigenaar.reservations.show', $reservering))->assertRedirect(route('login'));
        $this->post(route('eigenaar.reservations.confirm', $reservering))->assertRedirect(route('login'));
    }

    public function test_customers_and_instructors_cannot_confirm(): void
    {
        $reservering = $this->reservering();
        $instructeur = User::where('role', Role::Instructeur)->firstOrFail();

        foreach ([$this->lara(), $instructeur] as $gebruiker) {
            $this->actingAs($gebruiker)->get(route('eigenaar.reservations.show', $reservering))->assertForbidden();
            $this->actingAs($gebruiker)->post(route('eigenaar.reservations.confirm', $reservering))->assertForbidden();
        }

        // De status is niet veranderd.
        $this->assertSame(ReservationStatus::Voorlopig, $reservering->fresh()->status);
    }

    public function test_owner_sees_the_page_with_both_mails(): void
    {
        $this->actingAs($this->eigenaar())
            ->get(route('eigenaar.reservations.show', $this->reservering()))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('eigenaar/definitief')
                ->where('reservation.number', 'WK12-1007')
                ->where('reservation.status', 'voorlopig')
                ->where('reservation.customer.email', 'lara.visser@email.nl')
                ->where('reservation.instructor.name', 'Duco Veenstra')
                ->where('reservation.instructor.email', 'duco@kitesurfschool-windkracht12.nl')
                ->has('reservation.lessons', 3));
    }

    public function test_owner_can_make_a_reservation_definitive(): void
    {
        $reservering = $this->reservering();

        $this->actingAs($this->eigenaar())
            ->post(route('eigenaar.reservations.confirm', $reservering))
            ->assertRedirect(route('eigenaar.reservations.show', $reservering));

        $reservering->refresh();
        $this->assertSame(ReservationStatus::Definitief, $reservering->status);

        // Verder is er niets aan de reservering veranderd.
        $this->assertSame(75000, $reservering->total_cents);
        $this->assertCount(3, $reservering->lessons);
    }

    public function test_only_the_chosen_reservation_changes(): void
    {
        $this->actingAs($this->eigenaar())->post(route('eigenaar.reservations.confirm', $this->reservering('WK12-1007')));

        $this->assertSame(ReservationStatus::Definitief, $this->reservering('WK12-1007')->status);
        $this->assertSame(ReservationStatus::Voorlopig, $this->reservering('WK12-1001')->status);
    }

    public function test_confirming_twice_changes_nothing(): void
    {
        $reservering = $this->reservering();
        $eigenaar = $this->eigenaar();

        $this->actingAs($eigenaar)->post(route('eigenaar.reservations.confirm', $reservering));
        $this->actingAs($eigenaar)->post(route('eigenaar.reservations.confirm', $reservering))
            ->assertRedirect(route('eigenaar.reservations.show', $reservering));

        $this->assertSame(ReservationStatus::Definitief, $reservering->fresh()->status);
    }

    public function test_customer_sees_the_new_status_in_my_reservations(): void
    {
        // Casus 3j: de status moet bij alle reserveringsoverzichten te zien zijn.
        $this->actingAs($this->eigenaar())->post(route('eigenaar.reservations.confirm', $this->reservering()));

        $this->actingAs($this->lara())->get(route('klant.reservations.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('reservations.0.number', 'WK12-1007')
                ->where('reservations.0.status', 'definitief')
                ->where('reservations.1.status', 'voorlopig'));
    }

    public function test_unknown_reservation_gives_a_404(): void
    {
        $this->actingAs($this->eigenaar())->get('/eigenaar/reserveringen/9999')->assertNotFound();
        $this->actingAs($this->eigenaar())->post('/eigenaar/reserveringen/9999/definitief')->assertNotFound();
    }
}
