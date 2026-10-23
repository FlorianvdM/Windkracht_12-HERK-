<?php

namespace Database\Seeders;

use App\Enums\ReservationStatus;
use App\Models\Location;
use App\Models\Package;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Twee voorbeeldreserveringen om de overzichten mee te testen (zelfde als in het ontwerp).
 */
class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $lara = User::where('email', 'lara.visser@email.nl')->firstOrFail();
        $zandvoort = Location::where('name', 'Zandvoort')->firstOrFail();

        $examples = [
            // nummer, pakket, betaling gemeld, lesdagen
            ['WK12-1001', 'Privéles', true, ['2026-10-18']],
            ['WK12-1007', 'Duo-pakket 3 lessen', false, ['2026-10-22', '2026-10-25', '2026-10-31']],
        ];

        foreach ($examples as [$number, $packageName, $reported, $dates]) {
            $package = Package::where('name', $packageName)->firstOrFail();

            $reservation = Reservation::query()->updateOrCreate(['number' => $number], [
                'user_id' => $lara->id,
                'package_id' => $package->id,
                'location_id' => $zandvoort->id,
                'status' => ReservationStatus::Voorlopig,
                'payment_reported' => $reported,
                // De aanmelder betaalt het totaal, ook als het pakket per persoon is geprijsd.
                'total_cents' => $package->price_cents * $package->max_persons,
            ]);

            foreach ($dates as $date) {
                $reservation->lessons()->updateOrCreate(
                    ['instructor_id' => $zandvoort->instructor_id, 'lesson_date' => $date],
                    ['status' => 'gepland'],
                );
            }
        }
    }
}
