<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

/**
 * Een lespakket, bijvoorbeeld "Duo-pakket 3 lessen".
 *
 * @property int $id
 * @property string $name
 * @property int $price_cents prijs per persoon in eurocenten
 * @property string $hours
 * @property int $lesson_days
 * @property int $max_persons
 */
#[Fillable(['name', 'price_cents', 'hours', 'lesson_days', 'max_persons'])]
class Package extends Model
{
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
