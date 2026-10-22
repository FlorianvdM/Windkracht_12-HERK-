<?php

namespace App\Http\Controllers\Instructeur;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

/**
 * De instructeur ziet een dag-, week- of maandoverzicht van zijn eigen lessen (casus 1f).
 */
class LessonController extends Controller
{
    public function index(Request $request): Response
    {
        // Ongeldige invoer in de adresbalk (bijvoorbeeld ?weergave=jaar) negeren we: dan tonen we vandaag.
        $controle = Validator::make($request->query(), [
            'weergave' => ['nullable', 'in:dag,week,maand'],
            'datum' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $invoer = $controle->fails() ? [] : $controle->validated();

        $weergave = $invoer['weergave'] ?? 'dag';
        $datum = isset($invoer['datum'])
            ? Carbon::createFromFormat('Y-m-d', $invoer['datum'])->startOfDay()
            : today();

        // Begin en einde van de periode die we tonen (een week begint op maandag).
        [$start, $eind] = match ($weergave) {
            'dag' => [$datum->copy(), $datum->copy()],
            'week' => [$datum->copy()->startOfWeek(), $datum->copy()->endOfWeek()],
            'maand' => [$datum->copy()->startOfMonth(), $datum->copy()->endOfMonth()],
        };

        // Eén stap vooruit of terug, passend bij de gekozen weergave (voor de knoppen Vorige en Volgende).
        $verschuif = fn (int $stap): string => match ($weergave) {
            'dag' => $datum->copy()->addDays($stap)->toDateString(),
            'week' => $datum->copy()->addWeeks($stap)->toDateString(),
            'maand' => $datum->copy()->addMonthsNoOverflow($stap)->toDateString(),
        };

        // Alleen de lessen van de ingelogde instructeur, zodat je nooit lessen van een collega ziet.
        $lessons = Lesson::query()
            ->where('instructor_id', $request->user()->id)
            ->whereDate('lesson_date', '>=', $start->toDateString())
            ->whereDate('lesson_date', '<=', $eind->toDateString())
            ->with(['reservation.customer', 'reservation.location', 'reservation.package'])
            ->orderBy('lesson_date')
            ->orderBy('start_time')
            ->get();

        return Inertia::render('instructeur/lessen', [
            'weergave' => $weergave,
            'datum' => $datum->toDateString(),
            'start' => $start->toDateString(),
            'eind' => $eind->toDateString(),
            'vorige' => $verschuif(-1),
            'volgende' => $verschuif(1),
            'vandaag' => today()->toDateString(),
            // We sturen alleen wat de instructeur nodig heeft: geen e-mailadres of andere privégegevens van de klant.
            'lessons' => $lessons->map(fn (Lesson $lesson): array => [
                'id' => $lesson->id,
                'date' => $lesson->lesson_date->format('Y-m-d'),
                'time' => substr($lesson->start_time, 0, 5),
                'status' => $lesson->status,
                'number' => $lesson->reservation->number,
                'reservation_status' => $lesson->reservation->status->value,
                'customer' => $lesson->reservation->customer->name,
                'location' => $lesson->reservation->location->name,
                'package' => $lesson->reservation->package->name,
            ])->values(),
        ]);
    }
}