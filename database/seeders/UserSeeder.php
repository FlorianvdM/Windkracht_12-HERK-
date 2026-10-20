<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Testgebruikers voor het prototype (de casus vraagt om voldoende testdata).
 * De eigenaar staat 'out of the box' in de database (casus 3a). Alle namen zijn uit de casus of verzonnen.
 * Let op: dit zijn alleen testaccounts voor op de eigen computer.
 */
class UserSeeder extends Seeder
{
    /** Gemeenschappelijk testwachtwoord, voldoet aan de wachtwoordregels (12 tekens, hoofdletter, cijfer, leesteken). */
    public const TEST_PASSWORD = 'Windkracht-12!Test';

    public function run(): void
    {
        $users = [
            ['Terence Olieslager', 'terence@kitesurfschool-windkracht12.nl', Role::Eigenaar],
            ['Duco Veenstra', 'duco@kitesurfschool-windkracht12.nl', Role::Instructeur],
            ['Waldemar van Dongen', 'waldemar@kitesurfschool-windkracht12.nl', Role::Instructeur],
            ['Ruud Terlingen', 'ruud@kitesurfschool-windkracht12.nl', Role::Instructeur],
            ['Saskia Brink', 'saskia@kitesurfschool-windkracht12.nl', Role::Instructeur],
            ['Bernie Vredenstein', 'bernie@kitesurfschool-windkracht12.nl', Role::Instructeur],
            ['Lara Visser', 'lara.visser@email.nl', Role::Klant],
            ['Eva Mol', 'eva.mol@email.nl', Role::Klant],
            ['Noah de Groot', 'noah.degroot@email.nl', Role::Klant],
        ];

        foreach ($users as [$name, $email, $role]) {
            // De rol is bewust niet mass-assignable (beveiliging), daarom forceFill.
            User::query()->firstOrNew(['email' => $email])
                ->forceFill([
                    'name' => $name,
                    'password' => self::TEST_PASSWORD,
                    'role' => $role,
                    'email_verified_at' => now(),
                ])
                ->save();
        }
    }
}
