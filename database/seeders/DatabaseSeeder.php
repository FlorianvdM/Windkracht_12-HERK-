<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * De volgorde telt: locaties hebben instructeurs nodig, reserveringen hebben klanten, pakketten en locaties nodig.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PackageSeeder::class,
            LocationSeeder::class,
            ReservationSeeder::class,
        ]);
    }
}
