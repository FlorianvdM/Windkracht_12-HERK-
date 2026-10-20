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
 */
#[Fillable(['reservation_id', 'instructor_id', 'lesson_date', 'start_time', 'status'])]
class Lesson extends Model
{
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
