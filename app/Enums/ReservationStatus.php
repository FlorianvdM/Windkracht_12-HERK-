<?php

namespace App\Enums;

/**
 * Status van een reservering: eerst voorlopig, definitief na controle van de betaling door de eigenaar.
 */
enum ReservationStatus: string
{
    case Voorlopig = 'voorlopig';
    case Definitief = 'definitief';
}
