<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Lesmateriaal: een soort kite, board of wetsuit met het aantal stuks.
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string|null $size
 * @property int $quantity
 * @property int $defect
 */
#[Fillable(['name', 'type', 'size', 'quantity', 'defect'])]
class Equipment extends Model
{
    // 'Equipment' heeft geen meervoud in het Engels. Daarom zeggen we zelf welke tabel het is.
    protected $table = 'equipment';

    // De soorten materiaal die je kunt kiezen.
    public const TYPES = ['kite', 'board', 'wetsuit', 'overig'];
}