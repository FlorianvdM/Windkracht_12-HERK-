<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * De zes locaties, elk met de instructeur die daar les geeft (zoals in het ontwerp, wireframe 5).
 */
class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            'Zandvoort' => 'duco@kitesurfschool-windkracht12.nl',
            'Muiderberg' => 'waldemar@kitesurfschool-windkracht12.nl',
            'Wijk aan Zee' => 'duco@kitesurfschool-windkracht12.nl',
            'IJmuiden' => 'ruud@kitesurfschool-windkracht12.nl',
            'Scheveningen' => 'saskia@kitesurfschool-windkracht12.nl',
            'Hoek van Holland' => 'bernie@kitesurfschool-windkracht12.nl',
        ];

        foreach ($locations as $name => $instructorEmail) {
            Location::query()->updateOrCreate(['name' => $name], [
                'instructor_id' => User::where('email', $instructorEmail)->value('id'),
            ]);
        }
    }
}
