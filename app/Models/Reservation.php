<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Een reservering van een klant voor een pakket op een locatie.
 *
 * @property int $id
 * @property string $number
 * @property int $user_id
 * @property int $package_id
 * @property int $location_id
 * @property ReservationStatus $status
 * @property bool $payment_reported
 * @property int $total_cents
 * @property string|null $duo_name
 * @property string|null $duo_address
 * @property string|null $duo_city
 */
// NIEUW: de drie duo-velden zijn toegevoegd, zodat we ze in één keer kunnen opslaan met create().
#[Fillable(['number', 'user_id', 'package_id', 'location_id', 'status', 'payment_reported', 'total_cents', 'duo_name', 'duo_address', 'duo_city'])]
class Reservation extends Model
{
    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'payment_reported' => 'boolean',
        ];
    }

    /**
     * Het volgende reserveringsnummer, bijvoorbeeld WK12-1008.
     * We zoeken het hoogste bestaande nummer en tellen er één bij op. Zo botsen nieuwe nummers
     * nooit met de nummers uit de testdata (WK12-1001 en WK12-1007).
     */
    public static function nextNumber(): string
    {
        $hoogste = static::query()
            ->pluck('number')
            ->map(fn (string $nummer): int => (int) Str::after($nummer, 'WK12-'))
            ->max();

        return 'WK12-'.(($hoogste ?: 1000) + 1);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}