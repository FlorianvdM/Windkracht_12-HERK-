<?php

namespace App\Support;

use App\Models\Lesson;
use Illuminate\Support\Facades\Mail;

/**
 * Verstuurt de standaardmails rond het annuleren van lessen.
 *
 * In .env staat MAIL_MAILER=log. Laravel verstuurt de mail dan niet echt, maar schrijft hem
 * in storage/logs/laravel.log. Zo kan ik laten zien dat de mail klopt, zonder mailserver (Won't have).
 * Later is alleen een andere MAIL_MAILER in .env nodig om echte e-mails te versturen (deelvraag 6).
 */
class StandaardMail
{
    /**
     * Stuurt een tekstmail naar de klant van deze les. Welke les en welke instructeur het betreft,
     * staat er altijd in (casus 1e).
     *
     * @param  array<int, string>  $regels  de tekst van de mail, regel voor regel
     */
    public static function naarKlant(Lesson $lesson, string $onderwerp, array $regels): void
    {
        $lesson->loadMissing(['reservation.customer', 'reservation.location', 'instructor']);
        $klant = $lesson->reservation->customer;

        $tekst = implode("\n", [
            "Beste {$klant->name},",
            '',
            ...$regels,
            '',
            'Om welke les gaat het?',
            "Reservering: {$lesson->reservation->number}",
            'Datum: '.$lesson->lesson_date->format('d-m-Y').' om '.substr($lesson->start_time, 0, 5),
            "Locatie: {$lesson->reservation->location->name}",
            "Instructeur: {$lesson->instructor->name}",
            '',
            'Met vriendelijke groet,',
            'Kitesurfschool Windkracht-12',
        ]);

        Mail::raw($tekst, function ($mail) use ($klant, $onderwerp): void {
            $mail->to($klant->email, $klant->name)->subject($onderwerp);
        });
    }
}