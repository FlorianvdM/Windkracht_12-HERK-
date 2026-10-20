<?php

namespace App\Enums;

/**
 * De drie gebruikersrollen uit de casus.
 */
enum Role: string
{
    case Klant = 'klant';
    case Instructeur = 'instructeur';
    case Eigenaar = 'eigenaar';
}
