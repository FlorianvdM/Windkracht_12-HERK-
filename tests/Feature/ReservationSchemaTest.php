<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Enums\Role;
use App\Models\Lesson;
use App\Models\Package;
use App\Models\Reservation;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_are_customers_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertSame(Role::Klant, $user->fresh()->role);
    }

    public function test_role_cannot_be_mass_assigned(): void
    {
        $user = User::create([
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
            'password' => 'Windkracht-12!Test',
            'role' => Role::Eigenaar->value,
        ]);

        $this->assertSame(Role::Klant, $user->fresh()->role);
    }

    public function test_seeders_create_packages_locations_and_example_reservations(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(4, Package::count());
        $this->assertSame(1, User::where('role', Role::Eigenaar)->count());
        $this->assertSame(5, User::where('role', Role::Instructeur)->count());

        $reservation = Reservation::where('number', 'WK12-1007')->firstOrFail();
        $this->assertSame(ReservationStatus::Voorlopig, $reservation->status);
        $this->assertSame(75000, $reservation->total_cents);
        $this->assertCount(3, $reservation->lessons);
        $this->assertSame('Zandvoort', $reservation->location->name);
        $this->assertSame('Duco Veenstra', $reservation->location->instructor->name);
    }

    public function test_instructor_cannot_have_two_lessons_on_the_same_day(): void
    {
        $this->seed(DatabaseSeeder::class);
        $lesson = Lesson::firstOrFail();

        $this->expectException(QueryException::class);

        Lesson::create([
            'reservation_id' => $lesson->reservation_id,
            'instructor_id' => $lesson->instructor_id,
            'lesson_date' => $lesson->lesson_date,
        ]);
    }
}
