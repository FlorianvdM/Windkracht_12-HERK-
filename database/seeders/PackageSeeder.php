<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

/**
 * De vier lespakketten uit de casus (prijzen per persoon, materiaal inbegrepen).
 */
class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            ['Privéles', 17500, 2.5, 1, 1],
            ['Losse duo-kiteles', 13500, 3.5, 1, 2],
            ['Duo-pakket 3 lessen', 37500, 10.5, 3, 2],
            ['Duo-pakket 5 lessen', 67500, 17.5, 5, 2],
        ];

        foreach ($packages as [$name, $price, $hours, $days, $max]) {
            Package::query()->updateOrCreate(['name' => $name], [
                'price_cents' => $price,
                'hours' => $hours,
                'lesson_days' => $days,
                'max_persons' => $max,
            ]);
        }
    }
}
