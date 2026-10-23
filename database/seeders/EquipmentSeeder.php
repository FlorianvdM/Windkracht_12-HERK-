<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Seeder;

/**
 * Testdata voor het materiaalbeheer: wat de school ongeveer in de bus heeft liggen.
 */
class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $materiaal = [
            // naam, soort, maat, aantal, kapot
            ['Kite 7 m²', 'kite', '7 m²', 4, 0],
            ['Kite 9 m²', 'kite', '9 m²', 6, 1],
            ['Kite 12 m²', 'kite', '12 m²', 4, 0],
            ['Lesboard 140 cm', 'board', '140 cm', 8, 1],
            ['Wetsuit maat S', 'wetsuit', 'S', 5, 0],
            ['Wetsuit maat M', 'wetsuit', 'M', 6, 0],
            ['Wetsuit maat L', 'wetsuit', 'L', 5, 2],
            ['Helm', 'overig', null, 10, 0],
        ];

        foreach ($materiaal as [$naam, $soort, $maat, $aantal, $kapot]) {
            Equipment::query()->updateOrCreate(['name' => $naam], [
                'type' => $soort,
                'size' => $maat,
                'quantity' => $aantal,
                'defect' => $kapot,
            ]);
        }
    }
}