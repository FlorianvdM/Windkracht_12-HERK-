<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Enums\Role;
use App\Models\Lesson;
use App\Models\Location;
use App\Models\Package;
use App\Models\Reservation;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Bewijs voor deelvraag 1: een klant kan een reservering maken (formulier, validatie en opslaan).
 */
class ReservationFlowTest extends TestCase
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

    /** De dag die we in de tests gebruiken: ver genoeg in de toekomst om niet te botsen met de testdata. */
    private function dag(int $dagenVanaf): string
    {
        return now()->addDays(100 + $dagenVanaf)->format('Y-m-d');
    }

    /** @return array<string, mixed> */
    private function geldigeReservering(string $pakket = 'Duo-pakket 3 lessen', string $locatie = 'Zandvoort'): array
    {
        return [
            'package_id' => Package::where('name', $pakket)->value('id'),
            'location_id' => Location::where('name', $locatie)->value('id'),
            'lesson_dates' => [$this->dag(0), $this->dag(1), $this->dag(2)],
        ];
    }

    public function test_guests_and_instructors_cannot_reserve(): void
    {
        $this->get(route('klant.reservations.create'))->assertRedirect(route('login'));
        $this->post(route('klant.reservations.store'), $this->geldigeReservering())->assertRedirect(route('login'));

        $instructeur = User::factory()->create(['role' => Role::Instructeur]);
        $this->actingAs($instructeur)->get(route('klant.reservations.create'))->assertForbidden();
        $this->actingAs($instructeur)->post(route('klant.reservations.store'), $this->geldigeReservering())->assertForbidden();
    }

    public function test_reservation_page_shows_packages_and_locations_with_instructor(): void
    {
        $this->actingAs($this->klant())
            ->get(route('klant.reservations.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('klant/reserveren')
                ->has('packages', 4)
                ->has('locations', 6)
                ->where('locations.0.name', 'Hoek van Holland')   // alfabetisch gesorteerd
                ->where('locations.0.instructor', 'Bernie Vredenstein'));
    }

    public function test_booked_days_of_the_instructor_are_sent_along(): void
    {
        $reservering = Reservation::firstOrFail();
        $duco = User::where('email', 'duco@kitesurfschool-windkracht12.nl')->firstOrFail();
        Lesson::create([
            'reservation_id' => $reservering->id,
            'instructor_id' => $duco->id,
            'lesson_date' => $this->dag(5),
        ]);

        // Zandvoort en Wijk aan Zee delen Duco: op beide locaties is die dag bezet.
        $this->actingAs($this->klant())
            ->get(route('klant.reservations.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('locations', fn ($locaties) => collect($locaties)
                    ->whereIn('name', ['Zandvoort', 'Wijk aan Zee'])
                    ->every(fn ($locatie) => in_array($this->dag(5), (array) $locatie['booked_dates'], true))));
    }

    public function test_customer_can_make_a_reservation(): void
    {
        $klant = $this->klant();

        $response = $this->actingAs($klant)->post(route('klant.reservations.store'), $this->geldigeReservering());

        $reservering = Reservation::where('user_id', $klant->id)->firstOrFail();
        $response->assertRedirect(route('klant.reservations.show', $reservering));

        // Status 'voorlopig', totaal = 2 x € 375,00 (de aanmelder betaalt alles) en een nieuw nummer.
        $this->assertSame(ReservationStatus::Voorlopig, $reservering->status);
        $this->assertSame(75000, $reservering->total_cents);
        $this->assertFalse($reservering->payment_reported);
        $this->assertSame('WK12-1008', $reservering->number);   // de testdata gaat tot WK12-1007

        // Drie lessen, allemaal bij de instructeur van Zandvoort (Duco Veenstra).
        $this->assertCount(3, $reservering->lessons);
        $this->assertSame('Duco Veenstra', $reservering->lessons->first()->instructor->name);
        $this->assertSame([$this->dag(0), $this->dag(1), $this->dag(2)], $reservering->lessons->sortBy('lesson_date')->map(fn (Lesson $les) => $les->lesson_date->format('Y-m-d'))->values()->all());
    }

    public function test_every_reservation_gets_its_own_number(): void
    {
        $klant = $this->klant();

        $this->actingAs($klant)->post(route('klant.reservations.store'), $this->geldigeReservering());

        $tweede = [
            'package_id' => Package::where('name', 'Privéles')->value('id'),
            'location_id' => Location::where('name', 'Scheveningen')->value('id'),
            'lesson_dates' => [$this->dag(0)],
        ];
        $this->actingAs($klant)->post(route('klant.reservations.store'), $tweede)->assertSessionHasNoErrors();

        $this->assertSame(['WK12-1008', 'WK12-1009'], Reservation::where('user_id', $klant->id)->orderBy('id')->pluck('number')->all());
        $this->assertSame(17500, Reservation::where('number', 'WK12-1009')->value('total_cents'));   // privéles: 1 persoon
    }

    public function test_status_total_and_customer_cannot_be_chosen_through_the_form(): void
    {
        $klant = $this->klant();
        $andereKlant = $this->klant();

        $this->actingAs($klant)->post(route('klant.reservations.store'), $this->geldigeReservering() + [
            'status' => 'definitief',
            'total_cents' => 1,
            'user_id' => $andereKlant->id,
            'number' => 'WK12-0001',
        ]);

        $reservering = Reservation::where('user_id', $klant->id)->firstOrFail();
        $this->assertSame(ReservationStatus::Voorlopig, $reservering->status);
        $this->assertSame(75000, $reservering->total_cents);
        $this->assertSame('WK12-1008', $reservering->number);
        $this->assertSame(0, Reservation::where('user_id', $andereKlant->id)->count());
    }

    public function test_the_number_of_days_must_match_the_package(): void
    {
        $klant = $this->klant();
        $gegevens = $this->geldigeReservering();
        $gegevens['lesson_dates'] = [$this->dag(0), $this->dag(1)];   // pakket van 3, maar 2 gekozen

        $this->actingAs($klant)->post(route('klant.reservations.store'), $gegevens)->assertSessionHasErrors('lesson_dates');

        $this->assertSame(0, Reservation::where('user_id', $klant->id)->count());
    }

    public function test_days_in_the_past_or_today_are_rejected(): void
    {
        $klant = $this->klant();
        $gegevens = $this->geldigeReservering('Privéles');

        foreach ([now()->subDay()->format('Y-m-d'), now()->format('Y-m-d')] as $dag) {
            $gegevens['lesson_dates'] = [$dag];
            $this->actingAs($klant)->post(route('klant.reservations.store'), $gegevens)->assertSessionHasErrors('lesson_dates.0');
        }

        $this->assertSame(0, Reservation::where('user_id', $klant->id)->count());
    }

    public function test_the_same_day_twice_is_rejected(): void
    {
        $klant = $this->klant();
        $gegevens = $this->geldigeReservering();
        $gegevens['lesson_dates'] = [$this->dag(0), $this->dag(0), $this->dag(1)];

        $this->actingAs($klant)->post(route('klant.reservations.store'), $gegevens)->assertSessionHasErrors('lesson_dates.0');

        $this->assertSame(0, Reservation::where('user_id', $klant->id)->count());
    }

    public function test_a_day_on_which_the_instructor_is_booked_is_rejected(): void
    {
        $duco = User::where('email', 'duco@kitesurfschool-windkracht12.nl')->firstOrFail();
        Lesson::create([
            'reservation_id' => Reservation::firstOrFail()->id,
            'instructor_id' => $duco->id,
            'lesson_date' => $this->dag(1),
        ]);

        $klant = $this->klant();

        // Wijk aan Zee heeft ook Duco als instructeur, dus ook daar is deze dag bezet.
        $this->actingAs($klant)->post(route('klant.reservations.store'), $this->geldigeReservering(locatie: 'Wijk aan Zee'))
            ->assertSessionHasErrors('lesson_dates');

        // Op een locatie met een andere instructeur (IJmuiden: Ruud) is dezelfde dag gewoon vrij.
        $this->actingAs($klant)->post(route('klant.reservations.store'), $this->geldigeReservering(locatie: 'IJmuiden'))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Reservation::where('user_id', $klant->id)->count());
    }

    public function test_package_and_location_are_required_and_must_exist(): void
    {
        $klant = $this->klant();

        $this->actingAs($klant)->post(route('klant.reservations.store'), [])
            ->assertSessionHasErrors(['package_id', 'location_id', 'lesson_dates']);

        $gegevens = $this->geldigeReservering();
        $gegevens['package_id'] = 9999;
        $gegevens['location_id'] = 9999;
        $this->actingAs($klant)->post(route('klant.reservations.store'), $gegevens)
            ->assertSessionHasErrors(['package_id', 'location_id']);
    }

    public function test_a_location_without_instructor_cannot_be_chosen(): void
    {
        $zonderInstructeur = Location::create(['name' => 'Texel']);

        $gegevens = $this->geldigeReservering();
        $gegevens['location_id'] = $zonderInstructeur->id;

        $this->actingAs($this->klant())->post(route('klant.reservations.store'), $gegevens)
            ->assertSessionHasErrors('location_id');
    }
}
