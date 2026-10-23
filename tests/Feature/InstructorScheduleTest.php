<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Een instructeur ziet een dag-, week- en maandoverzicht van zijn eigen lessen (casus 1f).
 * Duco heeft in de testdata lessen op 18, 22, 25 en 31 oktober 2026.
 */
class InstructorScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // De React-bestanden hoeven in de tests niet gebouwd te zijn.
        $this->withoutVite();

        $this->seed(DatabaseSeeder::class);
    }

    private function instructeur(string $voornaam): User
    {
        return User::where('email', $voornaam.'@kitesurfschool-windkracht12.nl')->firstOrFail();
    }

    private function overzicht(User $gebruiker, string $weergave, string $datum = '2026-10-22')
    {
        return $this->actingAs($gebruiker)->get(route('instructeur.lessons.index', [
            'weergave' => $weergave,
            'datum' => $datum,
        ]));
    }

    public function test_day_overview_shows_the_lesson_of_that_day(): void
    {
        $this->overzicht($this->instructeur('duco'), 'dag')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('instructeur/lessen')
                ->where('weergave', 'dag')
                ->has('lessons', 1)
                ->where('lessons.0.customer', 'Lara Visser')
                ->where('lessons.0.number', 'WK12-1007'));
    }

    public function test_week_overview_runs_from_monday_to_sunday(): void
    {
        // 22 oktober 2026 is een donderdag: de week loopt van maandag 19 t/m zondag 25 oktober.
        $this->overzicht($this->instructeur('duco'), 'week')
            ->assertInertia(fn (Assert $page) => $page
                ->where('start', '2026-10-19')
                ->where('eind', '2026-10-25')
                ->has('lessons', 2));
    }

    public function test_month_overview_shows_all_lessons_of_the_month(): void
    {
        $this->overzicht($this->instructeur('duco'), 'maand')
            ->assertInertia(fn (Assert $page) => $page
                ->where('start', '2026-10-01')
                ->where('eind', '2026-10-31')
                ->has('lessons', 4));
    }

    public function test_instructor_only_sees_own_lessons(): void
    {
        // Ruud geeft les in IJmuiden en heeft in de testdata geen lessen.
        $this->overzicht($this->instructeur('ruud'), 'maand')
            ->assertInertia(fn (Assert $page) => $page->has('lessons', 0));
    }

    public function test_invalid_view_falls_back_to_day_overview(): void
    {
        $this->actingAs($this->instructeur('duco'))
            ->get(route('instructeur.lessons.index', ['weergave' => 'jaar']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('weergave', 'dag'));
    }

    public function test_customers_and_guests_cannot_open_the_overview(): void
    {
        $klant = User::factory()->create(['role' => Role::Klant]);

        $this->actingAs($klant)->get(route('instructeur.lessons.index'))->assertForbidden();

        auth()->logout();
        $this->get(route('instructeur.lessons.index'))->assertRedirect(route('login'));
    }
}