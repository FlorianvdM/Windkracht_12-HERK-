<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eén lesdag binnen een reservering.
 *
 * @property int $id
 * @property int $reservation_id
 * @property int $instructor_id
 * @property \Illuminate\Support\Carbon $lesson_date
 * @property string $start_time
 * @property string $status
 * @property string|null $cancel_reason
 */
#[Fillable(['reservation_id', 'instructor_id', 'lesson_date', 'start_time', 'status', 'cancel_reason'])]
class Lesson extends Model
{
    // NIEUW: de mogelijke statussen van een les. Zo typ je de tekst maar op één plek.
    public const GEPLAND = 'gepland';                                  // de les gaat door
    public const ANNULERING_AANGEVRAAGD = 'annulering_aangevraagd';    // klant wil annuleren, eigenaar moet de reden goedkeuren
    public const NIEUWE_DATUM_KIEZEN = 'nieuwe_datum_kiezen';          // les gaat niet door, klant kiest een nieuwe datum

    protected function casts(): array
    {
        return [
            'lesson_date' => 'date',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
}