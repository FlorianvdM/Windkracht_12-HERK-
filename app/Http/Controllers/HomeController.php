<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Inertia\Inertia;
use Inertia\Response;

/**
 * De algemene homepage: voor iedereen toegankelijk, met informatie over kitesurfen en de pakketten.
 */
class HomeController extends Controller
{
    public function index(): Response
    {
        // De pakketten komen uit de database, zodat de prijzen op de homepage
        // altijd kloppen met wat klanten bij het reserveren zien.
        return Inertia::render('welcome', [
            'packages' => Package::query()->orderBy('price_cents')->get()->map(fn (Package $package): array => [
                'id' => $package->id,
                'name' => $package->name,
                'price_cents' => $package->price_cents,
                'hours' => (float) $package->hours,
                'lesson_days' => $package->lesson_days,
                'max_persons' => $package->max_persons,
            ]),
        ]);
    }
}