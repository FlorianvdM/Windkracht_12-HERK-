<?php

namespace App\Http\Requests\Klant;

use App\Models\Lesson;
use App\Models\Location;
use App\Models\Package;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Controleert een nieuwe reservering. De controle gebeurt altijd op de server,
 * ook als de React-pagina al controleert: browsers kun je namelijk omzeilen.
 */
class StoreReservationRequest extends FormRequest
{
    /**
     * De basisregels. De rol-middleware zorgt er al voor dat alleen klanten hier komen.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            // Alleen locaties met een instructeur kunnen worden gekozen.
            'location_id' => [
                'required',
                'integer',
                Rule::exists('locations', 'id')->whereNotNull('instructor_id'),
            ],
            'lesson_dates' => ['required', 'array', 'min:1'],
            // Elke lesdag: geldige datum, vanaf morgen, en geen dag dubbel.
            'lesson_dates.*' => ['required', 'date_format:Y-m-d', 'after:today', 'distinct'],
            // NIEUW: NAW-gegevens van de duo-lesser. Of ze verplicht zijn, controleren we in after(),
            // want dat hangt af van het gekozen pakket.
            'duo_name' => ['nullable', 'string', 'max:255'],
            'duo_address' => ['nullable', 'string', 'max:255'],
            'duo_city' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Extra controles die meer gegevens nodig hebben. Ze lopen pas nadat de basisregels kloppen.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $package = Package::findOrFail($this->input('package_id'));
                $location = Location::findOrFail($this->input('location_id'));
                $dates = $this->input('lesson_dates');

                // Het aantal lesdagen moet precies bij het pakket passen.
                if (count($dates) !== $package->lesson_days) {
                    $validator->errors()->add(
                        'lesson_dates',
                        "Dit pakket heeft {$package->lesson_days} lesdag(en). Kies er precies {$package->lesson_days}.",
                    );

                    return;
                }

                // NIEUW: bij een duo-pakket (max. 2 personen) zijn de gegevens van de duo-lesser verplicht.
                if ($package->max_persons > 1) {
                    $velden = [
                        'duo_name' => 'Vul de naam van je duo-lesser in.',
                        'duo_address' => 'Vul het adres van je duo-lesser in.',
                        'duo_city' => 'Vul de woonplaats van je duo-lesser in.',
                    ];

                    foreach ($velden as $veld => $melding) {
                        if (blank($this->input($veld))) {
                            $validator->errors()->add($veld, $melding);
                        }
                    }

                    if ($validator->errors()->isNotEmpty()) {
                        return;
                    }
                }

                // Een instructeur geeft maximaal één les per dag (zie de lessons-tabel).
                $bezet = Lesson::query()
                    ->where('instructor_id', $location->instructor_id)
                    ->whereIn('lesson_date', $dates)
                    ->exists();

                if ($bezet) {
                    $validator->errors()->add(
                        'lesson_dates',
                        'Een of meer gekozen dagen zijn al bezet. Kies een andere dag.',
                    );
                }
            },
        ];
    }

    /**
     * Nederlandse foutmeldingen.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'package_id.required' => 'Kies een pakket.',
            'package_id.exists' => 'Dit pakket bestaat niet.',
            'location_id.required' => 'Kies een locatie.',
            'location_id.exists' => 'Deze locatie bestaat niet of heeft geen instructeur.',
            'lesson_dates.required' => 'Kies je lesdagen.',
            'lesson_dates.min' => 'Kies je lesdagen.',
            'lesson_dates.*.date_format' => 'Een lesdag heeft geen geldige datum.',
            'lesson_dates.*.after' => 'Je kunt alleen lesdagen kiezen vanaf morgen.',
            'lesson_dates.*.distinct' => 'Je hebt dezelfde dag twee keer gekozen.',
            'duo_name.max' => 'De naam van je duo-lesser is te lang.',
            'duo_address.max' => 'Het adres van je duo-lesser is te lang.',
            'duo_city.max' => 'De woonplaats van je duo-lesser is te lang.',
        ];
    }
}