<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
 */
#[Fillable(['number', 'user_id', 'package_id', 'location_id', 'status', 'payment_reported', 'total_cents'])]
class Reservation extends Model
{
    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'payment_reported' => 'boolean',
        ];
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
