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

    /**
     * De naam van de route met de eigen homepage van deze rol.
     * Na het inloggen sturen we iedereen hiernaartoe (casus: "eigen homepage per gebruikersrol").
     */
    public function homeRoute(): string
    {
        return match ($this) {
            self::Klant => 'klant.home',
            self::Instructeur => 'instructeur.home',
            self::Eigenaar => 'eigenaar.home',
        };
    }
}
