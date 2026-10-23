<?php

namespace App\Http\Controllers\Eigenaar;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Materiaalbeheer door de eigenaar: kites, boards en wetsuits toevoegen, aantallen aanpassen en verwijderen.
 */
class EquipmentController extends Controller
{
    /**
     * Overzicht van al het materiaal, gesorteerd op soort en naam.
     */
    public function index(): Response
    {
        return Inertia::render('eigenaar/materiaal', [
            'types' => Equipment::TYPES,
            'items' => Equipment::query()
                ->orderBy('type')
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'size', 'quantity', 'defect']),
        ]);
    }

    /**
     * Nieuw materiaal toevoegen.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(Equipment::TYPES)],
            'size' => ['nullable', 'string', 'max:20'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ], [
            'name.required' => 'Vul een naam in.',
            'type.in' => 'Kies een geldige soort.',
            'quantity.required' => 'Vul het aantal in.',
            'quantity.min' => 'Het aantal moet minimaal 1 zijn.',
        ]);

        Equipment::create([...$data, 'defect' => 0]);

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$data['name']} is toegevoegd."]);

        return back();
    }

    /**
     * Het aantal stuks en het aantal kapotte stuks aanpassen.
     */
    public function update(Request $request, Equipment $equipment): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:999'],
            // Er kunnen nooit meer stuks kapot zijn dan er in totaal zijn.
            'defect' => ['required', 'integer', 'min:0', 'lte:quantity'],
        ], [
            'defect.lte' => 'Er kunnen niet meer stuks kapot zijn dan er in totaal zijn.',
        ]);

        $equipment->update($data);

        return back();
    }

    /**
     * Materiaal verwijderen, bijvoorbeeld als het is verkocht of weggegooid.
     */
    public function destroy(Equipment $equipment): RedirectResponse
    {
        $equipment->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$equipment->name} is verwijderd."]);

        return back();
    }
}