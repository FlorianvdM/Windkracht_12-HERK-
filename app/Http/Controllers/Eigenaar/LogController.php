<?php

namespace App\Http\Controllers\Eigenaar;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;

/**
 * De eigenaar bekijkt het logboek met in- en uitlogmomenten (storage/logs/inlog.log).
 */
class LogController extends Controller
{
    public function index(): Response
    {
        $pad = storage_path('logs/inlog.log');

        // Bestaat het bestand nog niet (nog niemand ingelogd)? Dan is de lijst leeg.
        $regels = File::exists($pad)
            ? File::lines($pad)->filter()->values()->all()
            : [];

        // De nieuwste 200 regels bovenaan. Elke regel knippen we op ' | ' in vier kolommen.
        $items = collect($regels)
            ->reverse()
            ->take(200)
            ->map(function (string $regel): array {
                [$datum, $tijd, $actie, $email] = array_pad(explode(' | ', $regel), 4, '');

                return compact('datum', 'tijd', 'actie', 'email');
            })
            ->values();

        return Inertia::render('eigenaar/logboek', [
            'regels' => $items,
        ]);
    }
}