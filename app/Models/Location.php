<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Een locatie waar les wordt gegeven. De locatie bepaalt welke instructeur de les geeft.
 *
 * @property int $id
 * @property string $name
 * @property int|null $instructor_id
 */
#[Fillable(['name', 'instructor_id'])]
class Location extends Model
{
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
