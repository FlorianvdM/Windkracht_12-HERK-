<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Enums\Role;
use App\Models\Reservation;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Een klant kan melden dat hij betaald heeft (casus 2g). De reservering blijft voorlopig.
 */
class PaymentReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // De React-bestanden hoeven in de tests niet gebouwd te zijn.
        $this->withoutVite();

        // Testdata: Lara Visser heeft twee reserveringen (WK12-1001 is al gemeld, WK12-1007 nog niet).
        $this->seed(DatabaseSeeder::class);
    }

    private function lara(): User
    {
        return User::where('email', 'lara.visser@email.nl')->firstOrFail();
    }

    public function test_customer_can_report_payment_and_status_stays_voorlopig(): void
    {
        $reservering = Reservation::where('number', 'WK12-1007')->firstOrFail();

        $this->actingAs($this->lara())
            ->post(route('klant.reservations.report-payment', $reservering))
            ->assertRedirect();

        $reservering->refresh();
        $this->assertTrue($reservering->payment_reported);
        // Alleen de eigenaar mag een reservering definitief maken.
        $this->assertSame(ReservationStatus::Voorlopig, $reservering->status);
    }

    public function test_other_customer_cannot_report_payment_for_someone_else(): void
    {
        $reservering = Reservation::where('number', 'WK12-1007')->firstOrFail();
        $andereKlant = User::factory()->create(['role' => Role::Klant]);

        $this->actingAs($andereKlant)
            ->post(route('klant.reservations.report-payment', $reservering))
            ->assertNotFound();

        $this->assertFalse($reservering->fresh()->payment_reported);
    }

    public function test_guests_are_sent_to_login(): void
    {
        $reservering = Reservation::where('number', 'WK12-1007')->firstOrFail();

        $this->post(route('klant.reservations.report-payment', $reservering))
            ->assertRedirect(route('login'));
    }
}